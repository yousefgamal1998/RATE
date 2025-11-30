<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$pdo = \Illuminate\Support\Facades\DB::getPdo();
$driver = \Illuminate\Support\Facades\DB::getDriverName();
$config = config('database.connections.' . config('database.default'));
echo "DB driver: $driver\n";
echo "DB config: " . json_encode($config) . "\n";
try {
    $dbName = \Illuminate\Support\Facades\DB::getDatabaseName();
    echo "Connected DB name: " . $dbName . "\n";
} catch (Exception $e) {
    echo "Could not get DB name: " . $e->getMessage() . "\n";
}
