<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\Category;
$id = $argv[1] ?? 4;
$c = Category::find($id);
if (!$c) {
    echo "Category not found: $id\n";
    exit(1);
}
// debug dump to ensure properties are visible in CLI
echo "Category (raw):\n";
var_export([ 'id' => $c->id, 'name' => $c->name, 'slug' => $c->slug ]);
echo PHP_EOL;
echo "Category: id={$c->id}, name={$c->name}, slug={$c->slug}\n";
$movies = $c->movies()->get();
echo "Movies (count=".$movies->count()."):\n";
foreach ($movies as $m) {
    echo "- id={$m->id}, title={$m->title}, tmdb_id={$m->tmdb_id}, category_id={$m->category_id}\n";
}
