<?php
$base = dirname(__DIR__);
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Movie;

$m = Movie::where('slug', 'test-movie-copilot')->first();
if ($m) {
    echo "FOUND:" . $m->id . "|" . $m->title . PHP_EOL;
    exit(0);
}

// Fallback: list first 5
$all = Movie::orderBy('id')->take(5)->get(['id','slug','title']);
foreach ($all as $row) {
    echo "ID:" . $row->id . " SLUG:" . ($row->slug ?? 'null') . " TITLE:" . ($row->title ?? 'null') . PHP_EOL;
}

if ($all->isEmpty()) echo "NO_MOVIES_FOUND" . PHP_EOL;
