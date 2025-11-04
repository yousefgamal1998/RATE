<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$email = $argv[1] ?? 'yousefgamala1998@gmail.com';
$u = User::where('email', $email)->first();
if ($u) {
    echo "FOUND\n";
    echo "id: {$u->id}\n";
    echo "name: {$u->name}\n";
    echo "email: {$u->email}\n";
    echo "created_at: {$u->created_at}\n";
} else {
    echo "NOT FOUND\n";
}
