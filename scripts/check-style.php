<?php

declare(strict_types=1);

$roots = ['wordpress', 'scripts', 'tests'];
$extensions = ['php', 'js', 'css', 'json', 'yml', 'yaml', 'sh'];
$errors = [];

foreach ($roots as $root) {
    if (! is_dir($root)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }

        $extension = strtolower($file->getExtension());
        if (! in_array($extension, $extensions, true)) {
            continue;
        }

        $path = $file->getPathname();
        $contents = file_get_contents($path);
        if ($contents === false) {
            $errors[] = "$path: unreadable";
            continue;
        }

        if ($contents !== '' && ! str_ends_with($contents, "\n")) {
            $errors[] = "$path: missing final newline";
        }

        foreach (preg_split('/\R/', $contents) ?: [] as $index => $line) {
            if (preg_match('/[ \t]+$/', $line) === 1) {
                $errors[] = sprintf('%s:%d: trailing whitespace', $path, $index + 1);
            }
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

echo "Style hygiene checks passed.\n";
