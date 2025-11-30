<?php
require __DIR__ . '/../vendor/autoload.php';

// Boot the framework so Eloquent works
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Movie;
use App\Models\Category;

$mcu = Movie::where(function($q){
    $q->where('visibility','dashboard')->orWhere('visibility','both');
})->where('dashboard_id', 2)->orderBy('created_at','desc')->get();

$disney = collect();
$disneyCat = Category::whereIn('slug', ['disney-plus-originals','disney-plus'])->first();
if ($disneyCat) {
    $disney = Movie::where(function($q){
        $q->where('visibility','dashboard')->orWhere('visibility','both');
    })->where('dashboard_id', $disneyCat->id)->orderBy('created_at','desc')->get();
}

$dc = collect();
$dcCat = Category::where('slug','dc-comics')->first();
if ($dcCat) {
    $dc = Movie::where(function($q){
        $q->where('visibility','dashboard')->orWhere('visibility','both');
    })->where('dashboard_id', $dcCat->id)->orderBy('created_at','desc')->get();
}

$horror = collect();
$hCat = Category::where('slug','horror')->first();
if ($hCat) {
    $horror = $hCat->movies()->orderBy('created_at','desc')->get();
}

$out = [
    'mcu' => $mcu->map->only(['id','title','category_id','dashboard_id','visibility']),
    'disney' => $disney->map->only(['id','title','category_id','dashboard_id','visibility']),
    'dc' => $dc->map->only(['id','title','category_id','dashboard_id','visibility']),
    'horror' => $horror->map->only(['id','title','category_id','dashboard_id','visibility']),
];

echo json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
