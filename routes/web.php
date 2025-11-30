<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TmdbController;
use App\Http\Controllers\MovieController;

Route::get('/', function () {
    return view('index'); // 👈 هنا بنستدعي index.blade.php مباشرة من مجلد views
})->name('index');

Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Signup (new controller)
Route::get('/signup', [App\Http\Controllers\RegisterController::class, 'show'])->name('signup');
Route::post('/signup', [App\Http\Controllers\RegisterController::class, 'register'])
    ->name('signup.post')
    ->middleware('throttle:10,1');

// AJAX email availability check (rate-limited)
Route::get('/api/check-email', [App\Http\Controllers\RegisterController::class, 'checkEmail'])
    ->name('api.check-email')
    ->middleware('throttle:30,1');

// Account (profile) - requires auth
Route::middleware(['auth'])->get('/account', function () {
    return view('account');
})->name('account');

// previous direct-view route for /signup removed in favor of RegisterController

Route::middleware(['auth'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Movie detail (front-end show page) - requires authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/movies/{movie}', [App\Http\Controllers\MovieController::class, 'show'])
        ->whereNumber('movie')
        ->name('movies.show');
});

// Development helper: log in as the test movie user (only in local/dev)
Route::get('/dev/login-as-test', function () {
    if (!app()->environment('local') && !app()->environment('development')) {
        abort(404);
    }

    $user = \App\Models\User::first();
    if (!$user) {
        // create a quick user — pass plain password to model mutator
        $user = \App\Models\User::create([
            'name' => 'Dev User',
            'email' => 'dev@example.test',
            'password' => 'password',
        ]);
    }

    \Illuminate\Support\Facades\Auth::login($user);
    return redirect('/');
})->name('dev.login');

// Use a controller method so the view receives categories from the database.
Route::middleware(['auth'])->get('/admin/add-movie', [MovieController::class, 'create'])
    ->name('admin.movies.create');

// Store new movie (form POST from Add Movie page)
Route::middleware(['auth'])->post('/admin/add-movie', [MovieController::class, 'store'])
    ->name('admin.movies.store');

Route::get('/auth/redirect/{provider}', [SocialController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/callback/{provider}', [SocialController::class, 'callback'])->name('social.callback');

// Password reset (forgot password)
Route::get('/password/forgot', [App\Http\Controllers\PasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [App\Http\Controllers\PasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [App\Http\Controllers\PasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [App\Http\Controllers\PasswordController::class, 'reset'])->name('password.update');

// Routes that return views directly when requested as "only" endpoints.
// Accept both GET and POST so forms or fetch requests can target them.
Route::match(['get', 'post'], '/dashboard-only', function () {
    return view('dashboard'); // resources/views/dashboard.blade.php
})->name('dashboard.only');

Route::match(['get', 'post'], '/homepage-only', function () {
    return view('index'); // resources/views/index.blade.php
})->name('homepage.only');

// About page (static informational page)
Route::get('/about', function () {
    return view('about');
})->name('about');

// Category pages (e.g. DC Comics) - show movies attached to a category slug
Route::middleware(['auth'])->get('/categories/{slug}', [App\Http\Controllers\MovieController::class, 'byCategory'])
    ->name('categories.show');

// TMDB test route - server-side proxy to TMDB API. Reads credentials from config/services.php -> .env
Route::get('/tmdb/movie/{id}', [TmdbController::class, 'show'])
    ->whereNumber('id')
    ->name('tmdb.movie');

// Temporary debug route to inspect dashboard collections (local/dev only)
Route::get('/debug/dashboard-data', function () {
    if (!app()->environment('local') && !app()->environment('development')) {
        abort(404);
    }

    $mcu = \App\Models\Movie::where(function($q){
        $q->where('visibility','dashboard')->orWhere('visibility','both');
    })->where('dashboard_id', 2)->orderBy('created_at','desc')->get();

    $disney = collect();
    $disneyCat = \App\Models\Category::whereIn('slug', ['disney-plus-originals','disney-plus'])->first();
    if ($disneyCat) {
        $disney = \App\Models\Movie::where(function($q){
            $q->where('visibility','dashboard')->orWhere('visibility','both');
        })->where('dashboard_id', $disneyCat->id)->orderBy('created_at','desc')->get();
    }

    $dc = collect();
    $dcCat = \App\Models\Category::where('slug','dc-comics')->first();
    if ($dcCat) {
        $dc = \App\Models\Movie::where(function($q){
            $q->where('visibility','dashboard')->orWhere('visibility','both');
        })->where('dashboard_id', $dcCat->id)->orderBy('created_at','desc')->get();
    }

    $horror = collect();
    $hCat = \App\Models\Category::where('slug','horror')->first();
    if ($hCat) {
        $horror = $hCat->movies()->orderBy('created_at','desc')->get();
    }

    return response()->json([
        'mcu' => $mcu->map->only(['id','title','category_id','dashboard_id','visibility']),
        'disney' => $disney->map->only(['id','title','category_id','dashboard_id','visibility']),
        'dc' => $dc->map->only(['id','title','category_id','dashboard_id','visibility']),
        'horror' => $horror->map->only(['id','title','category_id','dashboard_id','visibility']),
    ]);
});
