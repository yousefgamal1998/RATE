<?php
// Usage examples:
// php hash_user_password.php user@example.com        # interactively confirm and hash current DB value
// php hash_user_password.php user@example.com --yes  # hash current DB value without prompt
// php hash_user_password.php user@example.com --new "NewPass123"  # set new password (will be hashed)

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$argv = $_SERVER['argv'];
array_shift($argv); // script name

if (count($argv) < 1) {
    echo "Usage:\n";
    echo "  php hash_user_password.php user@example.com [--yes]\n";
    echo "  php hash_user_password.php user@example.com --new \"NewPass123\"\n";
    exit(1);
}

$email = $argv[0];
$flags = array_slice($argv, 1);
$autoYes = in_array('--yes', $flags, true);
$newPassIndex = array_search('--new', $flags, true);
$newPassword = null;
if ($newPassIndex !== false) {
    if (isset($flags[$newPassIndex + 1])) {
        $newPassword = $flags[$newPassIndex + 1];
    } else {
        echo "Error: --new specified but no password provided.\n";
        exit(1);
    }
}

$user = User::where('email', $email)->first();
if (! $user) {
    echo "User not found for email: {$email}\n";
    exit(1);
}

$current = $user->getAttribute('password');
$isHash = is_string($current) && preg_match('/^\$2[aby]\$|^\$argon2/i', $current);

echo "Found user: id={$user->id}, email={$user->email}\n";
echo "Current password field length: " . (is_string($current) ? strlen($current) : 0) . "\n";
echo "Looks like hash?: " . ($isHash ? 'yes' : 'no') . "\n";

if ($isHash && $newPassword === null) {
    echo "No action taken: password already looks hashed.\n";
    exit(0);
}

if ($newPassword !== null) {
    $toHash = $newPassword;
    echo "You requested to set a new password (will be hashed).\n";
} else {
    // We'll assume the current DB value is plaintext and will be hashed (this is what you requested in your snippet)
    $toHash = $current;
    echo "Will hash the existing DB value (treating it as plaintext) and replace it with a proper hash.\n";
}

if (! $autoYes) {
    echo "Proceed? Type 'yes' to continue: ";
    $handle = fopen('php://stdin', 'r');
    $line = fgets($handle);
    $line = trim($line);
    if ($line !== 'yes') {
        echo "Aborted by user. No changes made.\n";
        exit(0);
    }
}

if ($toHash === null || $toHash === '') {
    echo "Refusing to hash an empty value. Provide a new password with --new \"YourPass\" or fill the user's password first.\n";
    exit(1);
}

$hashed = Hash::make($toHash);
// Use setAttribute to avoid static analysis/property access errors and to trigger mutator behavior correctly
$user->setAttribute('password', $hashed);
$user->save();

echo "Password updated and hashed successfully for user id={$user->id}.\n";
exit(0);
