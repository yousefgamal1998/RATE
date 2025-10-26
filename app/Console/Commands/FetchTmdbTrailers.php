<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Models\Movie;
use App\Services\TmdbService;

class FetchTmdbTrailers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tmdb:fetch-trailers {--limit= : Maximum number of movies to process} {--chunk=100 : Chunk size for processing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch official embeddable trailers from TMDB for movies and persist tmdb_id / video_url where missing.';

    protected TmdbService $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        parent::__construct();
        $this->tmdb = $tmdb;
    }

    public function handle()
    {
        $io = new SymfonyStyle($this->input, $this->output);
        $limit = $this->option('limit') ? intval($this->option('limit')) : null;
        $chunkSize = intval($this->option('chunk') ?? 100);

        $query = Movie::query()
            ->where(function($q){
                $q->whereNull('video_url')->orWhere('video_url', '');
            });

        if ($limit) {
            $query->limit($limit);
        }

        $total = $query->count();
        if ($total === 0) {
            $io->info('No movies found that need trailers.');
            return 0;
        }

        $io->text("Processing {$total} movies in chunks of {$chunkSize}...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $processed = 0;

    $query->chunk($chunkSize, function($movies) use (&$bar, &$processed, $io) {
            foreach ($movies as $movie) {
                try {
                    $processed++;

                    // Attempt to find tmdb id if missing
                    $tmdbId = $movie->tmdb_id ?? null;
                    $foundTmdbId = null;

                    if (!$tmdbId) {
                        $q = ['query' => $movie->title];
                        if ($movie->year) $q['year'] = $movie->year;

                        $search = $this->tmdb->get('search/movie', $q);
                        $results = $search['results'] ?? [];
                        if (!empty($results)) {
                            $best = $results[0];
                            $tmdbId = $best['id'] ?? null;
                            $foundTmdbId = $tmdbId;
                        }
                    }

                    $tmdbTrailer = null;

                    if ($tmdbId) {
                        $movieData = $this->tmdb->getMovie($tmdbId, ['videos']);
                        $videos = $movieData['videos']['results'] ?? [];

                        // Prefer official YouTube trailer, then any YouTube trailer, then any YouTube video
                        $isEmbeddable = function($key) {
                            try {
                                return $this->tmdb->isYoutubeEmbeddable($key);
                            } catch (\Exception $e) {
                                return false;
                            }
                        };

                        // 1) Official YouTube trailer
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

                        // 2) Any YouTube trailer
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

                    // Save tmdb_id if discovered
                    if ($foundTmdbId && !$movie->tmdb_id) {
                        $movie->tmdb_id = $foundTmdbId;
                    }

                    // Save video_url if we found a trailer and movie doesn't have one
                    if (!empty($tmdbTrailer) && empty($movie->video_url)) {
                        $movie->video_url = $tmdbTrailer;
                    }

                    // Persist if changed
                    if ($movie->isDirty()) {
                        $movie->save();
                    }

                    // Be nice to API rate limits
                    usleep(200000); // 200ms
                    } catch (\Exception $e) {
                        $io->warning("Failed for movie ID {$movie->id} ({$movie->title}): {$e->getMessage()}");
                    }

                $bar->advance();
            }
        });

    $bar->finish();
    $io->newLine(2);
    $io->success("Done. Processed {$processed} movies.");

        return 0;
    }
}
