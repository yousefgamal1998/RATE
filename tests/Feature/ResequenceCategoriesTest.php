<?php

use Illuminate\Support\Facades\DB;

uses(Tests\TestCase::class);

test('after resequence all movies reference existing category', function () {
    // Create sample categories with explicit ids using the query builder to avoid model mass-assignment guards
    $now = date('Y-m-d H:i:s');
    DB::table('categories')->insert([
        ['id' => 10, 'name' => 'Cat A', 'slug' => 'cat-a', 'created_at' => $now, 'updated_at' => $now],
        ['id' => 20, 'name' => 'Cat B', 'slug' => 'cat-b', 'created_at' => $now, 'updated_at' => $now],
    ]);

    // Create movies that point to those category ids
    DB::table('movies')->insert([
        ['title' => 'Movie One', 'description' => 'desc', 'rating' => 5, 'category_id' => 10, 'created_at' => $now, 'updated_at' => $now],
        ['title' => 'Movie Two', 'description' => 'desc', 'rating' => 7, 'category_id' => 20, 'created_at' => $now, 'updated_at' => $now],
    ]);

    // Run the resequence command
    $this->artisan('resequence:categories')->assertExitCode(0);

    // After running, every movie.category_id must join to an existing categories.id
    $invalid = DB::table('movies')
        ->leftJoin('categories', 'movies.category_id', '=', 'categories.id')
        ->whereNull('categories.id')
        ->count();

    expect($invalid)->toBe(0);
});
