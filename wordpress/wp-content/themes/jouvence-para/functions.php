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
    }
);
