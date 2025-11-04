<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class ResequenceCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resequence:categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resequence category ids to 1..N and update movie foreign keys accordingly';

    public function handle()
    {
        $this->info('Starting categories re-sequence...');

        // We'll order categories by current id (deterministic). If you prefer another order
        // (e.g., name or created_at) tell me and I can change this.
        $categories = Category::orderBy('id')->get();
        $count = $categories->count();
        if ($count === 0) {
            $this->info('No categories found. Nothing to do.');
            return 0;
        }

        // Build mapping oldId => newId
        $oldToNew = [];
        $newId = 1;
        foreach ($categories as $cat) {
            $oldToNew[$cat->id] = $newId++;
        }

        // Use an offset safely beyond current max id to avoid collisions
        $maxId = Category::max('id') ?? 0;
        $offset = max(1000, $maxId + 1000);

        DB::beginTransaction();

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // Move all categories to temporary ids (add offset)
            DB::table('categories')->update(['id' => DB::raw("id + {$offset}")]);
            $this->line("Moved all categories to temporary ids with offset {$offset}");

            // Assign new sequential ids 1..N according to mapping
            foreach ($oldToNew as $old => $target) {
                $tmpId = $old + $offset;
                DB::table('categories')->where('id', $tmpId)->update(['id' => $target]);
                $this->line("Assigned old {$old} -> new {$target}");
            }

            // Update movies FK: movies still reference old ids (original values)
            foreach ($oldToNew as $old => $target) {
                if ($old === $target) continue;
                $rows = DB::table('movies')->where('category_id', $old)->update(['category_id' => $target]);
                if ($rows) $this->line("Updated movies: {$rows} rows - category_id {$old} -> {$target}");
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            DB::commit();

            $this->info("Re-sequence completed: {$count} categories re-numbered 1..{$count}");
            return 0;
        } catch (\Throwable $e) {
            DB::rollBack();
            try { DB::statement('SET FOREIGN_KEY_CHECKS=1'); } catch (\Throwable $_) {}
            $this->error('Resequence failed: ' . $e->getMessage());
            return 1;
        }
    }
}
