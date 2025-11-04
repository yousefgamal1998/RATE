<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cats = DB::table('categories')->orderBy('id')->get();
echo "Categories:\n";
foreach ($cats as $c) {
    echo "{$c->id}\t{$c->name}\t{$c->slug}\n";
}

echo "\nMovies (id,title,category_id):\n";
$movies = DB::table('movies')->select('id','title','category_id')->orderBy('id')->get();
foreach ($movies as $m) {
    echo "{$m->id}\t{$m->title}\t{$m->category_id}\n";
}

echo "\nMovies with category_id = 4:\n";
$rows = DB::table('movies')->where('category_id', 4)->get();
foreach ($rows as $r) {
    echo "{$r->id}\t{$r->title}\t{$r->category_id}\n";
}
