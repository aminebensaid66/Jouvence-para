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

        if (function_exists('is_cart') && is_cart()) {
            wp_enqueue_script(
                'jouvence-para-cart-count',
                get_template_directory_uri() . '/assets/js/cart-count.js',
                ['wp-data'],
                wp_get_theme()->get('Version'),
                true
            );
        }
    }
);

function account_link_markup(): string
{
    if (! function_exists('wc_get_page_id') || ! function_exists('wc_get_page_permalink')) {
        return '';
    }
    $id = wc_get_page_id('myaccount');
    if ($id < 1 || get_post_status($id) !== 'publish') {
        return '';
    }
    return '<a class="jp-account-link" href="' . esc_url(wc_get_page_permalink('myaccount')) . '">' . esc_html__('Mon compte', 'jouvence-para') . '</a>';
}

function cart_link_markup(): string
{
    if (! function_exists('wc_get_cart_url') || ! function_exists('WC')) {
        return '';
    }

    $woocommerce = \WC();
    $cart = is_object($woocommerce) && isset($woocommerce->cart) ? $woocommerce->cart : null;
    if (! is_object($cart) || ! is_callable([$cart, 'get_cart_contents_count'])) {
        return '';
    }

    $count = max(0, (int) $cart->get_cart_contents_count());
    $singularLabel = _n('%d article dans le panier', '%d articles dans le panier', 1, 'jouvence-para');
    $pluralLabel = _n('%d article dans le panier', '%d articles dans le panier', 2, 'jouvence-para');
    $label = sprintf(_n('%d article dans le panier', '%d articles dans le panier', $count, 'jouvence-para'), $count);

    return sprintf(
        '<a class="jp-cart-link" href="%1$s"><span>%2$s</span><span class="jp-cart-link__count" aria-live="polite" aria-atomic="true"><span class="jp-cart-link__number" aria-hidden="true">%3$s</span><span class="screen-reader-text jp-cart-link__announcement" data-label-singular="%4$s" data-label-plural="%5$s">%6$s</span></span></a>',
        esc_url(wc_get_cart_url()),
        esc_html__('Panier', 'jouvence-para'),
        esc_html((string) $count),
        esc_attr($singularLabel),
        esc_attr($pluralLabel),
        esc_html($label)
    );
}

add_filter(
    'woocommerce_add_to_cart_fragments',
    static function (array $fragments): array {
        $cartLink = cart_link_markup();
        if ($cartLink !== '') {
            $fragments['a.jp-cart-link'] = $cartLink;
        }

        return $fragments;
    }
);

function cart_whatsapp_share_markup(): string
{
    if (! function_exists('WC') || ! is_object(WC()) || ! isset(WC()->cart)
        || ! is_callable([WC()->cart, 'get_cart'])) {
        return '';
    }
    $items = [];
    $unavailableCount = 0;
    foreach (WC()->cart->get_cart() as $cartItem) {
        $product = is_array($cartItem) ? ($cartItem['data'] ?? null) : null;
        if (! $product instanceof \WC_Product || ! method_exists($product, 'get_id')
            || ! $product->is_visible() || get_post_status((int) $product->get_id()) !== 'publish') {
            ++$unavailableCount;
            continue;
        }
        $name = method_exists($product, 'get_name') ? (string) $product->get_name() : '';
        $url = method_exists($product, 'get_permalink') ? (string) $product->get_permalink() : '';
        $quantity = (int) ($cartItem['quantity'] ?? 0);
        $items[] = ['name' => $name, 'url' => $url, 'quantity' => $quantity];
    }
    $url = (string) apply_filters('jouvence_para_whatsapp_cart_share_url', '', $items, $unavailableCount);
    if ($url === '') {
        return $unavailableCount > 0
            ? '<p class="jp-whatsapp-cart-share__unavailable" role="status">' . esc_html__('Certains articles indisponibles ne peuvent pas être partagés.', 'jouvence-para') . '</p>'
            : '';
    }

    return '<aside class="jp-whatsapp-cart-share" aria-labelledby="jp-whatsapp-cart-share-title">'
        . '<h2 id="jp-whatsapp-cart-share-title">' . esc_html__('Partager mon panier', 'jouvence-para') . '</h2>'
        . '<p>' . esc_html__('Le message contient uniquement les noms, quantités et liens des produits affichés. Aucun renseignement personnel ou de commande n’est ajouté.', 'jouvence-para') . '</p>'
        . '<a class="jp-button jp-button--secondary" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html__('Demander conseil sur WhatsApp (nouvel onglet)', 'jouvence-para') . '</a>'
        . '<p class="jp-whatsapp-cart-share__note">' . esc_html__('Les produits masqués ou retirés sont omis. Le prix et la disponibilité restent à confirmer.', 'jouvence-para') . '</p>'
        . '</aside>';
}

add_action('woocommerce_after_cart', static function (): void { echo cart_whatsapp_share_markup(); }, 20);
add_filter(
    'render_block',
    static function (string $content, array $block): string {
        return ($block['blockName'] ?? '') === 'woocommerce/cart' ? $content . cart_whatsapp_share_markup() : $content;
    },
    20,
    2
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

add_filter(
    'woocommerce_product_upsells_products_heading',
    static fn (string $heading): string => __('Compléments de la routine', 'jouvence-para')
);

add_filter(
    'woocommerce_product_related_products_heading',
    static fn (string $heading): string => __('Produits similaires', 'jouvence-para')
);

add_filter(
    'woocommerce_upsells_orderby',
    static fn (string $orderby): string => 'menu_order'
);

add_filter(
    'woocommerce_upsells_order',
    static fn (string $order): string => 'asc'
);

add_filter(
    'woocommerce_product_related_posts_shuffle',
    static fn (bool $shuffle): bool => false
);

add_filter(
    'woocommerce_output_related_products_args',
    static function (array $args): array {
        $args['orderby'] = 'menu_order';
        $args['order'] = 'asc';

        return $args;
    }
);
