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
        wp_enqueue_script(
            'jouvence-para-whatsapp-context',
            get_template_directory_uri() . '/assets/js/whatsapp-context.js',
            ['jouvence-para-cookie-consent'],
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

add_action(
    'woocommerce_single_product_summary',
    static function (): void {
        global $product;
        if (! $product instanceof \WC_Product) {
            return;
        }
        $url = (string) apply_filters('jouvence_para_whatsapp_url', '', $product);
        if ($url === '') {
            return;
        }
        $hours = (string) apply_filters('jouvence_para_whatsapp_hours', '');
        echo '<div class="jp-whatsapp-advice">';
        echo '<a class="jp-button jp-button--secondary" data-jp-whatsapp-click data-jp-product-id="' . esc_attr((string) $product->get_id()) . '" href="' . esc_url($url) . '" rel="noopener noreferrer">' . esc_html__('Demander conseil sur WhatsApp', 'jouvence-para') . '</a>';
        if ($hours !== '') {
            echo '<p class="jp-whatsapp-advice__hours">' . esc_html($hours) . '</p>';
        }
        echo '</div>';
    },
    35
);

add_action(
    'woocommerce_single_product_summary',
    static function (): void {
        $advice = apply_filters('jouvence_para_delivery_advice', []);
        if (! is_array($advice) || $advice === []) {
            return;
        }
        echo '<aside class="jp-delivery-advice" aria-labelledby="jp-delivery-advice-title">';
        echo '<strong id="jp-delivery-advice-title">' . esc_html__('Livraison et retrait', 'jouvence-para') . '</strong>';
        echo '<p>' . esc_html__('Livraison nationale : 7,000 TND · offerte dès 200,000 TND après remises · estimation 2 jours ouvrés.', 'jouvence-para') . '</p>';
        echo '<p>' . esc_html__('Retrait en boutique gratuit.', 'jouvence-para') . '</p>';
        if (($advice['policy_url'] ?? '') !== '') {
            echo '<a href="' . esc_url((string) $advice['policy_url']) . '">' . esc_html__('Voir les conditions de livraison', 'jouvence-para') . '</a>';
        }
        echo '</aside>';
    },
    34
);

add_action(
    'woocommerce_before_shop_loop',
    static function (): void {
        $groups = apply_filters('jouvence_para_facets', []);
        if (! is_array($groups) || $groups === []) {
            return;
        }
        echo '<form class="jp-facets" method="get" aria-label="' . esc_attr__('Filtres produits', 'jouvence-para') . '">';
        if (isset($_GET['orderby']) && sanitize_key((string) wp_unslash($_GET['orderby'])) !== '') { echo '<input type="hidden" name="orderby" value="' . esc_attr(sanitize_key((string) wp_unslash($_GET['orderby']))) . '">'; }
        $activeChips = [];
        foreach ($groups as $group) {
            $key = (string) ($group['key'] ?? '');
            $options = is_array($group['options'] ?? null) ? $group['options'] : [];
            if ($key === '' || $options === []) { continue; }
            echo '<fieldset><legend>' . esc_html((string) ($group['label'] ?? $key)) . '</legend>';
            foreach ($options as $option) {
                $slug = (string) ($option['slug'] ?? '');
                if ($slug === '') { continue; }
                $active = (bool) ($option['active'] ?? false);
                echo '<label><input type="checkbox" name="jp_filter_' . esc_attr($key) . '[]" value="' . esc_attr($slug) . '" ' . checked($active, true, false) . '> ';
                echo esc_html((string) ($option['name'] ?? $slug)) . ' <span aria-label="' . esc_attr__('nombre de produits', 'jouvence-para') . '">(' . esc_html((string) (int) ($option['count'] ?? 0)) . ')</span></label>';
                if ($active) {
                    $param = 'jp_filter_' . $key;
                    $rawValues = isset($_GET[$param]) ? (array) wp_unslash($_GET[$param]) : [];
                    $remaining = array_values(array_filter(array_map('sanitize_key', $rawValues), static fn (string $value): bool => $value !== $slug));
                    $clearUrl = $remaining === [] ? remove_query_arg($param) : add_query_arg($param, $remaining);
                    $activeChips[] = '<li><a href="' . esc_url($clearUrl) . '" aria-label="' . esc_attr(sprintf(__('Retirer le filtre %s', 'jouvence-para'), (string) ($option['name'] ?? $slug))) . '">' . esc_html((string) ($option['name'] ?? $slug)) . ' ×</a></li>';
                }
            }
            echo '</fieldset>';
        }
        if ($activeChips !== []) {
            echo '<div class="jp-active-filters" aria-live="polite"><strong>' . esc_html__('Filtres actifs', 'jouvence-para') . '</strong><ul>' . wp_kses_post(implode('', $activeChips)) . '</ul></div>';
        }
        echo '<div class="jp-facets__actions"><button class="jp-button" type="submit">' . esc_html__('Appliquer les filtres', 'jouvence-para') . '</button>';
        echo '<a href="' . esc_url(remove_query_arg(array_map(static fn ($k) => 'jp_filter_' . $k, array_keys(\JouvencePara\Core\Discovery\FilterState::TAXONOMIES)))) . '">' . esc_html__('Effacer tous les filtres', 'jouvence-para') . '</a></div>';
        echo '</form>';
    },
    5
);
