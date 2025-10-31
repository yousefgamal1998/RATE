<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\Movie;

class CleanupCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'categories:cleanup {--dry-run : Show what would be changed but do not modify the DB} {--yes|y : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge duplicate categories by name: reassign movies to a single canonical category and delete duplicates (safe, transactional).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $skipConfirm = (bool) $this->option('yes') || (bool) $this->option('y');

        $this->info('Scanning categories for duplicates by normalized name...');

        $categories = Category::all();
        $groups = $categories->groupBy(function (Category $c) {
            return trim(strtolower($c->name));
        });

        $duplicates = [];
        foreach ($groups as $name => $items) {
            if ($items->count() > 1) {
                $duplicates[$name] = $items->values();
            }
        }

        if (empty($duplicates)) {
            $this->info('No duplicate category names found. Nothing to do.');
            return 0;
        }

        $this->line('Found the following duplicate groups:');
        foreach ($duplicates as $name => $items) {
            $ids = $items->pluck('id')->implode(', ');
            $slugs = $items->pluck('slug')->unique()->implode(', ');
            $this->line(" - {$name} => ids: [{$ids}] slugs: [{$slugs}]");
        }

        if ($dry) {
            $this->info('Dry-run mode: no changes will be made. Use the command without --dry-run to apply changes.');
            return 0;
        }

        if (! $skipConfirm && ! $this->confirm('Proceed to merge duplicates and delete duplicate category rows?')) {
            $this->info('Aborted. No changes made.');
            return 1;
        }

        $this->info('Starting merge inside a database transaction...');

        DB::transaction(function () use ($duplicates) {
            foreach ($duplicates as $name => $items) {
                // Choose keeper deterministically: lowest id
                $items = $items->sortBy('id')->values();
                $keeper = $items->first();
                $dups = $items->slice(1);

                $this->line("Merging group '{$name}': keeping id={$keeper->id} (slug={$keeper->slug})");

                foreach ($dups as $dup) {
                    $this->line(" - Reassigning movies from category {$dup->id} -> {$keeper->id}...");
                    Movie::where('category_id', $dup->id)->update(['category_id' => $keeper->id]);
                    $this->line(" - Deleting duplicate category id={$dup->id} (slug={$dup->slug})...");
                    // Use delete() to fire model events if needed
                    $dup->delete();
                }
            }
        });

        $this->info('Cleanup complete. Duplicate categories merged.');
        return 0;
    }
}
