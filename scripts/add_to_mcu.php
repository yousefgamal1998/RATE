<?php
// One-off helper: php scripts/add_to_mcu.php <tmdb_id>
if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run this script from CLI only.\n");
    exit(1);
}

if ($argc < 2) {
    echo "Usage: php scripts/add_to_mcu.php <tmdb_id>\n";
    exit(1);
}

$tmdbId = (int) $argv[1];

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Bootstrap the framework so Eloquent and facades work
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Movie;

$m = Movie::where('tmdb_id', $tmdbId)->first();
if (! $m) {
    echo "Movie not found for tmdb_id: {$tmdbId}\n";
    exit(0);
}

$m->update([
    'dashboard_id' => 2,
    'visibility' => 'dashboard',
]);

echo "Updated movie: {$m->id} — {$m->title}\n";

return 0;
