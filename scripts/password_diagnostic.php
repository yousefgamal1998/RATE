<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

// Make password visible for this diagnostic only
$users = User::limit(20)->get(['id','email','password','created_at'])->makeVisible('password')->toArray();
foreach ($users as $u) {
    $pw = $u['password'];
    $is_hash = is_string($pw) && preg_match('/^\$2[aby]\$|^\$argon2/i', $pw);
    $len = is_string($pw) ? strlen($pw) : 0;
    echo "{$u['id']}\t{$u['email']}\tlen={$len}\thash={$is_hash}\tcreated={$u['created_at']}\n";
}

// Summary counts
$total = count($users);
$hashCount = 0;
foreach ($users as $u) {
    $pw = $u['password'];
    if (is_string($pw) && preg_match('/^\$2[aby]\$|^\$argon2/i', $pw)) $hashCount++;
}
echo "\nTotal checked: {$total}, hashed: {$hashCount}, plain-like: " . ($total - $hashCount) . "\n";
