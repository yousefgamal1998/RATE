<?php
// Usage:
// php create_user.php email@example.com "password"
// or set environment variables CREATE_USER_EMAIL and CREATE_USER_PASSWORD and run php create_user.php

$argv0 = $argv[0] ?? 'create_user.php';

// Get credentials from args or env
if (isset($argv[1]) && isset($argv[2])) {
    $email = $argv[1];
    $password = $argv[2];
} else {
    $email = getenv('CREATE_USER_EMAIL') ?: null;
    $password = getenv('CREATE_USER_PASSWORD') ?: null;
}

if (!$email || !$password) {
    echo "Usage:\n";
    echo "  php {$argv0} user@example.com \"password\"\n";
    echo "or set CREATE_USER_EMAIL and CREATE_USER_PASSWORD env vars.\n";
    exit(1);
}

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

// Basic validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Error: invalid email format.\n";
    exit(1);
}

// Make sure we don't accidentally create duplicate
$existing = User::where('email', $email)->first();
if ($existing) {
    echo "User already exists: {$existing->id} ({$existing->email})\n";
    exit(0);
}

// Create user. Pass the plain password to the model so the User mutator hashes it.
$user = User::create([
    'name' => explode('@', $email)[0],
    'email' => $email,
    'password' => $password,
]);

if ($user) {
    echo "CREATED: {$user->id} ({$user->email})\n";
    exit(0);
}

echo "Failed to create user.\n";
exit(1);
