<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['slug' => 'latest-movies', 'name' => 'Latest Movies', 'description' => 'Recently added movies'],
            ['slug' => 'mcu', 'name' => 'Marvel Cinematic Universe', 'description' => 'Movies and series in the Marvel Cinematic Universe'],
            ['slug' => 'dc-comics', 'name' => 'DC Comics', 'description' => 'Movies and series from the DC Comics universe'],
            ['slug' => 'disney-plus', 'name' => 'Disney+ Originals', 'description' => 'Disney+ originals and premieres'],
            ['slug' => 'horror', 'name' => 'Horror', 'description' => 'Scary, suspenseful and horror films'],
        ];

        foreach ($items as $it) {
            $cat = Category::firstOrCreate(
                ['slug' => $it['slug']],
                ['name' => $it['name'], 'description' => $it['description'] ?? null]
            );

            // Print helpful info when running seeder interactively
            $this->command?->info(sprintf('Category: %-28s slug=%-15s id=%d', $cat->name, $cat->slug, $cat->id));
        }
    }
}
