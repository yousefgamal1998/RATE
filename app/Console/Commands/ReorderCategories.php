<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class ReorderCategories extends Command
{
    /**
     * The name and signature of the console command.
     * Map slugs to desired numeric ids (1..5 by default).
     *
     * @var string
     */
    protected $signature = 'reorder:categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reassign dashboard category ids to 1..5 and update movies FK accordingly';

    public function handle()
    {
        $this->info('Starting category reorder...');

        // Desired mapping: slug => id
        $mapping = [
            'latest-movies' => 1,
            'mcu' => 2,
            'dc-comics' => 3,
            'disney-plus' => 4,
            'horror' => 5,
        ];

        $slugs = array_keys($mapping);

        // Ensure categories exist (create if missing)
        foreach ($slugs as $slug) {
            Category::firstOrCreate(['slug' => $slug], ['name' => ucwords(str_replace(['-','+'], [' ', '+'], $slug))]);
        }

        $existing = Category::whereIn('slug', $slugs)->get()->keyBy('slug');

        // Build old -> new mapping
        $oldToNew = [];
        foreach ($mapping as $slug => $newId) {
            $oldId = $existing[$slug]->id ?? null;
            if ($oldId === null) {
                $this->error('Missing category after creation: ' . $slug);
                return 1;
            }
            $oldToNew[$oldId] = $newId;
        }

        // Compute an offset large enough to avoid collisions
        $maxId = Category::max('id') ?? 0;
        $offset = max(1000, $maxId + 1000);

        DB::beginTransaction();

        try {
            // Disable foreign key checks to allow primary key updates
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Move ALL categories to temporary ids to avoid collisions with target ids.
            // This ensures we free any numeric target ids even if unrelated rows currently use them.
            DB::table('categories')->update(['id' => DB::raw("id + {$offset}")]);
            $this->line("Moved all categories to temporary ids with offset {$offset}");

            // Now set each mapped category from its temporary id to the desired target id
            $targetIds = array_values($mapping);
            foreach ($mapping as $slug => $targetId) {
                $oldId = $existing[$slug]->id;
                $tmpId = $oldId + $offset;
                DB::table('categories')->where('id', $tmpId)->update(['id' => $targetId]);
                $this->info("Assigned {$slug} -> id {$targetId}");
            }

            // Update movies FK references from old ids to new ids (movies still reference old ids)
            foreach ($oldToNew as $old => $new) {
                if ($old === $new) continue;
                $count = DB::table('movies')->where('category_id', $old)->update(['category_id' => $new]);
                $this->line("Updated movies: category_id {$old} -> {$new} (rows: {$count})");
            }

            // Handle potential collisions: some other rows may have originally used the numeric
            // target ids (e.g. another category originally had id=2). Those rows are currently
            // at id = original + offset and would collide when we subtract the offset.
            // Move those colliding rows to fresh new ids (beyond current max) first.
            $colliding = DB::table('categories')
                ->where('id', '>', $offset)
                ->whereRaw('(`id` - ' . (int)$offset . ') IN (' . implode(',', $targetIds) . ')')
                ->pluck('id')
                ->toArray();

            $currentMax = DB::table('categories')->max('id') ?? 0;
            foreach ($colliding as $curId) {
                $currentMax++;
                DB::table('categories')->where('id', $curId)->update(['id' => $currentMax]);
                $this->line("Moved colliding temporary id {$curId} -> {$currentMax}");
            }

            // Now restore remaining categories (those whose original id won't collide)
            DB::table('categories')
                ->where('id', '>', $offset)
                ->whereRaw('(`id` - ' . (int)$offset . ') NOT IN (' . implode(',', $targetIds) . ')')
                ->update(['id' => DB::raw("id - {$offset}")]);

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            DB::commit();
            $this->info('Category reorder completed successfully.');
            return 0;
        } catch (\Throwable $e) {
            DB::rollBack();
            // Try to re-enable FK checks before exiting
            try { DB::statement('SET FOREIGN_KEY_CHECKS=1'); } catch (\Throwable $_) {}
            $this->error('Failed to reorder categories: ' . $e->getMessage());
            return 1;
        }
    }
}
