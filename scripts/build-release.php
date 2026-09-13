<?php

declare(strict_types=1);

if (!class_exists(ZipArchive::class)) {
    fwrite(STDERR, "The PHP zip extension is required to build release artifacts.\n");
    exit(1);
}

$root = realpath(dirname(__DIR__));
if ($root === false) {
    throw new RuntimeException('Unable to resolve repository root.');
}

$destination = $argv[1] ?? $root . '/dist';
if (!str_starts_with($destination, DIRECTORY_SEPARATOR)) {
    $destination = $root . DIRECTORY_SEPARATOR . $destination;
}

if (!is_dir($destination) && !mkdir($destination, 0775, true) && !is_dir($destination)) {
    throw new RuntimeException(sprintf('Unable to create release directory: %s', $destination));
}

$destination = realpath($destination);
if ($destination === false || $destination === $root) {
    throw new RuntimeException('The release directory must not be the repository root.');
}

$components = [
    'jouvence-para-core' => 'wordpress/wp-content/plugins/jouvence-para-core',
    'jouvence-para-theme' => 'wordpress/wp-content/themes/jouvence-para',
];
$artifacts = [];

foreach ($components as $artifactName => $relativePath) {
    $source = realpath($root . DIRECTORY_SEPARATOR . $relativePath);
    if ($source === false || !is_dir($source)) {
        throw new RuntimeException(sprintf('Missing release component: %s', $relativePath));
    }

    if ($destination === $source || str_starts_with($destination, $source . DIRECTORY_SEPARATOR)) {
        throw new RuntimeException('The release directory must not be inside a packaged component.');
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $files[] = $file->getPathname();
        }
    }
    sort($files, SORT_STRING);

    $artifact = $destination . DIRECTORY_SEPARATOR . $artifactName . '.zip';
    $temporary = tempnam($destination, '.release-');
    if ($temporary === false) {
        throw new RuntimeException('Unable to create a temporary release artifact.');
    }

    $zip = new ZipArchive();
    if ($zip->open($temporary, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        @unlink($temporary);
        throw new RuntimeException(sprintf('Unable to create artifact: %s', $artifact));
    }

    $archiveRoot = basename($source);
    foreach ($files as $file) {
        $entry = $archiveRoot . '/' . substr($file, strlen($source) + 1);
        $contents = file_get_contents($file);
        if ($contents === false || !$zip->addFromString($entry, $contents)) {
            $zip->close();
            @unlink($temporary);
            throw new RuntimeException(sprintf('Unable to add file to artifact: %s', $file));
        }
        $zip->setMtimeName($entry, 315532800);
    }

    if (!$zip->close() || !rename($temporary, $artifact)) {
        @unlink($temporary);
        throw new RuntimeException(sprintf('Unable to finalize artifact: %s', $artifact));
    }
    $artifacts[basename($artifact)] = hash_file('sha256', $artifact);
}

ksort($artifacts, SORT_STRING);
$checksums = '';
foreach ($artifacts as $filename => $checksum) {
    $checksums .= sprintf("%s  %s\n", $checksum, $filename);
}

if (file_put_contents($destination . '/SHA256SUMS', $checksums, LOCK_EX) === false) {
    throw new RuntimeException('Unable to write artifact checksums.');
}
