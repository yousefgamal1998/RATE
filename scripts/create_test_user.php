<?php
$base = dirname(__DIR__);
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$email = 'dev@example.test';
$user = User::where('email', $email)->first();
if ($user) {
    echo "EXISTS:" . $user->id . "\n";
    exit(0);
}

$user = User::create([
    'name' => 'Dev User',
    'email' => $email,
    'password' => 'password',
]);

echo "CREATED:" . $user->id . "\n";
