<?php

declare(strict_types=1);

namespace {
    class WP_Post
    {
        public int $ID = 41;
        public string $post_type = 'post';
        public string $post_status = 'publish';
        public string $post_name = 'conseil';
        public string $post_title = 'Un conseil';
        public string $post_content = '';
        public string $post_excerpt = '';
        public string $post_password = '';

        public function __construct(array $properties = [])
        {
            foreach ($properties as $key => $value) {
                $this->$key = $value;
            }
        }
    }

    function jp_test_seo_page(array $context = []): void
    {
        $GLOBALS['jp_seo_context'] = array_replace([
            'post' => null, 'title' => 'Jouvence Para', 'paged' => 1,
            'canonical' => 'https://store.example/conseils/conseil/',
            'term_url' => 'https://store.example/categorie/visage/',
            'external_constants' => [],
        ], $context);
        $GLOBALS['jp_test_options']['permalink_structure'] = '/%postname%/';
        $GLOBALS['wp_rewrite'] = (object) ['pagination_base' => 'page'];
    }

    function jp_test_seo_output(callable $render): string
    {
        ob_start();
        try {
            $render();
            return (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
    }

    function is_singular(string $type = ''): bool
    {
        $post = $GLOBALS['jp_seo_context']['post'] ?? null;
        return $post instanceof WP_Post && ($type === '' || $type === $post->post_type);
    }
    function is_admin(): bool { return (bool) ($GLOBALS['jp_seo_context']['admin'] ?? false); }
    function is_404(): bool { return (bool) ($GLOBALS['jp_seo_context']['404'] ?? false); }
    function is_search(): bool { return (bool) ($GLOBALS['jp_seo_context']['search'] ?? false); }
    function is_feed(): bool { return (bool) ($GLOBALS['jp_seo_context']['feed'] ?? false); }
    function is_preview(): bool { return (bool) ($GLOBALS['jp_seo_context']['preview'] ?? false); }
    function is_cart(): bool { return (bool) ($GLOBALS['jp_seo_context']['cart'] ?? false); }
    function is_checkout(): bool { return (bool) ($GLOBALS['jp_seo_context']['checkout'] ?? false); }
    function is_account_page(): bool { return (bool) ($GLOBALS['jp_seo_context']['account'] ?? false); }
    function is_front_page(): bool { return (bool) ($GLOBALS['jp_seo_context']['front'] ?? false); }
    function is_home(): bool { return (bool) ($GLOBALS['jp_seo_context']['home'] ?? false); }
    function is_shop(): bool { return (bool) ($GLOBALS['jp_seo_context']['shop'] ?? false); }
    function is_tax(): bool { return (bool) ($GLOBALS['jp_seo_context']['tax'] ?? false); }
    function is_category(): bool { return (bool) ($GLOBALS['jp_seo_context']['category'] ?? false); }
    function is_tag(): bool { return (bool) ($GLOBALS['jp_seo_context']['tag'] ?? false); }
    function get_post(?int $id = null): ?WP_Post { return $GLOBALS['jp_seo_context'][$id === null ? 'post' : 'shop_post'] ?? null; }
    function get_the_title(WP_Post $post): string { return $post->post_title; }
    function get_permalink(mixed $post = null): string { return $GLOBALS['jp_seo_context']['canonical']; }
    function post_password_required(WP_Post $post): bool { return $post->post_password !== ''; }
    function wp_get_document_title(): string { return $GLOBALS['jp_seo_context']['title']; }
    function wp_get_canonical_url(): string { return $GLOBALS['jp_seo_context']['canonical']; }
    function wp_get_attachment_image_url(int $id, string $size): string { return 'https://store.example/image-' . $id . '.jpg'; }
    function has_excerpt(WP_Post $post): bool { return $post->post_excerpt !== ''; }
    function get_the_excerpt(WP_Post $post): string { return $post->post_excerpt; }
    function strip_shortcodes(string $text): string { return preg_replace('/\[[^\]]*\]/', '', $text) ?? ''; }
    function wp_strip_all_tags(string $text): string { return strip_tags($text); }
    function get_bloginfo(string $key): string { return $key === 'name' ? 'Jouvence & Para' : 'Conseils et soins'; }
    function home_url(string $path): string { return 'https://store.example/' . ltrim($path, '/'); }
    function trailingslashit(string $url): string { return rtrim($url, '/') . '/'; }
    function untrailingslashit(string $url): string { return rtrim($url, '/'); }
    function user_trailingslashit(string $url, string $type): string { return trailingslashit($url); }
    function get_query_var(string $key): mixed { return $GLOBALS['jp_seo_context'][$key] ?? ''; }
    function get_queried_object(): object { return $GLOBALS['jp_seo_context']['term'] ?? (object) ['description' => '']; }
    function get_term_link(object $term): string { return $GLOBALS['jp_seo_context']['term_url']; }
    function wc_get_page_id(string $page): int { return 42; }
    function wc_get_page_permalink(string $page): string { return $GLOBALS['jp_seo_context']['shop_url'] ?? 'https://store.example/boutique/'; }
    function wp_json_encode(array $data, int $flags): string|false { return json_encode($data, $flags); }
    function add_query_arg(string $key, mixed $value, string $url): string { return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query([$key => $value]); }
    function add_rewrite_rule(string $regex, string $query, string $position): void { $GLOBALS['jp_seo_rewrites'][$regex] = [$query, $position]; }
    function flush_rewrite_rules(bool $hard = true): void { $GLOBALS['jp_seo_flushes'][] = $hard; }
}

namespace JouvencePara\Core\Discovery {
    // Simulate installed plugins without leaking irreversible PHP constants between cases.
    function defined(string $name): bool
    {
        return \defined($name) || in_array($name, $GLOBALS['jp_seo_context']['external_constants'] ?? [], true);
    }
}
