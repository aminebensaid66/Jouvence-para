<?php

declare(strict_types=1);

$files = ['composer.json', 'package.json'];
$checked = 0;

foreach ($files as $file) {
    if (! is_file($file)) {
        continue;
    }

    $checked++;
    $contents = file_get_contents($file);
    if ($contents === false) {
        fwrite(STDERR, "$file: unreadable\n");
        exit(1);
    }

    json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    echo "JSON OK: $file\n";
}

if ($checked === 0) {
    fwrite(STDERR, "No project JSON files found.\n");
    exit(1);
}
