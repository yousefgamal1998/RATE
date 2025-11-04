<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$pdo = \Illuminate\Support\Facades\DB::select("select id, title, tmdb_id, category_id from movies where category_id = ?", [4]);
if (empty($pdo)) {
    echo "raw query returned 0 rows\n";
} else {
    foreach ($pdo as $r) {
        echo json_encode((array)$r) . PHP_EOL;
    }
}
