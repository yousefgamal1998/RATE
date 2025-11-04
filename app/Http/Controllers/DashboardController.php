<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;

class DashboardController extends Controller
{
    /**
     * Show the dashboard with latest movies (paginated).
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 12);

        // Prefer showing the canonical 'latest-movies' category for the dashboard's
        // Latest Movies carousel. Fall back to global recent movies if the
        // category does not exist.
        $latestCat = \App\Models\Category::where('slug', 'latest-movies')->first();
        if ($latestCat) {
            $movies = $latestCat->movies()->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        } else {
            $movies = Movie::orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        }

        // If request expects JSON (for AJAX load more) return JSON
        if ($request->wantsJson()) {
            return response()->json($movies);
        }

        // Also provide MCU-specific movies grouped by dashboard_id = 2
        $mcuMovies = Movie::where(function($q){
                $q->where('visibility','dashboard')
                  ->orWhere('visibility','both');
            })
            ->where('dashboard_id', 2)
            ->orderBy('created_at', 'desc')
            ->get();

        // Disney carousel (use dashboard_id = category.id when available)
        $disneyMovies = collect();
        try {
            $disneyCat = \App\Models\Category::whereIn('slug', ['disney-plus-originals','disney-plus'])->first();
            if ($disneyCat) {
                $disneyMovies = Movie::where(function($q){
                        $q->where('visibility','dashboard')
                          ->orWhere('visibility','both');
                    })
                    ->where('dashboard_id', $disneyCat->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        } catch (\Exception $e) {
            // fallback to empty collection
            $disneyMovies = collect();
        }

        // DC carousel
        $dcMovies = collect();
        try {
            $dcCat = \App\Models\Category::where('slug', 'dc-comics')->first();
            if ($dcCat) {
                $dcMovies = Movie::where(function($q){
                        $q->where('visibility','dashboard')
                          ->orWhere('visibility','both');
                    })
                    ->where('dashboard_id', $dcCat->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        } catch (\Exception $e) {
            $dcMovies = collect();
        }

        return view('dashboard', [
            'movies' => $movies,
            'mcuMovies' => $mcuMovies,
            'disneyMovies' => $disneyMovies,
            'dcMovies' => $dcMovies,
        ]);
    }
}
