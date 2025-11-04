<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\Movie;
$movie = Movie::where('tmdb_id', 271110)->first();
if ($movie) {
    echo "movie: id={$movie->id}, title={$movie->title}, tmdb_id={$movie->tmdb_id}, category_id=" . ($movie->category_id ?? 'NULL') . ", dashboard_id=" . ($movie->dashboard_id ?? 'NULL') . ", visibility=" . ($movie->visibility ?? 'NULL') . "\n";
} else {
    echo "movie not found\n";
}
