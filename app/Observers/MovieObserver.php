<?php

namespace App\Observers;

use App\Models\Movie;
use App\Services\TmdbService;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Exception;

class MovieObserver
{
    protected TmdbService $tmdb;

    public function __construct()
    {
        // resolve the TMDB service from the container
        $this->tmdb = app(TmdbService::class);
    }

    /**
     * Handle the Movie "created" event.
     * When a movie is created, try to fetch a suitable YouTube trailer
     * from TMDB and save an embeddable URL to the movie's video_url field.
     */
    public function created(Movie $movie): void
    {
        // if a video_url already exists, nothing to do
        if (!empty($movie->video_url)) {
            return;
        }

        // need a tmdb_id to query TMDB
        if (empty($movie->tmdb_id)) {
            return;
        }

        try {
            // Also request credits so the API returns production_companies if needed elsewhere
            $data = $this->tmdb->getMovie((int) $movie->tmdb_id, ['videos', 'credits']);
        } catch (Exception $e) {
            logger()->debug('MovieObserver: TMDB getMovie failed: ' . $e->getMessage());
            return;
        }

        if (empty($data['videos']['results']) || !is_array($data['videos']['results'])) {
            return;
        }

        // Rank videos: prefer official Trailer, then Trailer, then Teaser, etc.
        $candidates = $data['videos']['results'];
        usort($candidates, function ($a, $b) {
            $rank = ['Trailer' => 3, 'Teaser' => 2, 'Clip' => 1];
            $ra = ($rank[$a['type']] ?? 0) + (!empty($a['official']) ? 0.5 : 0);
            $rb = ($rank[$b['type']] ?? 0) + (!empty($b['official']) ? 0.5 : 0);
            // higher score first
            return $rb <=> $ra;
        });

        $selectedKey = null;

        foreach ($candidates as $c) {
            if (empty($c['site']) || strtolower($c['site']) !== 'youtube') {
                continue;
            }
            if (empty($c['key'])) {
                continue;
            }

            $key = $c['key'];

            // quick embeddable check; isYoutubeEmbeddable returns false on network errors
            try {
                if ($this->tmdb->isYoutubeEmbeddable($key)) {
                    $selectedKey = $key;
                    break;
                }
            } catch (Exception $e) {
                logger()->debug('MovieObserver: YouTube oEmbed check failed: ' . $e->getMessage());
                // continue to next candidate
            }
        }

        if (empty($selectedKey)) {
            return;
        }

        $embed = $this->tmdb->buildYoutubeEmbedUrl($selectedKey);

        // Save without re-triggering model events (avoid recursion)
        try {
            if (method_exists($movie, 'saveQuietly')) {
                $movie->forceFill(['video_url' => $embed])->saveQuietly();
            } else {
                EloquentModel::withoutEvents(function () use ($movie, $embed) {
                    $movie->forceFill(['video_url' => $embed])->save();
                });
            }
        } catch (Exception $e) {
            logger()->debug('MovieObserver: failed to persist video_url: ' . $e->getMessage());
        }
    }
}
