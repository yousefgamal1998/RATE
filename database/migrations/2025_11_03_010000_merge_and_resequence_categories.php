<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Merge duplicate categories (by slug/name) keeping the lowest id, reassign all references,
 * then resequence `categories.id` to be consecutive (1,2,3...).
 *
 * WARNING: This migration modifies primary keys and foreign keys. It disables foreign key
 * checks while applying changes and writes a full backup file to storage/app that can be
 * used to restore the original state. Always backup your database before running on
 * production. This is intended for use on a local or maintenance window.
 */
return new class extends Migration
{
    protected string $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/category_resequence_backup_' . date('Ymd_His') . '.json');
    }

    public function up(): void
    {
        // Step 0: gather DB name
        $dbName = DB::getDatabaseName();

        // If the `categories` table does not exist, return early.
        // This avoids QueryException during deployments where the table
        // may not have been created yet.
        if (! DB::getSchemaBuilder()->hasTable('categories')) {
            return;
        }
        // We'll collect a full backup snapshot of categories and any rows referencing category_id
        $backup = [
            'created_at' => now()->toDateTimeString(),
            'categories' => DB::table('categories')->orderBy('id')->get()->map(function ($r) { return (array) $r; })->toArray(),
            'references' => [],
        ];

        // Find all tables that have a 'category_id' column
        $tables = DB::table('information_schema.columns')
            ->select('table_name')
            ->where('table_schema', $dbName)
            ->where('column_name', 'category_id')
            ->distinct()
            ->pluck('table_name')
            ->toArray();

        // For each such table, backup rows that reference any category (we'll pull all rows with category_id not null)
        foreach ($tables as $table) {
            $backup['references'][$table] = DB::table($table)->whereNotNull('category_id')->get()->map(function ($r) { return (array) $r; })->toArray();
        }

        // Save backup to storage
        if (!is_dir(dirname($this->backupPath))) {
            @mkdir(dirname($this->backupPath), 0755, true);
        }
        file_put_contents($this->backupPath, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Step 1: Detect duplicates by slug or normalized name and map them to canonical ids (lowest id wins)
        $categories = collect($backup['categories']);

        // Build grouping key: prefer slug, fallback to lower(name)
        $groups = [];
        foreach ($categories as $cat) {
            $key = !empty($cat['slug']) ? mb_strtolower(trim($cat['slug'])) : mb_strtolower(trim($cat['name']));
            if ($key === '') {
                $key = '::empty::' . $cat['id'];
            }
            $groups[$key][] = $cat;
        }

        $mergeMap = []; // oldId => keepId
        foreach ($groups as $key => $items) {
            if (count($items) <= 1) continue;
            // sort by id to keep the smallest id
            usort($items, function ($a, $b) { return $a['id'] <=> $b['id']; });
            $keep = $items[0]['id'];
            for ($i = 1; $i < count($items); $i++) {
                $mergeMap[$items[$i]['id']] = $keep;
            }
        }

        // Step 2: Apply merges - reassign category_id from duplicate -> keep across all referencing tables
        DB::transaction(function () use ($mergeMap, $tables) {
            if (empty($mergeMap)) {
                // nothing to merge
                return;
            }

            foreach ($tables as $table) {
                foreach ($mergeMap as $old => $keep) {
                    DB::table($table)->where('category_id', $old)->update(['category_id' => $keep]);
                }
            }

            // Delete duplicate category rows
            DB::table('categories')->whereIn('id', array_keys($mergeMap))->delete();
        });

        // Step 3: Resequence IDs to be consecutive
        // Build list of remaining category ids ordered by current id ascending
        $remaining = DB::table('categories')->orderBy('id')->pluck('id')->toArray();
        $mapping = []; // oldId => newSequentialId
        $seq = 1;
        foreach ($remaining as $oldId) {
            $mapping[$oldId] = $seq++;
        }

        if (empty($mapping)) {
            // nothing more to do
            return;
        }

        // We'll use temporary negative ids to avoid conflicts, and we will disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Update categories ids to temporary negative new ids
        foreach ($mapping as $old => $new) {
            DB::table('categories')->where('id', $old)->update(['id' => -$new]);
        }

        // Update referencing tables to temporary negative ids as well
        foreach ($tables as $table) {
            foreach ($mapping as $old => $new) {
                DB::table($table)->where('category_id', $old)->update(['category_id' => -$new]);
            }
        }

        // Now set categories ids to final positive new ids
        foreach ($mapping as $old => $new) {
            DB::table('categories')->where('id', -$new)->update(['id' => $new]);
        }

        // And set referencing tables category_id to final positive new ids
        foreach ($tables as $table) {
            foreach ($mapping as $old => $new) {
                DB::table($table)->where('category_id', -$new)->update(['category_id' => $new]);
            }
        }

        // Ensure AUTO_INCREMENT is set to max(id)+1
        $maxId = DB::table('categories')->max('id') ?: 0;
        $next = $maxId + 1;
        DB::statement("ALTER TABLE `categories` AUTO_INCREMENT = {$next}");

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Save also mapping in the backup file for potential down()
        $meta = json_decode((string) file_get_contents($this->backupPath), true);
        $meta['merge_map'] = $mergeMap;
        $meta['resequence_map'] = $mapping;
        file_put_contents($this->backupPath, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function down(): void
    {
        // The down migration attempts to restore state from the backup file created in up().
        if (!file_exists($this->backupPath)) {
            throw new \RuntimeException('Backup file not found: ' . $this->backupPath . '. Cannot safely revert.');
        }

        $backup = json_decode((string) file_get_contents($this->backupPath), true);
        if (empty($backup) || empty($backup['categories'])) {
            throw new \RuntimeException('Invalid backup file: ' . $this->backupPath);
        }

        $dbName = DB::getDatabaseName();
        $tables = DB::table('information_schema.columns')
            ->select('table_name')
            ->where('table_schema', $dbName)
            ->where('column_name', 'category_id')
            ->distinct()
            ->pluck('table_name')
            ->toArray();

        // We will restore categories table from scratch and then update referencing rows
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::transaction(function () use ($backup, $tables) {
            // Clear current categories
            DB::table('categories')->truncate();

            // Re-insert original categories in original order, preserving original ids as much as possible
            foreach ($backup['categories'] as $cat) {
                $id = $cat['id'];
                $data = $cat;
                unset($data['id']);
                DB::table('categories')->insert(array_merge(['id' => $id], $data));
            }

            // Reapply original references
            foreach ($tables as $table) {
                if (empty($backup['references'][$table])) continue;
                // We'll update rows by primary key; if primary key is 'id' we can use that
                $pk = DB::table('information_schema.columns')
                    ->where('table_schema', DB::getDatabaseName())
                    ->where('table_name', $table)
                    ->where('column_key', 'PRI')
                    ->value('column_name') ?: 'id';

                foreach ($backup['references'][$table] as $row) {
                    if (!isset($row[$pk])) continue;
                    $pkVal = $row[$pk];
                    // Update category_id back to backed up value
                    DB::table($table)->where($pk, $pkVal)->update(['category_id' => $row['category_id'] ?? null]);
                }
            }
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
