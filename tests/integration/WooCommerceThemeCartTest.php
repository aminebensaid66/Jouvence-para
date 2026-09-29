<?php

declare(strict_types=1);

if (! class_exists('JP_Test_Cart')) {
    final class JP_Test_Cart
    {
        public function __construct(public int $count = 0) {}

        public function get_cart_contents_count(): int
        {
            return $this->count;
        }
    }
}

if (! function_exists('WC')) {
    function WC(): object
    {
        return $GLOBALS['jp_test_woocommerce'];
    }
}

if (! function_exists('wc_get_cart_url')) {
    function wc_get_cart_url(): string
    {
        return (string) ($GLOBALS['jp_test_cart_url'] ?? 'https://store.example/cart/');
    }
}

if (! function_exists('esc_url')) {
    function esc_url(string $url): string { return htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); }
}

if (! function_exists('esc_attr')) {
    function esc_attr(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
}

if (! function_exists('esc_html')) {
    function esc_html(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
}

if (! function_exists('esc_html__')) {
    function esc_html__(string $text, string $domain = 'default'): string
    {
        unset($domain);

        return esc_html($text);
    }
}

if (! function_exists('_n')) {
    function _n(string $singular, string $plural, int $number, string $domain = 'default'): string
    {
        unset($domain);

        return $number === 1 ? $singular : $plural;
    }
}

return [
    'header cart link uses the WooCommerce URL and current item count' => static function (TestHarness $test): void {
        if (! defined('ABSPATH')) {
            define('ABSPATH', __DIR__ . '/');
        }

        $GLOBALS['jp_test_hooks'] = [];
        require_once __DIR__ . '/../../wordpress/wp-content/themes/jouvence-para/functions.php';
        $GLOBALS['jp_test_woocommerce'] = (object) ['cart' => new JP_Test_Cart(2)];
        $GLOBALS['jp_test_cart_url'] = 'https://store.example/cart/?from=header&tab=cart';

        $markup = \JouvencePara\Theme\cart_link_markup();

        $test->assertTrue(str_contains($markup, 'href="https://store.example/cart/?from=header&amp;tab=cart"'));
        $test->assertTrue(str_contains($markup, 'jp-cart-link__number" aria-hidden="true">2</span>'));
        $test->assertTrue(str_contains($markup, 'aria-live="polite" aria-atomic="true"'));
        $test->assertTrue(str_contains($markup, 'data-label-singular="%d article dans le panier"'));
        $test->assertTrue(str_contains($markup, 'data-label-plural="%d articles dans le panier"'));
        $test->assertTrue(str_contains($markup, 'jp-cart-link__announcement" data-label-singular="%d article dans le panier" data-label-plural="%d articles dans le panier">2 articles dans le panier'));
    },
    'AJAX cart fragments refresh the header count and preserve other fragments' => static function (TestHarness $test): void {
        $filters = $GLOBALS['jp_test_hooks']['filter'] ?? [];
        $registrations = $filters['woocommerce_add_to_cart_fragments'] ?? [];
        $test->assertTrue($registrations !== [], 'Missing WooCommerce cart fragment filter');

        $GLOBALS['jp_test_woocommerce']->cart->count = 4;
        $callback = $registrations[array_key_last($registrations)][0];
        $fragments = $callback(['.existing-fragment' => '<span>keep</span>']);

        $test->assertSame('<span>keep</span>', $fragments['.existing-fragment']);
        $test->assertTrue(str_contains($fragments['a.jp-cart-link'], 'jp-cart-link__number" aria-hidden="true">4</span>'));
        $test->assertTrue(str_contains($fragments['a.jp-cart-link'], '>4 articles dans le panier</span>'));
    },
    'cart link is omitted when WooCommerce has not initialized its cart' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_woocommerce']->cart = null;

        $test->assertSame('', \JouvencePara\Theme\cart_link_markup());
    },
    'cart block count script reads native WooCommerce cart state' => static function (TestHarness $test): void {
        $script = (string) file_get_contents(__DIR__ . '/../../wordpress/wp-content/themes/jouvence-para/assets/js/cart-count.js');

        $test->assertTrue(str_contains($script, "select('wc/store/cart')"));
        $test->assertTrue(str_contains($script, 'cartData.itemsCount'));
        $test->assertTrue(str_contains($script, 'wp.data.subscribe(updateCartItemsCount)'));
        $test->assertTrue(str_contains($script, 'jp-cart-link__announcement'));
    },
];
