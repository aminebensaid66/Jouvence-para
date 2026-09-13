<?php

declare(strict_types=1);

use JouvencePara\Core\Media\ImagePolicy;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Media/ImagePolicy.php';

return [
    'prefers avif over webp when both are available' => static function (TestHarness $test): void {
        $mime = ImagePolicy::preferredOutputMime(
            static fn (string $candidate): bool => in_array($candidate, ['image/avif', 'image/webp'], true)
        );
        $test->assertSame('image/avif', $mime);
    },
    'falls back to webp' => static function (TestHarness $test): void {
        $mime = ImagePolicy::preferredOutputMime(static fn (string $candidate): bool => $candidate === 'image/webp');
        $test->assertSame('image/webp', $mime);
    },
    'keeps source formats when modern formats are unavailable' => static function (TestHarness $test): void {
        $formats = ['image/jpeg' => 'image/jpeg'];
        $result = ImagePolicy::outputFormats($formats, static fn (string $candidate): bool => false);
        $test->assertSame($formats, $result);
    },
    'uses product image quality for supported output formats' => static function (TestHarness $test): void {
        $test->assertSame(82, ImagePolicy::quality('image/webp', 90));
        $test->assertSame(90, ImagePolicy::quality('image/gif', 90));
    },
];
