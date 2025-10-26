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
        Category::firstOrCreate(['slug' => 'mcu'], [
            'name' => 'Marvel Cinematic Universe',
            'description' => 'MCU movies',
        ]);

        Category::firstOrCreate(['slug' => 'latest'], [
            'name' => 'Latest Movies',
            'description' => 'Recently added movies',
        ]);
    }
}
