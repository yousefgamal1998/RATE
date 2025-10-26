<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Models\Movie;
use App\Services\TmdbService;

class FillTrailerUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trailers:fill {--limit= : Maximum number of movies to process} {--no-embed-check : Skip YouTube oEmbed embeddability checks}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find movies with empty video_url, get official embeddable trailers from TMDB/YouTube, and persist them.';

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
        $skipEmbedCheck = $this->option('no-embed-check');

        $query = Movie::query()->where(function($q){
            $q->whereNull('video_url')->orWhere('video_url', '');
        });

        if ($limit) $query->limit($limit);

        $total = $query->count();
        if ($total === 0) {
            $io->info('No movies found that need trailers.');
            return 0;
        }

        $io->text("Processing {$total} movies...");

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $processed = 0;

    $query->chunk(50, function($movies) use (&$bar, &$processed, $skipEmbedCheck, $io) {
            foreach ($movies as $movie) {
                $processed++;
                try {
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

                        $isEmbeddable = function($key) use ($skipEmbedCheck) {
                            if ($skipEmbedCheck) return true;
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

                    if ($foundTmdbId && !$movie->tmdb_id) {
                        $movie->tmdb_id = $foundTmdbId;
                    }

                    if (!empty($tmdbTrailer) && empty($movie->video_url)) {
                        $movie->video_url = $tmdbTrailer;
                    }

                    if ($movie->isDirty()) {
                        $movie->save();
                    }

                    // avoid hammering APIs
                    usleep(150000);
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
