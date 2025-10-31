<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Illuminate\Support\Facades\Schema;
use App\Services\TmdbService;
use App\Models\Movie;

class SyncTmdbMovie extends Command
{
    /**
     * The name and signature of the console command.
     * Accepts either a TMDB id or a local movie id via --movie.
     *
     * @var string
     */
    // Added optional --category and --category_id to allow assigning a category/universe when syncing.
    protected $signature = 'tmdb:sync-movie {tmdbId? : The TMDB movie id to sync} {--movie= : Local movie id to update (optional)} {--append= : Comma-separated list of append_to_response values} {--dry-run : Show changes that would be made but do not persist them} {--category= : Optional category/universe to assign to the movie} {--category_id= : Optional numeric category id to assign to the movie} {--dashboard_id= : Optional numeric dashboard id to assign to the movie} {--visibility= : Optional visibility (dashboard, homepage, both, add-movie) to assign to the movie}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch a movie from TMDB and update/create a local Movie record';

    protected TmdbService $tmdb;

    public function __construct(TmdbService $tmdb)
    {
        parent::__construct();

        $this->tmdb = $tmdb;
    }

    public function handle()
    {
        $io = new SymfonyStyle($this->input, $this->output);
        // Validate TMDB configuration before attempting network calls.
        $apiKey = config('services.tmdb.api_key');
        $readToken = config('services.tmdb.read_access_token');

        if (empty($apiKey) && empty($readToken)) {
            $io->error("TMDB credentials are not configured. Set either TMDB_API_KEY or TMDB_READ_ACCESS_TOKEN in your .env or configure 'services.tmdb' in config/services.php.");
            $io->text('Example (.env):');
            $io->text('  TMDB_API_KEY=your_v3_api_key_here');
            $io->text('  TMDB_READ_ACCESS_TOKEN=Bearer your_v4_read_token_here');
            $io->text('If you only have a v4 read access token, prefix it with "Bearer " when copying into .env.');
            return 5;
        }

        $tmdbId = $this->argument('tmdbId');
        $movieId = $this->option('movie');
        $append = $this->option('append');

        if (empty($tmdbId) && empty($movieId)) {
            $io->error('Provide either a TMDB id argument or --movie <local id> to sync.');
            return 1;
        }

        // If local movie id provided but no tmdb id, try to read tmdb_id from movie
        if (empty($tmdbId) && $movieId) {
            $movie = Movie::find($movieId);
            if (!$movie) {
                $io->error('Local movie not found: ' . $movieId);
                return 1;
            }
            if (empty($movie->tmdb_id)) {
                $io->error('Local movie does not have tmdb_id. Provide tmdbId argument or set tmdb_id on the movie.');
                return 1;
            }
            $tmdbId = $movie->tmdb_id;
        }

        $appendArr = [];
        if (!empty($append)) {
            $appendArr = array_map('trim', explode(',', $append));
        }

        // Always ensure we request credits so production_companies (and similar) are available
        // for Movie::updateFromTmdb which relies on production_companies to detect Marvel.
        if (!in_array('credits', $appendArr, true)) {
            $appendArr[] = 'credits';
        }

    $category = $this->option('category');
    $categoryId = $this->option('category_id');

    // New options: dashboard_id and visibility
    $dashboardId = $this->option('dashboard_id');
    $visibility = $this->option('visibility');

    $io->text('Fetching TMDB movie: ' . $tmdbId);

        try {
            $tmdbData = $this->tmdb->getMovie((int)$tmdbId, $appendArr);
        } catch (\Exception $e) {
            $io->error('Failed to fetch from TMDB: ' . $e->getMessage());
            return 2;
        }

        if (empty($tmdbData)) {
            $io->error('No data returned from TMDB for id: ' . $tmdbId);
            return 3;
        }

        // If a local movie id was provided, update that one. Otherwise try to find by tmdb_id, or create.
        if (!empty($movieId)) {
            $movie = Movie::find($movieId);
            if (!$movie) {
                $io->error('Local movie not found: ' . $movieId);
                return 4;
            }
        } else {
            $movie = Movie::where('tmdb_id', $tmdbId)->first();
        }

        $isDry = $this->option('dry-run');

        if (!$movie) {
            $io->warning('No existing local movie found for TMDB id ' . $tmdbId . '. A new Movie would be created.');
            $movie = new Movie();
        }

        // Prepare attributes from TMDB
        $attrs = Movie::attributesFromTmdb($tmdbData);

        // If a category or category_id was provided via CLI, include them in the proposed attributes
        // for dry-run output. Persisting is attempted only if the DB has a suitable column.
        if (!empty($category)) {
            $attrs['category'] = $category;
        }
        if (!empty($categoryId)) {
            $attrs['category_id'] = (int) $categoryId;
        }

        // Include proposed dashboard settings in dry-run output when provided
        if (!empty($dashboardId)) {
            $attrs['dashboard_id'] = (int) $dashboardId;
        }
        if (!empty($visibility)) {
            $attrs['visibility'] = $visibility;
        }

        if ($isDry) {
            $movieTitle = $movie->title ?? ("TMDB id: $tmdbId (new)");
            $io->section($movieTitle);

            $io->text('<info>Dry run: proposed changes</info>');

            // Compare current model attributes with proposed ones
            $fields = ['title','description','year','duration','genres','rating_decimal','user_score','tmdb_id','image_path','slug','category','universe','category_id','dashboard_id','visibility'];

            $changes = [];
            foreach ($fields as $field) {
                $current = $movie->{$field} ?? null;
                $proposed = $attrs[$field] ?? null;

                // Normalize arrays for comparison
                if (is_array($current)) {
                    $currentOut = json_encode($current, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
                } else {
                    $currentOut = (string) $current;
                }

                if (is_array($proposed)) {
                    $proposedOut = json_encode($proposed, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
                } else {
                    $proposedOut = (string) $proposed;
                }

                if ($currentOut !== $proposedOut) {
                    if (empty($current) && !empty($proposed)) {
                        $changes[] = sprintf('<fg=green>NEW</>  %-15s : %s', $field, $proposedOut);
                    } else {
                        $changes[] = sprintf('<fg=yellow>CHG</>  %-15s : %s  ->  %s', $field, $currentOut, $proposedOut);
                    }
                }
            }

            if (empty($changes)) {
                $io->text('No changes detected.');
            } else {
                $io->listing($changes);
            }

            $io->text('<comment>End proposed changes (no DB changes applied)</comment>');
            return 0;
        }

        // Not a dry-run: persist changes

    $movie->updateFromTmdb($tmdbData);

    // If a category_id or category was passed, try to persist it. Prefer numeric category_id when provided.
    if (!empty($categoryId)) {
        if (Schema::hasColumn('movies', 'category_id')) {
            // Check the Category exists to avoid FK constraint failures
            $cat = \App\Models\Category::find((int) $categoryId);
            if ($cat) {
                $movie->forceFill(['category_id' => (int) $categoryId])->save();
                $io->text('Category id saved to `category_id` column.');
            } else {
                // If a textual --category was provided, create the category and assign it.
                if (!empty($category)) {
                    $slug = \Illuminate\Support\Str::slug($category);

                    // Try to find existing category by slug or name first
                    $existing = \App\Models\Category::where('slug', $slug)
                        ->orWhere('name', $category)
                        ->first();

                    if ($existing) {
                        $movie->forceFill(['category_id' => $existing->id])->save();
                        $io->text('Assigned existing category (' . $existing->id . ') to the movie.');
                    } else {
                        // Attempt to create (or fetch) the category. Use firstOrCreate to reduce the small
                        // race window and catch any thrown exception (including unique constraint exceptions)
                        // to re-query and assign if another process created it concurrently.
                        try {
                            $new = \App\Models\Category::firstOrCreate(
                                ['slug' => $slug],
                                ['name' => $category]
                            );

                            $movie->forceFill(['category_id' => $new->id])->save();
                            $io->text('Category did not exist; created or fetched category (' . $new->id . ') and assigned it.');
                        } catch (\Throwable $e) {
                            // Another process may have created it concurrently. Try to re-fetch.
                            $existing = \App\Models\Category::where('slug', $slug)
                                ->orWhere('name', $category)
                                ->first();
                            if ($existing) {
                                $movie->forceFill(['category_id' => $existing->id])->save();
                                $io->text('Category was created concurrently; assigned existing category (' . $existing->id . ').');
                            } else {
                                $io->warning('Failed to create category and could not locate it afterwards: ' . $e->getMessage());
                            }
                        }
                    }
                } else {
                    $io->warning('Category id ' . $categoryId . ' does not exist. The value was not saved. Create the category first or pass --category to auto-create.');
                }
            }
        } else {
            $io->warning('Received --category_id but no `category_id` column exists on movies table. The value was not saved. Run a migration to add the column if you want persistence.');
        }
    } elseif (!empty($category)) {
        // Fallback to text category/universe if numeric id not provided
        if (Schema::hasColumn('movies', 'category')) {
            $movie->forceFill(['category' => $category])->save();
            $io->text('Category saved to `category` column.');
        } elseif (Schema::hasColumn('movies', 'universe')) {
            $movie->forceFill(['universe' => $category])->save();
            $io->text('Category saved to `universe` column.');
        } else {
            $io->warning('Received --category but no `category` or `universe` column exists on movies table. The value was not saved. Run a migration to add a column if you want persistence.');
        }
    }

    // Persist dashboard_id or visibility if provided
    if (!empty($dashboardId)) {
        if (Schema::hasColumn('movies', 'dashboard_id')) {
            $movie->forceFill(['dashboard_id' => (int) $dashboardId])->save();
            $io->text('Dashboard id saved to `dashboard_id` column.');
        } else {
            $io->warning('Received --dashboard_id but no `dashboard_id` column exists on movies table. The value was not saved. Run a migration to add the column if you want persistence.');
        }
    }

    if (!empty($visibility)) {
        if (Schema::hasColumn('movies', 'visibility')) {
            $movie->forceFill(['visibility' => $visibility])->save();
            $io->text('Visibility saved to `visibility` column.');
        } else {
            $io->warning('Received --visibility but no `visibility` column exists on movies table. The value was not saved. Run a migration to add the column if you want persistence.');
        }
    }

    $io->success('Movie synced: ' . $movie->id . ' — ' . $movie->title);

        return 0;
    }
}
