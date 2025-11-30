<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$rows = \Illuminate\Support\Facades\DB::select('select * from movies');
echo "rows: " . count($rows) . "\n";
foreach ($rows as $r) {
    echo json_encode((array)$r) . PHP_EOL;
}
