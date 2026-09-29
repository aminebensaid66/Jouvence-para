<?php

declare(strict_types=1);

namespace JouvencePara\Core\Discovery;

use JouvencePara\Core\Contracts\Module;
use WP_Post;

final class SeoModule implements Module
{
    private const PERMALINK_VERSION = '1';

    public function register(): void
    {
        add_filter('register_post_type_args', [SeoPolicy::class, 'productRewrite'], 20, 2);
        add_filter('register_taxonomy_args', [SeoPolicy::class, 'categoryRewrite'], 20, 2);
        add_filter('post_link', [$this, 'articlePermalink'], 20, 3);
        add_action('init', [$this, 'registerArticleRewrite'], 99);
        add_action('wp_head', [$this, 'metadata'], 3);
        add_action('wp_head', [$this, 'organizationSchema'], 6);
        add_action('wp_head', [$this, 'articleSchema'], 7);
        add_action('wp_head', [$this, 'archiveCanonical'], 9);
    }

    public function registerArticleRewrite(): void
    {
        add_rewrite_rule('^conseils/([^/]+)/?$', 'index.php?name=$matches[1]', 'top');
        if (get_option('jp_seo_permalink_version', '') === self::PERMALINK_VERSION) {
            return;
        }
        update_option('jp_seo_permalink_version', self::PERMALINK_VERSION, false);
        flush_rewrite_rules(false);
    }

    public function articlePermalink(string $permalink, WP_Post $post, bool $leavename): string
    {
        if ($post->post_type !== 'post' || get_option('permalink_structure', '') === '') {
            return $permalink;
        }
        $slug = $leavename ? '%postname%' : $post->post_name;
        return home_url(user_trailingslashit('conseils/' . $slug, 'single'));
    }

    public function metadata(): void
    {
        if (! $this->ownsMetadata() || ! $this->isPublicPage()) {
            return;
        }
        $title = wp_get_document_title();
        $description = $this->currentDescription();
        if ($description === '') {
            $description = SeoPolicy::description($title);
        }
        $canonical = $this->currentCanonical();
        if ($description === '' || $canonical === '') {
            return;
        }
        $image = $this->currentImage();
        $type = is_singular('post') ? 'article' : 'website';

        echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta property="og:type" content="' . esc_attr($type) . '">' . "\n";
        echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
        echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
        echo '<meta name="twitter:card" content="' . esc_attr($image === '' ? 'summary' : 'summary_large_image') . '">' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
        if ($image !== '') {
            echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
            echo '<meta name="twitter:image" content="' . esc_url($image) . '">' . "\n";
        }
    }

    public function organizationSchema(): void
    {
        if (! $this->ownsMetadata() || is_admin()) {
            return;
        }
        $name = trim((string) get_bloginfo('name'));
        $url = home_url('/');
        if ($name === '' || $url === '') {
            return;
        }
        $organization = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => trailingslashit($url) . '#organization',
            'name' => $name,
            'url' => $url,
        ];
        $this->printSchema($organization);
    }

    public function articleSchema(): void
    {
        if (! $this->ownsMetadata() || ! is_singular('post') || is_preview()) {
            return;
        }
        $post = get_post();
        if (! $post instanceof WP_Post || $post->post_status !== 'publish' || post_password_required($post)) {
            return;
        }
        $headline = trim(wp_strip_all_tags(get_the_title($post)));
        $headline = html_entity_decode($headline, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $url = get_permalink($post);
        if ($headline === '' || ! is_string($url) || $url === '') {
            return;
        }
        $article = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $headline,
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
            'publisher' => [
                '@type' => 'Organization',
                'name' => (string) get_bloginfo('name'),
                '@id' => trailingslashit(home_url('/')) . '#organization',
            ],
        ];
        $this->printSchema($article);
    }

    public function archiveCanonical(): void
    {
        if (! $this->ownsMetadata() || ! $this->isPublicPage() || is_singular()) {
            return;
        }
        $url = $this->currentCanonical();
        if ($url !== '') {
            echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
        }
    }

    private function currentDescription(): string
    {
        if (is_singular()) {
            $post = get_post();
            if (! $post instanceof WP_Post) {
                return '';
            }
            if ($post->post_type === 'product' && function_exists('wc_get_product')) {
                $product = wc_get_product($post->ID);
                if ($product instanceof \WC_Product) {
                    $description = $product->get_short_description('view');
                    if ($description === '') {
                        $description = $product->get_description('view');
                    }
                    return SeoPolicy::description(strip_shortcodes($description));
                }
            }
            $description = has_excerpt($post) ? get_the_excerpt($post) : $post->post_content;
            return SeoPolicy::description(strip_shortcodes((string) $description));
        }
        if (is_tax() || is_category() || is_tag()) {
            $term = get_queried_object();
            return is_object($term) && isset($term->description) ? SeoPolicy::description((string) $term->description) : '';
        }
        if (is_shop() && function_exists('wc_get_page_id')) {
            $shop = get_post(wc_get_page_id('shop'));
            if ($shop instanceof WP_Post && trim((string) $shop->post_content) !== '') {
                return SeoPolicy::description(strip_shortcodes($shop->post_content));
            }
            return '';
        }
        return SeoPolicy::description((string) get_bloginfo('description'));
    }

    private function currentCanonical(): string
    {
        if (is_singular()) {
            $url = wp_get_canonical_url();
            return is_string($url) ? $url : '';
        }
        if (is_shop() && function_exists('wc_get_page_permalink')) {
            return $this->pagedUrl((string) wc_get_page_permalink('shop'));
        }
        if (is_tax() || is_category() || is_tag()) {
            $url = get_term_link(get_queried_object());
            return is_string($url) ? $this->pagedUrl($url) : '';
        }
        if (is_home() && ! is_front_page()) {
            $postsPageId = (int) get_option('page_for_posts', 0);
            if ($postsPageId > 0) {
                return $this->pagedUrl((string) get_permalink($postsPageId));
            }
        }
        if (is_front_page() || is_home()) {
            return $this->pagedUrl(home_url('/'));
        }
        return '';
    }

    private function currentImage(): string
    {
        if (is_singular()) {
            $post = get_post();
            if ($post instanceof WP_Post && $post->post_type === 'product' && function_exists('wc_get_product')) {
                $product = wc_get_product($post->ID);
                if ($product instanceof \WC_Product && $product->get_image_id() > 0) {
                    $image = wp_get_attachment_image_url($product->get_image_id(), 'full');
                    return is_string($image) ? $image : '';
                }
            }
            return '';
        }
        return '';
    }

    private function isPublicPage(): bool
    {
        if (is_404() || is_search() || is_feed() || is_preview()) {
            return false;
        }
        if (
            (function_exists('is_cart') && is_cart())
            || (function_exists('is_checkout') && is_checkout())
            || (function_exists('is_account_page') && is_account_page())
        ) {
            return false;
        }
        if (is_singular()) {
            $post = get_post();
            return $post instanceof WP_Post && $post->post_status === 'publish' && ! post_password_required($post);
        }
        return is_front_page() || is_home() || is_shop() || is_tax() || is_category() || is_tag();
    }

    private function ownsMetadata(): bool
    {
        return ! defined('WPSEO_VERSION')
            && ! defined('RANK_MATH_VERSION')
            && ! defined('SEOPRESS_VERSION')
            && ! function_exists('aioseo')
            && ! class_exists('SEOPress', false);
    }

    private function pagedUrl(string $url): string
    {
        $page = max(1, (int) get_query_var('paged'));
        if ($page === 1) {
            return $url;
        }
        if (get_option('permalink_structure', '') === '') {
            return (string) add_query_arg('paged', $page, $url);
        }
        $paginationBase = sanitize_title((string) ($GLOBALS['wp_rewrite']->pagination_base ?? 'page'));
        if ($paginationBase === '') {
            $paginationBase = 'page';
        }
        return user_trailingslashit(untrailingslashit($url) . '/' . $paginationBase . '/' . $page, 'paged');
    }

    /** @param array<string, mixed> $data */
    private function printSchema(array $data): void
    {
        $json = wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES);
        if (is_string($json)) {
            echo '<script type="application/ld+json">' . $json . '</script>' . "\n";
        }
    }
}
