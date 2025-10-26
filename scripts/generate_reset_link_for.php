<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Password;
use App\Models\User;

// Parse arguments and options
$options = [
    'file' => null,
    'output' => null,
    'copy' => false,
    'emails' => [],
];

for ($i = 1; $i < $argc; $i++) {
    $arg = $argv[$i];
    if (str_starts_with($arg, '--file=')) {
        $options['file'] = substr($arg, strlen('--file='));
        continue;
    }
    if (str_starts_with($arg, '--output=')) {
        $options['output'] = substr($arg, strlen('--output='));
        continue;
    }
    if ($arg === '--copy') {
        $options['copy'] = true;
        continue;
    }
    if ($arg === '--help' || $arg === '-h') {
        echo "Usage: php generate_reset_link_for.php [options] [email1 email2 ...]\n";
        echo "Options:\n";
        echo "  --file=path       Read emails (one per line) from a file\n";
        echo "  --output=path     Write generated links to the given file (append)\n";
        echo "  --copy            Try to copy the last generated link to clipboard (dev only)\n";
        echo "  --help, -h        Show this help\n";
        exit(0);
    }
    $options['emails'][] = $arg;
}

// If file option provided, load emails from file
if ($options['file']) {
    if (!is_readable($options['file'])) {
        fwrite(STDERR, "ERROR: file not readable: {$options['file']}\n");
        exit(2);
    }
    $lines = file($options['file'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line !== '') $options['emails'][] = $line;
    }
}

if (count($options['emails']) === 0) {
    echo "Usage: php generate_reset_link_for.php [--file=path] [--output=path] [--copy] email1 email2 ...\n";
    exit(1);
}

// إعداد عنوان التطبيق (من .env أو localhost كـ fallback)
$appUrl = config('app.url') ?: 'http://localhost';

$results = [];
foreach ($options['emails'] as $email) {
    $email = trim($email);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "❌ Skipping invalid email: {$email}\n";
        continue;
    }

    $user = User::where('email', $email)->first();

    if (! $user) {
        echo "❌ User not found for: {$email}\n";
        continue;
    }

    // إنشاء التوكن ورابط إعادة الضبط
    /** @var \Illuminate\Auth\Passwords\PasswordBroker $broker */
    $broker = Password::broker();
    $token = $broker->createToken($user);
    $url = rtrim($appUrl, '/') . '/password/reset/' . $token . '?email=' . urlencode($user->email);

    echo "✅ Reset link for {$email}:\n{$url}\n\n";
    $results[] = [
        'email' => $email,
        'url' => $url,
    ];
}

// Optionally append results to output file
if ($options['output'] && count($results) > 0) {
    $lines = [];
    foreach ($results as $r) {
        $lines[] = $r['email'] . ' => ' . $r['url'];
    }
    file_put_contents($options['output'], implode(PHP_EOL, $lines) . PHP_EOL, FILE_APPEND | LOCK_EX);
    echo "✅ Wrote " . count($results) . " links to {$options['output']}\n";
}

// Optionally copy last link to clipboard (best-effort)
if ($options['copy'] && count($results) > 0) {
    $last = end($results)['url'];
    $copied = false;
    // Windows clip
    if (str_starts_with(PHP_OS_FAMILY, 'Windows')) {
        $p = popen('clip', 'w');
        if ($p !== false) {
            fwrite($p, $last);
            pclose($p);
            $copied = true;
        }
    }
    // macOS pbcopy
    if (!$copied && trim(PHP_OS) === 'Darwin') {
        $p = popen('pbcopy', 'w');
        if ($p !== false) {
            fwrite($p, $last);
            pclose($p);
            $copied = true;
        }
    }
    // linux xclip
    if (!$copied) {
        $p = @popen('xclip -selection clipboard', 'w');
        if ($p !== false) {
            fwrite($p, $last);
            pclose($p);
            $copied = true;
        }
    }

    echo $copied ? "✅ Link copied to clipboard (best-effort)\n" : "⚠️ Could not copy link to clipboard on this system.\n";
}

echo "🎉 Done generating reset links.\n";
