<?php

declare(strict_types=1);

namespace JouvencePara\Theme;

if (! defined('ABSPATH')) {
    exit;
}

add_action(
    'after_setup_theme',
    static function (): void {
        load_theme_textdomain('jouvence-para', get_template_directory() . '/languages');
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('responsive-embeds');
        add_theme_support('woocommerce');
        add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');
        add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
        register_nav_menus(['primary' => __('Primary navigation', 'jouvence-para')]);
    }
);

add_action(
    'wp_enqueue_scripts',
    static function (): void {
        wp_enqueue_style(
            'jouvence-para',
            get_stylesheet_uri(),
            [],
            wp_get_theme()->get('Version')
        );
        wp_enqueue_style(
            'jouvence-para-cookie-consent',
            get_template_directory_uri() . '/assets/css/cookie-consent.css',
            ['jouvence-para'],
            wp_get_theme()->get('Version')
        );
        wp_enqueue_script(
            'jouvence-para-cookie-consent',
            get_template_directory_uri() . '/assets/js/cookie-consent.js',
            [],
            wp_get_theme()->get('Version'),
            true
        );
    }
);

add_action(
    'woocommerce_single_product_summary',
    static function (): void {
        global $product;
        if (! $product instanceof \WC_Product) {
            return;
        }
        $brands = get_the_terms($product->get_id(), 'jp_brand');
        if (! is_array($brands) || ! isset($brands[0])) {
            return;
        }
        echo '<p class="jp-product-detail__brand">' . esc_html($brands[0]->name) . '</p>';
    },
    4
);
