<?php

declare(strict_types=1);

namespace JouvencePara\Core\Media;

use JouvencePara\Core\Contracts\Module;

final class MediaModule implements Module
{
    public function register(): void
    {
        add_action('after_setup_theme', [$this, 'registerImageSizes']);
        add_filter('image_editor_output_format', [$this, 'filterOutputFormat']);
        add_filter('wp_editor_set_quality', [$this, 'filterQuality'], 10, 2);
    }

    public function registerImageSizes(): void
    {
        add_image_size(ImagePolicy::CARD_SIZE, 480, 480, false);
        add_image_size(ImagePolicy::GALLERY_SIZE, 1200, 1200, false);
    }

    /** @param array<string, string> $formats */
    public function filterOutputFormat(array $formats): array
    {
        return ImagePolicy::outputFormats(
            $formats,
            static function (string $mimeType): bool {
                if (! function_exists('wp_image_editor_supports')) {
                    return false;
                }

                return wp_image_editor_supports(['mime_type' => $mimeType]);
            }
        );
    }

    public function filterQuality(int $quality, string $mimeType): int
    {
        return ImagePolicy::quality($mimeType, $quality);
    }
}
