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
echo "\nRaw dump:\n";
print_r($cats->toArray());
