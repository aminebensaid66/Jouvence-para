<?php

declare(strict_types=1);

$descriptorSpec = [
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];
$process = proc_open(['git', 'ls-files', '-z'], $descriptorSpec, $pipes);
if (! is_resource($process)) {
    fwrite(STDERR, "Unable to start git while checking tracked files.\n");
    exit(1);
}

$tracked = stream_get_contents($pipes[1]);
$gitError = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
$exitCode = proc_close($process);

if ($exitCode !== 0 || $tracked === false) {
    fwrite(STDERR, 'Unable to list tracked files: ' . trim((string) $gitError) . PHP_EOL);
    exit(1);
}

$forbiddenNames = [
    '.env',
    'id_rsa',
    'id_ed25519',
];
$forbiddenExtensions = ['pem', 'key', 'p12', 'pfx'];
$secretPatterns = [
    '/gh[opsu]_[A-Za-z0-9]{20,}/' => 'GitHub token pattern',
    '/sk-[A-Za-z0-9_-]{20,}/' => 'API secret-key pattern',
    '/AKIA[0-9A-Z]{16}/' => 'AWS access-key pattern',
    '/-----BEGIN (?:RSA |OPENSSH |EC )?PRIVATE KEY-----/' => 'private-key marker',
];
$errors = [];

foreach (array_filter(explode("\0", $tracked)) as $path) {
    $basename = basename($path);
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    $forbiddenEnvironmentFile = str_starts_with($basename, '.env.') && $basename !== '.env.example';
    if (
        in_array($basename, $forbiddenNames, true)
        || $forbiddenEnvironmentFile
        || in_array($extension, $forbiddenExtensions, true)
    ) {
        $errors[] = "$path: secret-bearing filename must not be tracked";
        continue;
    }

    if (! is_file($path) || filesize($path) > 1_000_000) {
        continue;
    }

    $contents = file_get_contents($path);
    if ($contents === false) {
        continue;
    }

    foreach ($secretPatterns as $pattern => $description) {
        if (preg_match($pattern, $contents) === 1) {
            $errors[] = "$path: contains a $description";
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

echo "Tracked-file secret hygiene checks passed.\n";
