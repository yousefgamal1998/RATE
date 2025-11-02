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
        // Ensure the three default categories exist. Using firstOrCreate makes this safe to run repeatedly.
        Category::firstOrCreate(['slug' => 'latest'], [
            'name' => 'Latest Movies',
            'description' => 'Recently added movies',
        ]);

        Category::firstOrCreate(['slug' => 'mcu'], [
            'name' => 'Marvel Cinematic Universe',
            'description' => 'Movies and series in the Marvel Cinematic Universe',
        ]);

        // 'disney-plus' used as a concise slug for CI/CD and scripting environments
        Category::firstOrCreate(['slug' => 'disney-plus'], [
            'name' => 'Disney Plus',
            'description' => 'Movies and originals featured on Disney Plus',
        ]);

        // Add a Horror category so dashboard and add-movie can reference it
        Category::firstOrCreate(['slug' => 'horror'], [
            'name' => 'Horror',
            'description' => 'Scary, suspenseful and horror films',
        ]);


    }
}
