<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    protected string $backupPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/category_seed_backup_' . date('Ymd_His') . '.json');
    }

    public function up(): void
    {
        $desired = [
            1 => 'Latest Movies',
            2 => 'Marvel Cinematic Universe',
            3 => 'DC Comics',
            4 => 'Disney+ Originals',
            5 => 'Horror',
        ];

        // Backup existing categories
        $backup = [
            'created_at' => now()->toDateTimeString(),
            'categories' => DB::table('categories')->orderBy('id')->get()->map(fn($r) => (array)$r)->toArray(),
        ];

        if (!is_dir(dirname($this->backupPath))) {
            @mkdir(dirname($this->backupPath), 0755, true);
        }
        file_put_contents($this->backupPath, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Upsert desired categories by id
        DB::transaction(function () use ($desired) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            foreach ($desired as $id => $name) {
                $slug = Str::slug($name);
                // Special-case Disney+ to a nicer slug
                if (stripos($name, 'Disney+') !== false) {
                    $slug = 'disney-plus-originals';
                }

                // Use updateOrInsert to set id explicitly
                DB::table('categories')->updateOrInsert(
                    ['id' => $id],
                    [
                        'name' => $name,
                        'slug' => $slug,
                        'description' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        });
    }

    public function down(): void
    {
        if (!file_exists($this->backupPath)) {
            throw new \RuntimeException('Backup not found: ' . $this->backupPath);
        }

        $backup = json_decode((string) file_get_contents($this->backupPath), true);
        if (empty($backup) || !isset($backup['categories'])) {
            throw new \RuntimeException('Invalid backup file: ' . $this->backupPath);
        }

        DB::transaction(function () use ($backup) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            // Truncate and re-insert original categories to restore exact previous state
            DB::table('categories')->truncate();
            foreach ($backup['categories'] as $cat) {
                $id = $cat['id'];
                $data = $cat;
                unset($data['id']);
                DB::table('categories')->insert(array_merge(['id' => $id], $data));
            }
            DB::statement('SET_FOREIGN_KEY_CHECKS=1');
        });
    }
};
