<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Category;
use App\Services\TmdbService;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    // ✅ index الآن يدعم فلترة featured عن طريق ?featured=1
    public function index(Request $request)
    {
        $query = Movie::query();

        // دعم فلترة visibility عبر ?visibility=homepage أو ?featured=1 (backwards-compatible)
        if ($request->filled('visibility')) {
            $vis = $request->query('visibility');
            // تقبل 'homepage', 'dashboard', 'both', 'add-movie'
            if (in_array($vis, ['homepage','dashboard','both','add-movie'])) {
                if ($vis === 'homepage') {
                    $query->where(function($q){
                        $q->where('visibility','homepage')
                          ->orWhere('visibility','both');
                    });
                } elseif ($vis === 'dashboard') {
                    $query->where(function($q){
                        $q->where('visibility','dashboard')
                          ->orWhere('visibility','both');
                    });
                } elseif ($vis === 'add-movie') {
                    // show movies intended for the Add Movie page
                    $query->where('visibility', 'add-movie');
                } else { // both
                    // لا نضيف شرط (يعرض الكل)
                }
            }
        } elseif ($request->boolean('featured')) {
            // توافق مع السلوك القديم: ?featured=1
            $query->where('is_featured', true);
        }

        $movies = $query->orderBy('created_at', 'desc')->get();

        return response()->json($movies);
    }

    /**
     * Show the Add Movie form.
     *
     * This method loads categories from the database and passes them to the
     * `add-movie` Blade view. The view will render the category selector
     * without performing any writes.
     */
    public function create()
    {
        // Ensure the DC Comics category exists so admins can select it
        // from the Add Movie form even if the database hasn't been seeded yet.
        try {
            Category::firstOrCreate(
                ['slug' => 'dc-comics'],
                ['name' => 'DC Comics', 'description' => 'Movies and series from the DC Comics universe']
            );
        } catch (\Exception $e) {
            // ignore any DB errors here; we'll still attempt to load categories
        }

        $categories = Category::orderBy('id')->get();
        return view('add-movie', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->all();

        // حالة: مجموعة أفلام (bulk insert)
        if (isset($data[0]) && is_array($data[0])) {
            $validated = validator($data, [
                '*.title' => 'required|string|max:255',
                '*.description' => 'required|string',
                '*.user_score' => 'required|numeric|min:0|max:10',
                '*.image' => 'nullable|string',
                // سمحنا أيضًا بـ is_featured إن أُرسِل (اختياري)
                '*.is_featured' => 'sometimes|boolean',
                '*.visibility' => 'sometimes|in:dashboard,homepage,both,add-movie',
                '*.dashboard_id' => 'sometimes|nullable|integer',
                '*.category_id' => 'sometimes|nullable|integer|exists:categories,id',
            ])->validate();

            $now = now();
            foreach ($validated as &$movie) {
                $movie['created_at'] = $now;
                $movie['updated_at'] = $now;

                // تعيين is_featured بناءً على visibility (توافق)
                if (isset($movie['visibility'])) {
                    $movie['is_featured'] = in_array($movie['visibility'], ['homepage','both']);
                }
                // Normalize user score: incoming is 0-10 (may be decimal). We store two fields:
                // - rating_decimal: float 0-10 (one decimal precision)
                // - user_score: integer 0-100 (legacy integer representation stored under `user_score`)
                if (isset($movie['user_score'])) {
                    $dec = floatval($movie['user_score']);
                    // clamp
                    $dec = max(0, min(10, $dec));
                    $movie['rating_decimal'] = round($dec, 1);
                    // Legacy integer representation (0-100) stored under `user_score`
                    $movie['user_score'] = intval(round($dec * 10));
                }

                // If the category corresponds to Marvel Cinematic Universe,
                // ensure the movie will appear in the Latest Movies section by
                // giving it a homepage visibility (use 'both' for compatibility).
                if (!empty($movie['category_id'])) {
                    try {
                        $c = Category::find($movie['category_id']);
                        if ($c && (strtolower($c->slug) === 'marvel-cinematic-universe' || strtolower($c->name) === 'marvel cinematic universe')) {
                            $movie['visibility'] = 'both';
                            $movie['is_featured'] = true;
                            // set dashboard 2 when adding MCU items if not provided
                            if (empty($movie['dashboard_id'])) {
                                $movie['dashboard_id'] = 2;
                            }
                        }
                    } catch (\Exception $e) {
                        // ignore mapping failures for bulk inserts
                    }
                }
            }

            Movie::insert($validated);

            return response()->json(['message'=>'Movies added successfully','count'=>count($validated)], 201);
        }

        // حالة: فيلم واحد (فورم مع/بدون صورة)
      $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'user_score' => 'required|numeric|min:0|max:10',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            // قبول حقل is_featured كمُدخل (اختياري)
            'is_featured' => 'sometimes|boolean',
                    'visibility' => 'sometimes|in:dashboard,homepage,both,add-movie',
                            'dashboard_id' => 'sometimes|nullable|integer',
                            'category_id' => 'sometimes|nullable|integer|exists:categories,id',
        ]);

        // رفع الصورة إذا وُجِدَت
        if ($request->hasFile('image')) {
            try {
                $file = $request->file('image');
                if ($file->isValid()) {
                    $path = $file->store('movies', 'public');
                    $validated['image_path'] = 'storage/' . $path;
                }
            } catch (\Exception $e) {
                logger()->error('Image upload failed', ['exception' => $e->getMessage()]);
            }
        }

        // إذا أرسل المستخدم visibility فضبط is_featured تلقائياً للعلاقة
        if (isset($validated['visibility'])) {
            $validated['is_featured'] = in_array($validated['visibility'], ['homepage','both']);
        } else {
            // لو لم يُرسَل visibility وحصلنا على الـ checkbox القديم
            if (isset($validated['is_featured'])) {
                $validated['visibility'] = $validated['is_featured'] ? 'both' : 'dashboard';
            } else {
                $validated['visibility'] = 'dashboard';
            }
        }

        // Normalize user score for the single create path as well
        if (isset($validated['user_score'])) {
            $dec = floatval($validated['user_score']);
            $dec = max(0, min(10, $dec));
            $validated['rating_decimal'] = round($dec, 1);
            $validated['user_score'] = intval(round($dec * 10));
        }

        // If admin selected the 'disney-plus' helper category (canonical slug
        // used by CI/scripts), map it to the actual Disney+ Originals category
        // used by the front-end ('disney-plus-originals') so the movie will
        // appear in the ✨ Disney+ Originals section. Also set the MCU
        // dashboard (2) and mark as Marvel when helpful so it surfaces in
        // the 🛡️ Marvel Cinematic Universe area.
        //
        // Assumption: selecting the helper slug 'disney-plus' in the Add Movie
        // form should result in the movie appearing in Disney+ Originals and
        // in the MCU dashboard. If you prefer a different behavior (e.g.
        // attach to both categories via a many-to-many relationship), tell me
        // and I can implement that instead.
        if (!empty($validated['category_id'])) {
            try {
                $sel = Category::find($validated['category_id']);
                if ($sel && $sel->slug === 'disney-plus') {
                    $target = Category::firstOrCreate(
                        ['slug' => 'disney-plus-originals'],
                        ['name' => 'Disney+ Originals', 'description' => 'Disney+ Originals - auto-created']
                    );
                    if ($target) {
                        $validated['category_id'] = $target->id;
                    }

                    // Only map the category to Disney+ Originals. Do not modify
                    // dashboard or MCU flags — the movie should appear only in
                    // the ✨ Disney+ Originals section per your request.
                }
            } catch (\Exception $e) {
                logger()->warning('Category mapping (disney-plus) failed: ' . $e->getMessage());
            }
        }

        // If the selected category is Marvel Cinematic Universe, also make
        // sure the movie is visible on the homepage/latest list by setting
        // visibility to 'both' (and mark featured). This makes MCU-added
        // movies appear in the 🎬 Latest Movies section automatically.
        if (!empty($validated['category_id'])) {
            try {
                $sel2 = Category::find($validated['category_id']);
                if ($sel2 && (strtolower($sel2->slug) === 'marvel-cinematic-universe' || strtolower($sel2->name) === 'marvel cinematic universe')) {
                    $validated['visibility'] = 'both';
                    $validated['is_featured'] = true;
                    if (empty($validated['dashboard_id'])) {
                        $validated['dashboard_id'] = 2; // MCU dashboard
                    }
                }
            } catch (\Exception $e) {
                logger()->warning('MCU visibility mapping failed: ' . $e->getMessage());
            }
        }

        // Simple duplicate-protection: if a movie with the same title and
        // description was created very recently, assume the second request is
        // a duplicate (e.g. double-submit or double-fetch) and return the
        // existing record instead of creating another one.
        try {
            $recentDuplicate = Movie::where('title', $validated['title'] ?? '')
                ->where('description', $validated['description'] ?? '')
                ->where('created_at', '>=', now()->subSeconds(10))
                ->first();

            if ($recentDuplicate) {
                return response()->json([
                    'message' => 'Duplicate prevented: movie already added recently',
                    'movie' => $recentDuplicate,
                ], 200);
            }
        } catch (\Exception $e) {
            // If something went wrong during duplicate check, log and continue to create
            logger()->warning('Duplicate check failed: ' . $e->getMessage());
        }

        $movie = Movie::create($validated);

        return response()->json(['message'=>'✅ Movie added successfully','movie'=>$movie], 201);
    }

    // ✅ عرض فيلم واحد حسب ID
    // Front-end: show a movie page (returns HTML view)
    protected ?TmdbService $tmdb;

    public function __construct(?TmdbService $tmdb = null)
    {
        // TmdbService is optional (in case credentials are missing) — container will inject if bound
        $this->tmdb = $tmdb;
    }

    public function show(Movie $movie)
    {
    $tmdbTrailer = null;
    $foundTmdbId = null;

        // Try to fetch a trailer from TMDB if service configured
        try {
            if ($this->tmdb && (config('services.tmdb.read_access_token') || config('services.tmdb.api_key'))) {
                // If the movie already has a tmdb_id saved, use it. Otherwise search by title (+ year).
                $tmdbId = $movie->tmdb_id ?? null;

                if (!$tmdbId) {
                    $query = ['query' => $movie->title];
                    if ($movie->year) {
                        $query['year'] = $movie->year;
                    }

                    $search = $this->tmdb->get('search/movie', $query);
                    $results = $search['results'] ?? [];

                    if (!empty($results)) {
                        // pick the first reasonable match
                        $best = $results[0];
                        $tmdbId = $best['id'] ?? null;
                        // only mark foundTmdbId when we discovered it via search
                        $foundTmdbId = $tmdbId;
                    }
                }

                if ($tmdbId) {
                    // Request videos and credits to have video trailers and production company info
                    $movieData = $this->tmdb->getMovie($tmdbId, ['videos', 'credits']);
                    $videos = $movieData['videos']['results'] ?? [];

                    // Prefer an official YouTube trailer if available, otherwise
                    // fall back to any YouTube trailer, then any YouTube video.
                    // TMDB video objects often include an 'official' boolean we can prefer.
                    $tmdbTrailer = null;

                    // Try to pick an embeddable YouTube trailer/video.
                    // Prefer: official YouTube trailer -> any YouTube trailer -> any YouTube video.
                    $tmdbTrailer = null;

                    // Helper to test embeddability and build a privacy-friendly embed URL
                    $isEmbeddable = function($key) {
                        try {
                            if (!$this->tmdb) return false;
                            return $this->tmdb->isYoutubeEmbeddable($key);
                        } catch (\Exception $e) {
                            return false;
                        }
                    };

                    // 1) Official YouTube Trailer
                    foreach ($videos as $v) {
                        if (isset($v['site'], $v['type'], $v['key'])
                            && strtolower($v['site']) === 'youtube'
                            && strtolower($v['type']) === 'trailer'
                            && (!empty($v['official']) || (isset($v['official']) && $v['official'] === true))
                        ) {
                            if ($isEmbeddable($v['key'])) {
                                $tmdbTrailer = $this->tmdb->buildYoutubeEmbedUrl($v['key']);
                                break;
                            }
                        }
                    }

                    // 2) Any YouTube Trailer
                    if (!$tmdbTrailer) {
                        foreach ($videos as $v) {
                            if (isset($v['site'], $v['type'], $v['key'])
                                && strtolower($v['site']) === 'youtube'
                                && strtolower($v['type']) === 'trailer'
                            ) {
                                if ($isEmbeddable($v['key'])) {
                                    $tmdbTrailer = $this->tmdb->buildYoutubeEmbedUrl($v['key']);
                                    break;
                                }
                            }
                        }
                    }

                    // 3) Any YouTube video
                    if (!$tmdbTrailer) {
                        foreach ($videos as $v) {
                            if (isset($v['site'], $v['key']) && strtolower($v['site']) === 'youtube') {
                                if ($isEmbeddable($v['key'])) {
                                    $tmdbTrailer = $this->tmdb->buildYoutubeEmbedUrl($v['key']);
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // fail silently — we still render the movie page without trailer
            logger()->warning('TMDB trailer fetch failed: ' . $e->getMessage());
        }

        // Persist tmdb_id to the movie record if we found one and model doesn't already have it
        try {
            if ($foundTmdbId && !$movie->tmdb_id) {
                $movie->tmdb_id = $foundTmdbId;
                $movie->save();
            }
        } catch (\Exception $e) {
            logger()->warning('Failed to save tmdb_id on movie: ' . $e->getMessage());
        }

        // Persist the discovered TMDB trailer (embed URL) into movie->video_url when empty
        try {
            if (!empty($tmdbTrailer) && empty($movie->video_url)) {
                // Save the embed URL (so front-end can use it directly).
                $movie->video_url = $tmdbTrailer;
                $movie->save();
            }
        } catch (\Exception $e) {
            logger()->warning('Failed to save video_url on movie: ' . $e->getMessage());
        }

        // During local/debug, set a temporary YouTube link so the Watch button appears for testing
        if (app()->environment('local') || config('app.debug')) {
            if (empty($movie->video_url) && empty($tmdbTrailer)) {
                // using a watch URL (not embed) so it opens directly in a new tab
                $movie->video_url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
            }
        }

        // Return the blade view 'movie' with the movie model and optional tmdb trailer embed url
        return view('movie', ['movie' => $movie, 'tmdbTrailer' => $tmdbTrailer]);
    }

    // ✅ تعديل فيلم موجود
    public function update(Request $request, $id)
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return response()->json(['message' => 'Movie not found'], 404);
        }
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'user_score' => 'sometimes|required|numeric|min:0|max:10',
            'visibility' => 'sometimes|in:dashboard,homepage,both,add-movie',
            'is_featured' => 'sometimes|boolean',
            'dashboard_id' => 'sometimes|nullable|integer',
            'category_id' => 'sometimes|nullable|integer|exists:categories,id',
        ]);

        $data = $request->only(['title', 'description', 'user_score', 'visibility', 'is_featured', 'dashboard_id']);
        // include category_id if provided
        if ($request->has('category_id')) {
            $data['category_id'] = $request->input('category_id');
        }
        if (isset($data['user_score'])) {
            $dec = floatval($data['user_score']);
            $dec = max(0, min(10, $dec));
            $data['rating_decimal'] = round($dec, 1);
            $data['user_score'] = intval(round($dec * 10));
        }

        // Normalize visibility/is_featured relationship if visibility provided
        if (isset($data['visibility'])) {
            $data['is_featured'] = in_array($data['visibility'], ['homepage','both']);
        } else {
            // If visibility not provided but is_featured is, keep or derive visibility
            if (array_key_exists('is_featured', $data) && !isset($data['visibility'])) {
                $data['visibility'] = $data['is_featured'] ? 'both' : 'dashboard';
            }
        }

        // Normalize dashboard_id: accept empty/null
        if (array_key_exists('dashboard_id', $data) && ($data['dashboard_id'] === '' || $data['dashboard_id'] === null)) {
            $data['dashboard_id'] = null;
        }

        // Normalize category_id: accept empty/null
        if (array_key_exists('category_id', $data) && ($data['category_id'] === '' || $data['category_id'] === null)) {
            $data['category_id'] = null;
        }

        // Same mapping as create: if user changed the category to the helper
        // 'disney-plus' slug, map to the front-end category and set MCU flags.
        if (!empty($data['category_id'])) {
            try {
                $sel = Category::find($data['category_id']);
                if ($sel && $sel->slug === 'disney-plus') {
                    $target = Category::firstOrCreate(
                        ['slug' => 'disney-plus-originals'],
                        ['name' => 'Disney+ Originals', 'description' => 'Disney+ Originals - auto-created']
                    );
                    if ($target) {
                        $data['category_id'] = $target->id;
                    }

                    // Only remap the category; do not change dashboard_id or is_marvel.
                }
            } catch (\Exception $e) {
                logger()->warning('Category mapping (disney-plus) failed during update: ' . $e->getMessage());
            }
        }

        // If the category is changed/selected to Marvel Cinematic Universe
        // during an update, also ensure the movie remains visible in the
        // Latest Movies (homepage) by setting visibility to 'both'. Keep
        // dashboard and is_featured consistent with MCU convention.
        if (!empty($data['category_id'])) {
            try {
                $sel3 = Category::find($data['category_id']);
                if ($sel3 && (strtolower($sel3->slug) === 'marvel-cinematic-universe' || strtolower($sel3->name) === 'marvel cinematic universe')) {
                    $data['visibility'] = 'both';
                    $data['is_featured'] = true;
                    if (empty($data['dashboard_id'])) {
                        $data['dashboard_id'] = 2;
                    }
                }
            } catch (\Exception $e) {
                logger()->warning('MCU visibility mapping failed during update: ' . $e->getMessage());
            }
        }

        $movie->update($data);

        return response()->json($movie);
    }

    // ✅ حذف فيلم
    /**
     * Show movies for a given category slug (e.g. dc-comics)
     * Returns a blade view `category` with $category and $movies variables.
     */
    public function byCategory($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        // eager load movies for this category
        $movies = $category->movies()->orderBy('created_at', 'desc')->get();

        return view('category', ['category' => $category, 'movies' => $movies]);
    }

    public function destroy($id)
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return response()->json(['message' => 'Movie not found'], 404);
        }

        $movie->delete();

        return response()->json(['message' => 'Movie deleted successfully']);
    }

        // ✅ حذف كل الأفلام مرة واحدة
    public function destroyAll()
    {
        $count = Movie::count();

        if ($count === 0) {
            return response()->json(['message' => 'No movies to delete'], 404);
        }

        Movie::truncate(); // يمسح كل الصفوف من جدول movies
        return response()->json(['message' => "All ($count) movies deleted successfully"]);
    }

}
