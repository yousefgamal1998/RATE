<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\Category;
$slugs = $argv;
array_shift($slugs); // remove script name
if (empty($slugs)) {
    $slugs = ['disney-plus-originals','marvel-cinematic-universe','dc-comics','horror','latest-movies'];
}
foreach ($slugs as $s) {
    $id = Category::where('slug', $s)->value('id');
    echo "$s => " . ($id ?? 'NULL') . PHP_EOL;
}
