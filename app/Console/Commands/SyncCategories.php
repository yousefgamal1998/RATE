<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Category;

class SyncCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'categories:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure the default categories exist in the database (Latest, MCU, Disney Plus)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Ensuring default categories exist...');

        $defaults = [
            ['slug' => 'latest', 'name' => 'Latest Movies', 'description' => 'Recently added movies'],
            ['slug' => 'mcu', 'name' => 'Marvel Cinematic Universe', 'description' => 'Movies and series in the Marvel Cinematic Universe'],
            ['slug' => 'disney-plus', 'name' => 'Disney Plus', 'description' => 'Movies and originals featured on Disney Plus'],
        ];

        foreach ($defaults as $d) {
            $cat = Category::firstOrCreate(['slug' => $d['slug']], [
                'name' => $d['name'],
                'description' => $d['description'],
            ]);

            if ($cat->wasRecentlyCreated) {
                $this->line("Created category: {$cat->name} (slug: {$cat->slug})");
            } else {
                $this->line("Exists: {$cat->name} (slug: {$cat->slug})");
            }
        }

        $this->info('Done.');

        return 0;
    }
}
