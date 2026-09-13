<?php

declare(strict_types=1);

require_once __DIR__ . '/TestHarness.php';

$suite = $argv[1] ?? '';
$allowedSuites = ['unit', 'integration'];

if (! in_array($suite, $allowedSuites, true)) {
    fwrite(STDERR, 'Usage: php tests/run.php <unit|integration>' . PHP_EOL);
    exit(2);
}

if ($suite === 'integration') {
    require_once __DIR__ . '/integration/wordpress-stubs.php';
}

$files = glob(__DIR__ . '/' . $suite . '/*Test.php') ?: [];
sort($files);

if ($files === []) {
    fwrite(STDERR, "No $suite tests found.\n");
    exit(1);
}

$tests = [];
foreach ($files as $file) {
    $loaded = require $file;
    if (! is_array($loaded)) {
        fwrite(STDERR, "$file must return an array of named test callables.\n");
        exit(1);
    }

    foreach ($loaded as $name => $test) {
        if (! is_string($name) || ! is_callable($test)) {
            fwrite(STDERR, "$file contains an invalid test definition.\n");
            exit(1);
        }
        $tests[basename($file) . '::' . $name] = $test;
    }
}

exit((new TestHarness())->run($tests));
