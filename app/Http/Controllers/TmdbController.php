<?php

namespace App\Http\Controllers;

use App\Services\TmdbService;
use Illuminate\Http\Request;

class TmdbController extends Controller
{
    protected TmdbService $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        $this->tmdb = $tmdb;
    }

    /**
     * Simple endpoint to return TMDB movie data as JSON.
     * Example: GET /tmdb/movie/550
     */
    public function show(Request $request, int $id)
    {
        // Optionally allow ?append=credits,videos
        $append = [];
        if ($request->has('append')) {
            $append = explode(',', $request->query('append'));
        }

        try {
            $movie = $this->tmdb->getMovie($id, $append);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch from TMDB', 'message' => $e->getMessage()], 500);
        }

        return response()->json($movie);
    }
}
