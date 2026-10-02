<?php

declare(strict_types=1);

require_once __DIR__ . '/theme-fixture.php';

if (! class_exists('JP_Test_Cart')) {
    final class JP_Test_Cart
    {
        /** @param list<array<string, mixed>> $items */
        public function __construct(public int $count = 0, private array $items = []) {}

        public function get_cart_contents_count(): int
        {
            return $this->count;
        }

        /** @return list<array<string, mixed>> */
        public function get_cart(): array
        {
            return $this->items;
        }
    }
}

if (! class_exists('JP_Test_WhatsApp_Share_Product')) {
    final class JP_Test_WhatsApp_Share_Product extends WC_Product
    {
        public bool $visible = true;
        public function is_visible(): bool { return $this->visible; }
        public function get_permalink(): string { return 'https://store.example/product/' . $this->get_id() . '/'; }
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

if (! function_exists('apply_filters')) {
    function apply_filters(string $hook, mixed $value, mixed ...$args): mixed
    {
        if ($hook === 'jouvence_para_whatsapp_cart_share_url') {
            return (new \JouvencePara\Core\Support\WhatsAppService())->cartUrl($args[0] ?? [], (int) ($args[1] ?? 0));
        }
        return $value;
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
        jp_test_theme_hooks();
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
        $filters = jp_test_theme_hooks()['filter'] ?? [];
        $registrations = $filters['woocommerce_add_to_cart_fragments'] ?? [];
        $test->assertTrue($registrations !== [], 'Missing WooCommerce cart fragment filter');

        $GLOBALS['jp_test_woocommerce'] = (object) ['cart' => new JP_Test_Cart(4)];
        $callback = $registrations[array_key_last($registrations)][0];
        $fragments = $callback(['.existing-fragment' => '<span>keep</span>']);

        $test->assertSame('<span>keep</span>', $fragments['.existing-fragment']);
        $test->assertTrue(str_contains($fragments['a.jp-cart-link'], 'jp-cart-link__number" aria-hidden="true">4</span>'));
        $test->assertTrue(str_contains($fragments['a.jp-cart-link'], '>4 articles dans le panier</span>'));
    },
    'cart link is omitted when WooCommerce has not initialized its cart' => static function (TestHarness $test): void {
        jp_test_theme_hooks();
        $GLOBALS['jp_test_woocommerce'] = (object) ['cart' => null];

        $test->assertSame('', \JouvencePara\Theme\cart_link_markup());
    },
    'cart block count script reads native WooCommerce cart state' => static function (TestHarness $test): void {
        $script = (string) file_get_contents(__DIR__ . '/../../wordpress/wp-content/themes/jouvence-para/assets/js/cart-count.js');

        $test->assertTrue(str_contains($script, "select('wc/store/cart')"));
        $test->assertTrue(str_contains($script, 'cartData.itemsCount'));
        $test->assertTrue(str_contains($script, 'wp.data.subscribe(updateCartItemsCount)'));
        $test->assertTrue(str_contains($script, 'jp-cart-link__announcement'));
    },
    'classic cart share renders only a public product and keeps private cart fields out of the WhatsApp URL' => static function (TestHarness $test): void {
        $hooks = jp_test_theme_hooks();
        $GLOBALS['jp_account_page_status'] = 'publish';
        $GLOBALS['jp_test_product_statuses'] = [52 => 'private'];
        $public = new JP_Test_WhatsApp_Share_Product(['id' => 41, 'name' => 'Crème douceur', 'status' => 'publish']);
        $hidden = new JP_Test_WhatsApp_Share_Product(['id' => 52, 'name' => 'Produit privé', 'status' => 'publish']);
        $GLOBALS['jp_test_woocommerce'] = (object) ['cart' => new JP_Test_Cart(3, [
            ['data' => $public, 'quantity' => 2],
            ['data' => $hidden, 'quantity' => 1],
        ])];
        $callbacks = $hooks['action']['woocommerce_after_cart'] ?? [];
        $render = $callbacks[array_key_last($callbacks)][0];
        ob_start();
        $render();
        $markup = (string) ob_get_clean();
        $test->assertTrue(str_contains($markup, 'Demander conseil sur WhatsApp'), 'Classic cart did not render the share CTA');
        $test->assertTrue(str_contains($markup, 'renseignement personnel'), 'Privacy disclosure is missing');
        $test->assertTrue(str_contains($markup, 'Produit privé') === false, 'Hidden product name leaked into markup');
        $href = html_entity_decode((string) (preg_match('/href="([^"]+)"/', $markup, $match) ? $match[1] : ''), ENT_QUOTES, 'UTF-8');
        $message = rawurldecode((string) parse_url($href, PHP_URL_QUERY));
        $test->assertTrue(str_contains($message, '2 × Crème douceur'), 'Public cart context is missing from the WhatsApp message');
        $test->assertTrue(str_contains($message, '1 autre(s) article(s) non inclus'), 'Omitted cart count is missing');
        $test->assertTrue(! str_contains($message, 'Produit privé'), 'Hidden product leaked into the WhatsApp message');
    },
    'Cart Block share preserves block output and announces when all cart products are unavailable' => static function (TestHarness $test): void {
        $hooks = jp_test_theme_hooks();
        $GLOBALS['jp_account_page_status'] = 'publish';
        $GLOBALS['jp_test_product_statuses'] = [52 => 'private'];
        $hidden = new JP_Test_WhatsApp_Share_Product(['id' => 52, 'name' => 'Produit privé', 'status' => 'publish']);
        $GLOBALS['jp_test_woocommerce'] = (object) ['cart' => new JP_Test_Cart(1, [['data' => $hidden, 'quantity' => 1]])];
        $callbacks = $hooks['filter']['render_block'] ?? [];
        $render = $callbacks[array_key_last($callbacks)][0];
        $markup = $render('<div>WooCommerce cart</div>', ['blockName' => 'woocommerce/cart']);
        $test->assertTrue(str_starts_with($markup, '<div>WooCommerce cart</div>'));
        $test->assertTrue(str_contains($markup, 'role="status"'));
        $test->assertTrue(str_contains($markup, 'Certains articles indisponibles'));
        $test->assertTrue(! str_contains($markup, 'Produit privé'));
        $test->assertTrue(! str_contains($markup, 'wa.me/'));

        $public = new JP_Test_WhatsApp_Share_Product(['id' => 41, 'name' => 'Crème douceur', 'status' => 'publish']);
        $GLOBALS['jp_test_woocommerce']->cart = new JP_Test_Cart(1, [['data' => $public, 'quantity' => 1]]);
        $withProducts = $render('<div>WooCommerce cart</div>', ['blockName' => 'woocommerce/cart']);
        $test->assertTrue(str_contains($withProducts, 'href="https://wa.me/21629302202?text='), 'Cart Block share URL is missing');
        $href = html_entity_decode((string) (preg_match('/href="([^"]+)"/', $withProducts, $match) ? $match[1] : ''), ENT_QUOTES, 'UTF-8');
        $message = rawurldecode((string) parse_url($href, PHP_URL_QUERY));
        $test->assertTrue(str_contains($message, '1 × Crème douceur'), 'Cart Block product context is missing');
    },
];
