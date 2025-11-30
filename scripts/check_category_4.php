<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\Category;
$cat = Category::find(4);
if ($cat) {
    echo "found: id={$cat->id}, slug={$cat->slug}, name={$cat->name}\n";
} else {
    echo "not found\n";
}
