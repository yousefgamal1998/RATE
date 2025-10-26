<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Movie;

class MoviesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a few sample movies for development
        $sample = [
            [
                'title' => 'The Red Horizon',
                'slug' => 'the-red-horizon',
                'description' => 'An epic journey across a dystopian landscape.',
                'year' => 2024,
                'duration' => '1h 58m',
                'genres' => ['Action', 'Sci-Fi'],
                'rating' => 85,
                'rating_decimal' => 8.5,
                'image_path' => 'images/movies/red-horizon.jpg',
                'video_url' => null,
                'is_featured' => true,
            ],
            [
                'title' => 'Test Movie - Copilot',
                'slug' => 'test-movie-copilot',
                'description' => 'A test record created to verify the movie show page.',
                'year' => 2025,
                'duration' => '1h 30m',
                'genres' => ['Test','Demo'],
                'rating' => 80,
                'rating_decimal' => 8.0,
                'image_path' => 'images/movies/test.jpg',
                'video_url' => null,
                'is_featured' => false,
                'visibility' => 'homepage',
                'dashboard_id' => 999,
            ],
            [
                'title' => 'Midnight Echoes',
                'slug' => 'midnight-echoes',
                'description' => 'A haunting drama of love and memory.',
                'year' => 2023,
                'duration' => '2h 5m',
                'genres' => ['Drama', 'Mystery'],
                'rating' => 78,
                'rating_decimal' => 7.8,
                'image_path' => 'images/movies/midnight-echoes.jpg',
                'video_url' => null,
                'is_featured' => false,
            ],
        ];

        foreach ($sample as $m) {
            Movie::updateOrCreate(
                ['slug' => $m['slug']],
                $m
            );
        }
    }
}
