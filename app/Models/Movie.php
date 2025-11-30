<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Category;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'year',
        'duration',
        'genres',
        'user_score',
        'rating_decimal',
        'image_path',
        'video_url',
        'tmdb_id',
        'category_id',
        'is_featured',
        'is_marvel',
        'visibility',
        'dashboard_id',
    ];

    protected $casts = [
        'genres' => 'array',
        'is_featured' => 'boolean',
        'is_marvel' => 'boolean',
        'rating_decimal' => 'float',
        'user_score' => 'integer',
        'tmdb_id' => 'integer',
        'category_id' => 'integer',
    ];

    // Append a computed full URL for the frontend to consume
    protected $appends = ['image_url'];

    /**
     * Get a fully-resolved image URL for the movie.
     * Handles seeded relative paths (public folder), uploaded storage paths, and absolute URLs.
     */
    public function getImageUrlAttribute()
    {
        $path = $this->image_path ?? $this->image ?? null;

        if (!$path) {
            return null;
        }

        // If already an absolute URL, return as-is
        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        // If the path already includes the storage prefix (e.g. 'storage/movies/...')
        if (strpos($path, 'storage/') === 0) {
            return asset($path);
        }

        // If file exists in public/ exactly as provided (e.g. public/images/movies/...)
        if (file_exists(public_path($path))) {
            return asset($path);
        }

        // Some seed data or older fixtures may use 'images/...' while actual
        // assets live under 'image/'. Try both variants to be tolerant.
        if (strpos($path, 'images/') === 0) {
            $alt = 'image/' . substr($path, strlen('images/'));
            if (file_exists(public_path($alt))) {
                return asset($alt);
            }
        }

        // If the file exists in the storage disk (storage/app/public/...)
        $candidate = ltrim(str_replace('storage/', '', $path), '/');
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($candidate)) {
            return asset('storage/' . $candidate);
        }

        // Try public/image/ fallback for other relative paths (some assets live in public/image)
        if (file_exists(public_path('image/' . ltrim($path, '/')))) {
            return asset('image/' . ltrim($path, '/'));
        }

        // Final fallback: a placeholder image with title
        return 'https://via.placeholder.com/280x280?text=' . urlencode($this->title ?? 'Movie');
    }

    /**
     * Category relation
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Update this Movie model using TMDB movie data array.
     * Accepts the TMDB /movie/{id} response and maps commonly used fields.
     * This method is idempotent and intended for one-off syncs or background jobs.
     *
     * @param array $tmdb
     * @return $this
     */
    public function updateFromTmdb(array $tmdb)
    {
        $attrs = self::attributesFromTmdb($tmdb);

        // Fill only the attributes we prepared and save
        $this->fill($attrs);

        // If slug was not provided but model already has a slug, preserve it
        if (empty($this->slug) && !empty($attrs['title'])) {
            $this->slug = \Illuminate\Support\Str::slug($attrs['title']);
        }

        // Detect Marvel production companies by their TMDB IDs.
        // TMDB company ids:
        //  - 420  => Marvel Studios
        //  - 7505 => Marvel Entertainment
        $isMarvel = false;
        if (!empty($tmdb['production_companies']) && is_array($tmdb['production_companies'])) {
            foreach ($tmdb['production_companies'] as $company) {
                if (!empty($company['id']) && in_array((int) $company['id'], [420, 7505], true)) {
                    $isMarvel = true;
                    break;
                }
            }
        }

        // Ensure the model property is set so it will be persisted on save.
        $this->is_marvel = $isMarvel;

        $this->save();

        return $this;
    }

    /**
     * Build an attributes array from TMDB movie data without persisting.
     * This is useful for dry-run previews or external syncers.
     *
     * @param array $tmdb
     * @return array
     */
    public static function attributesFromTmdb(array $tmdb): array
    {
        $attrs = [];

        $attrs['title'] = $tmdb['title'] ?? $tmdb['original_title'] ?? null;
        $attrs['description'] = $tmdb['overview'] ?? null;

        if (!empty($tmdb['release_date'])) {
            $attrs['year'] = (int) substr($tmdb['release_date'], 0, 4);
        }

        if (isset($tmdb['runtime'])) {
            $attrs['duration'] = (int) $tmdb['runtime'];
        }

        if (!empty($tmdb['genres']) && is_array($tmdb['genres'])) {
            $attrs['genres'] = array_values(array_map(function ($g) {
                return $g['name'] ?? null;
            }, $tmdb['genres']));
        }

        if (isset($tmdb['vote_average'])) {
            $avg = (float) $tmdb['vote_average'];
            $attrs['rating_decimal'] = $avg;
            // Legacy integer score (0-100) kept for compatibility but renamed to `user_score`.
            $attrs['user_score'] = (int) round($avg * 10);
        }

        if (isset($tmdb['id'])) {
            $attrs['tmdb_id'] = (int) $tmdb['id'];
        }

        if (!empty($tmdb['poster_path'])) {
            $base = config('services.tmdb.image_base_url', 'https://image.tmdb.org/t/p/w780');
            $attrs['image_path'] = rtrim($base, '/') . '/' . ltrim($tmdb['poster_path'], '/');
        }

        return $attrs;
    }
}
