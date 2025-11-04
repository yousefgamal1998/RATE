<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\Category;
$cat = Category::find($argv[1] ?? 4);
if (!$cat) {
    echo "Category not found for id: " . ($argv[1] ?? 4) . PHP_EOL;
    exit(1);
}
$movies = $cat->movies()->get();
echo "Category: {$cat->id} - {$cat->name} ({$cat->slug})\n";
echo "Movies count: " . $movies->count() . "\n";
foreach ($movies as $m) {
    echo "- id={$m->id}, title={$m->title}, tmdb_id={$m->tmdb_id}, category_id={$m->category_id}\n";
}
