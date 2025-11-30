<?php
$base = dirname(__DIR__);
require $base . '/vendor/autoload.php';
$app = require $base . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$email = $argv[1] ?? 'yousefgamala2023@gmail.com';

$user = User::where('email', $email)->first();
if (!$user) {
    echo "NOT_FOUND\n";
    exit(0);
}

echo "FOUND\n";
echo "ID: " . $user->id . "\n";
echo "Name: " . ($user->name ?? 'N/A') . "\n";
echo "Email: " . $user->email . "\n";
$pw = $user->password ?? '';
if ($pw === '') {
    echo "Password: (empty)\n";
} else {
    echo "Password: (present) length=" . strlen($pw) . "\n";
    if (strpos($pw, '$2y$') === 0 || strpos($pw, '$2a$') === 0) {
        echo "Hash type: bcrypt\n";
    } else {
        echo "Hash type: unknown prefix: " . substr($pw,0,4) . "\n";
    }
}

// Don't print the hash for security, but show a short prefix
if ($pw) {
    echo "Hash prefix: " . substr($pw,0,10) . "...\n";
}

exit(0);
