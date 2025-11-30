<?php
$dir = __DIR__ . '/../';
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$errors = [];
foreach ($rii as $file) {
    if (!$file->isFile()) continue;
    $path = $file->getPathname();
    if (substr($path, -4) !== '.php') continue;
    // skip vendor
    if (strpos($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) !== false) continue;
    // run php -l
    $cmd = "php -l " . escapeshellarg($path) . " 2>&1";
    $out = [];
    exec($cmd, $out, $rc);
    if ($rc !== 0) {
        $errors[$path] = implode("\n", $out);
    }
}
if (count($errors) === 0) {
    echo "No syntax errors detected\n";
    exit(0);
}
foreach ($errors as $p => $e) {
    echo "ERROR: $p\n$e\n\n";
}
exit(1);
