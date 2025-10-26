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

        $movies = Movie::orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

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

        return view('dashboard', [
            'movies' => $movies,
            'mcuMovies' => $mcuMovies,
        ]);
    }
}
