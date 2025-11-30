<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected string $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/category_merge_backup_safe_' . date('Ymd_His') . '.json');
    }

    public function up(): void
    {
        $dbName = DB::getDatabaseName();

        // Backup current categories and references
        $backup = [
            'created_at' => now()->toDateTimeString(),
            'categories' => DB::table('categories')->orderBy('id')->get()->map(fn($r) => (array)$r)->toArray(),
            'references' => [],
        ];

        // find tables with category_id
        $tables = DB::table('information_schema.columns')
            ->select('table_name')
            ->where('table_schema', $dbName)
            ->where('column_name', 'category_id')
            ->distinct()
            ->pluck('table_name')
            ->toArray();

        foreach ($tables as $table) {
            $backup['references'][$table] = DB::table($table)->whereNotNull('category_id')->get()->map(fn($r) => (array)$r)->toArray();
        }

        if (!is_dir(dirname($this->backupPath))) {
            @mkdir(dirname($this->backupPath), 0755, true);
        }
        file_put_contents($this->backupPath, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Detect duplicates by slug (preferred) or lower(name)
        $categories = collect($backup['categories']);
        $groups = [];
        foreach ($categories as $cat) {
            $key = !empty($cat['slug']) ? mb_strtolower(trim($cat['slug'])) : mb_strtolower(trim($cat['name']));
            if ($key === '') {
                $key = '::empty::' . $cat['id'];
            }
            $groups[$key][] = $cat;
        }

        $mergeMap = []; // duplicateId => keepId

        foreach ($groups as $key => $items) {
            if (count($items) <= 1) continue;
            usort($items, fn($a, $b) => $a['id'] <=> $b['id']);
            $keep = $items[0]['id'];
            for ($i = 1; $i < count($items); $i++) {
                $mergeMap[$items[$i]['id']] = $keep;
            }
        }

        if (empty($mergeMap)) {
            // nothing to do
            return;
        }

        DB::transaction(function () use ($mergeMap, $tables) {
            foreach ($tables as $table) {
                foreach ($mergeMap as $old => $keep) {
                    DB::table($table)->where('category_id', $old)->update(['category_id' => $keep]);
                }
            }

            // delete duplicate category rows
            DB::table('categories')->whereIn('id', array_keys($mergeMap))->delete();
        });

        // Append merge_map to backup for traceability
        $meta = json_decode((string) file_get_contents($this->backupPath), true);
        $meta['merge_map'] = $mergeMap;
        file_put_contents($this->backupPath, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function down(): void
    {
        // Attempt to restore from backup if present
        if (!file_exists($this->backupPath)) {
            throw new \RuntimeException('Backup file not found: ' . $this->backupPath . '. Cannot revert safely.');
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

        DB::transaction(function () use ($backup, $tables) {
            // Ensure categories table has the original rows (we will insert any missing)
            foreach ($backup['categories'] as $cat) {
                $exists = DB::table('categories')->where('id', $cat['id'])->exists();
                if (!$exists) {
                    $data = $cat;
                    unset($data['id']);
                    DB::table('categories')->insert(array_merge(['id' => $cat['id']], $data));
                }
            }

            // Restore references
            foreach ($tables as $table) {
                if (empty($backup['references'][$table])) continue;
                $pk = DB::table('information_schema.columns')
                    ->where('table_schema', DB::getDatabaseName())
                    ->where('table_name', $table)
                    ->where('column_key', 'PRI')
                    ->value('column_name') ?: 'id';

                foreach ($backup['references'][$table] as $row) {
                    if (!isset($row[$pk])) continue;
                    DB::table($table)->where($pk, $row[$pk])->update(['category_id' => $row['category_id'] ?? null]);
                }
            }
        });
    }
};
