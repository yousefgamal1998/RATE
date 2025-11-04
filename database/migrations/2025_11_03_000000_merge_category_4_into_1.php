<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Keep and remove IDs.
     * Adjust these if you want to target different ids.
     */
    protected int $keepId = 1; // keep this category
    protected int $removeId = 4; // remove this duplicate

    protected string $backupPath = '';

    public function __construct()
    {
        $this->backupPath = storage_path('app/category_merge_backup_remove_4.json');
    }

    /**
     * Run the migrations.
     * - Backup duplicate category row and affected rows list
     * - Reassign category_id from removeId -> keepId across all tables that have category_id
     * - Delete the duplicate category row
     */
    public function up(): void
    {
        $keepId = $this->keepId;
        $removeId = $this->removeId;

        DB::transaction(function () use ($keepId, $removeId) {
            $dbName = DB::getDatabaseName();

            $duplicate = DB::table('categories')->where('id', $removeId)->first();
            $keep = DB::table('categories')->where('id', $keepId)->first();

            if (!$duplicate) {
                // Nothing to do
                return;
            }

            if (!$keep) {
                throw new \RuntimeException("Target keep category id={$keepId} does not exist. Aborting merge.");
            }

            // Find all tables with a column named 'category_id'
            $tables = DB::table('information_schema.columns')
                ->select('table_name')
                ->where('table_schema', $dbName)
                ->where('column_name', 'category_id')
                ->distinct()
                ->pluck('table_name')
                ->toArray();

            $affected = [];

            foreach ($tables as $table) {
                // Determine primary key column for the table (fallback to 'id')
                $pk = DB::table('information_schema.columns')
                    ->where('table_schema', $dbName)
                    ->where('table_name', $table)
                    ->where('column_key', 'PRI')
                    ->value('column_name') ?: 'id';

                // Collect affected primary keys
                $ids = DB::table($table)->where('category_id', $removeId)->pluck($pk)->toArray();
                if (!empty($ids)) {
                    $affected[$table] = ['pk' => $pk, 'ids' => $ids];
                    // Update rows to point to the keepId
                    DB::table($table)->where('category_id', $removeId)->update(['category_id' => $keepId]);
                }
            }

            // Backup duplicate row and affected list to storage so down() can restore
            $backup = [
                'duplicate' => (array) $duplicate,
                'keep' => (array) $keep,
                'affected' => $affected,
                'merged_at' => now()->toDateTimeString(),
            ];

            // Make sure storage directory exists
            if (!is_dir(dirname($this->backupPath))) {
                @mkdir(dirname($this->backupPath), 0755, true);
            }

            file_put_contents($this->backupPath, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Finally delete the duplicate category row
            DB::table('categories')->where('id', $removeId)->delete();
        });
    }

    /**
     * Reverse the migrations.
     * - Recreate the duplicate category from the backup file (if present)
     * - Reassign affected rows back to the recreated category id
     */
    public function down(): void
    {
        if (!file_exists($this->backupPath)) {
            // Backup not present; cannot reliably revert.
            // We throw so the developer is aware. You can recreate the category manually if desired.
            throw new \RuntimeException('Backup file not found: ' . $this->backupPath . '. Cannot safely revert merge.');
        }

        $backup = json_decode((string) file_get_contents($this->backupPath), true);
        if (empty($backup) || empty($backup['duplicate'])) {
            throw new \RuntimeException('Invalid or empty backup file: ' . $this->backupPath);
        }

        DB::transaction(function () use ($backup) {
            $dbName = DB::getDatabaseName();

            $duplicate = $backup['duplicate'];
            $affected = $backup['affected'] ?? [];

            // Recreate the category row (do not attempt to force the original id)
            $data = $duplicate;
            unset($data['id']); // let DB assign a new id

            // If timestamps exist, ensure they are in the right format
            if (isset($data['created_at']) && empty($data['created_at'])) {
                $data['created_at'] = now();
            }
            if (isset($data['updated_at']) && empty($data['updated_at'])) {
                $data['updated_at'] = now();
            }

            $newId = DB::table('categories')->insertGetId($data);

            // Reassign affected rows back to the recreated category id using recorded PKs
            foreach ($affected as $table => $meta) {
                $pk = $meta['pk'] ?? 'id';
                $ids = $meta['ids'] ?? [];
                if (!empty($ids)) {
                    DB::table($table)->whereIn($pk, $ids)->update(['category_id' => $newId]);
                }
            }

            // Optionally remove backup file after successful revert
            @unlink(storage_path('app/category_merge_backup_remove_4.json'));
        });
    }
};
