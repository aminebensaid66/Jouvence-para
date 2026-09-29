<?php

declare(strict_types=1);

namespace JouvencePara\Core\Discovery;

final class SeoPolicy
{
    public static function description(string $html, int $limit = 160): string
    {
        $html = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1\s*>/is', '', $html) ?? $html;
        $html = preg_replace('/<!--.*?-->/s', '', $html) ?? $html;
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', trim($text));
        if (! is_string($text) || $text === '' || $limit < 1) {
            return '';
        }

        $characters = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        if (! is_array($characters)) {
            return substr($text, 0, $limit);
        }
        if (count($characters) <= $limit) {
            return $text;
        }

        $excerpt = rtrim(implode('', array_slice($characters, 0, $limit - 1)));
        $lastSpace = strrpos($excerpt, ' ');
        if ($lastSpace !== false && $lastSpace > (int) ($limit * 0.6)) {
            $excerpt = substr($excerpt, 0, $lastSpace);
        }
        return rtrim($excerpt, " \t\n\r\0\x0B.,;:!?-") . '…';
    }

    /** @param array<string, mixed> $args @return array<string, mixed> */
    public static function productRewrite(array $args, string $postType): array
    {
        if ($postType !== 'product') {
            return $args;
        }
        $rewrite = is_array($args['rewrite'] ?? null) ? $args['rewrite'] : [];
        $args['rewrite'] = array_merge($rewrite, ['slug' => 'produit', 'with_front' => false]);
        return $args;
    }

    /** @param array<string, mixed> $args @return array<string, mixed> */
    public static function categoryRewrite(array $args, string $taxonomy): array
    {
        if ($taxonomy !== 'product_cat') {
            return $args;
        }
        $rewrite = is_array($args['rewrite'] ?? null) ? $args['rewrite'] : [];
        $args['rewrite'] = array_merge($rewrite, ['slug' => 'categorie', 'with_front' => false]);
        return $args;
    }
}
