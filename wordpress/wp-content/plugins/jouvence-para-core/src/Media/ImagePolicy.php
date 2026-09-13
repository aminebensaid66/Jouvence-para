<?php

declare(strict_types=1);

namespace JouvencePara\Core\Media;

final class ImagePolicy
{
    public const CARD_SIZE = 'jp-product-card';
    public const GALLERY_SIZE = 'jp-product-gallery';

    public static function preferredOutputMime(callable $supports): ?string
    {
        if ($supports('image/avif')) {
            return 'image/avif';
        }

        if ($supports('image/webp')) {
            return 'image/webp';
        }

        return null;
    }

    /** @param array<string, string> $formats */
    public static function outputFormats(array $formats, callable $supports): array
    {
        $preferred = self::preferredOutputMime($supports);
        if ($preferred === null) {
            return $formats;
        }

        $formats['image/jpeg'] = $preferred;
        $formats['image/png'] = $preferred;

        return $formats;
    }

    public static function quality(string $mimeType, int $quality): int
    {
        if (in_array($mimeType, ['image/avif', 'image/webp', 'image/jpeg'], true)) {
            return 82;
        }

        return $quality;
    }
}
