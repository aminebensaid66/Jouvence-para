<?php

declare(strict_types=1);

use JouvencePara\Core\Customers\WishlistModule;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Contracts/Module.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Customers/WishlistPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Customers/WishlistModule.php';
require_once __DIR__ . '/account-fixture.php';

if (! defined('EP_ROOT')) { define('EP_ROOT', 1); }
if (! defined('EP_PAGES')) { define('EP_PAGES', 2); }

if (! class_exists('JP_Test_Wishlist_Product')) {
    final class JP_Test_Wishlist_Product extends WC_Product
    {
        public bool $visible = true;
        public bool $purchasable = true;
        public bool $inStock = true;

        public function is_visible(): bool { return $this->visible; }
        public function is_purchasable(): bool { return $this->purchasable; }
        public function is_in_stock(): bool { return $this->inStock; }
        public function get_permalink(): string { return 'https://store.example/product/' . $this->get_id() . '/'; }
        public function get_image(): string { return '<img src="https://store.example/product.jpg" alt="">'; }
        public function get_min_purchase_quantity(): int { return 1; }
    }
}

if (! function_exists('is_user_logged_in')) {
    function is_user_logged_in(): bool { return (int) ($GLOBALS['jp_account_actor'] ?? 0) > 0; }
}
if (! function_exists('wp_generate_uuid4')) {
    function wp_generate_uuid4(): string { return 'aaaaaaaa-bbbb-4ccc-8ddd-eeeeeeeeeeee'; }
}
if (! function_exists('wp_kses_post')) {
    function wp_kses_post(string $html): string { return $html; }
}
if (! function_exists('add_rewrite_endpoint')) {
    function add_rewrite_endpoint(string $name, int $places): void { $GLOBALS['jp_wishlist_endpoint'] = [$name, $places]; }
}
if (! function_exists('wp_nonce_field')) {
    function wp_nonce_field(string $action, string $name): void { echo '<input type="hidden" name="' . esc_attr($name) . '" value="valid">'; }
}
if (! function_exists('wp_die')) {
    function wp_die(string $message, string $title = '', array $args = []): never { throw new RuntimeException($message, (int) ($args['response'] ?? 500)); }
}
if (! function_exists('wp_safe_redirect')) {
    function wp_safe_redirect(string $location): void { $GLOBALS['jp_wishlist_redirect'] = $location; }
}
if (! function_exists('wc_get_account_endpoint_url')) {
    function wc_get_account_endpoint_url(string $endpoint): string { return 'https://store.example/my-account/' . $endpoint . '/'; }
}

function jp_test_wishlist_reset(): void
{
    jp_test_account_reset();
    $GLOBALS['jp_test_hooks'] = [];
    $GLOBALS['jp_test_products'] = [];
    $GLOBALS['jp_wishlist_nonce_valid'] = true;
    $GLOBALS['jp_test_capability'] = true;
    $GLOBALS['jp_test_woocommerce'] = (object) [
        'cart' => new class {
            public int $additions = 0;
            public array $items = [];
            public function add_to_cart(int $productId, int $quantity): string|false
            {
                ++$this->additions;
                $this->items[] = ['product_id' => $productId, 'quantity' => $quantity];
                return 'cart-key';
            }
            public function get_cart(): array { return $this->items; }
        },
        'session' => new class {
            public array $data = [];
            public function get(string $key, mixed $default = ''): mixed { return $this->data[$key] ?? $default; }
            public function set(string $key, mixed $value): void { $this->data[$key] = $value; }
        },
    ];
    $GLOBALS['jp_test_products'][41] = new JP_Test_Wishlist_Product(['id' => 41, 'name' => 'Crème <script>bad</script>', 'status' => 'publish']);
    $GLOBALS['jp_account_page_status'] = 'publish';
    $_POST = [];
}

return [
    'registers account-only endpoint and does not expose navigation to guests' => static function (TestHarness $test): void {
        jp_test_wishlist_reset();
        $module = new WishlistModule();
        $module->register();
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['woocommerce_account_wishlist_endpoint']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['template_redirect']));
        $GLOBALS['jp_account_actor'] = 0;
        $guestItems = $module->accountMenuItems(['dashboard' => 'Dashboard', 'orders' => 'Orders']);
        $test->assertSame(['dashboard' => 'Dashboard', 'orders' => 'Orders'], $guestItems);
        $GLOBALS['jp_account_actor'] = 7;
        $memberItems = $module->accountMenuItems(['dashboard' => 'Dashboard', 'orders' => 'Orders', 'customer-logout' => 'Logout']);
        $test->assertSame(['dashboard', 'orders', 'wishlist', 'customer-logout'], array_keys($memberItems));
    },
    'endpoint rewrite is registered and flushed only once' => static function (TestHarness $test): void {
        jp_test_wishlist_reset();
        $GLOBALS['jp_test_options'] = [];
        $GLOBALS['jp_seo_flushes'] = [];
        $module = new WishlistModule();
        $module->registerEndpoint();
        $module->registerEndpoint();
        $test->assertSame(['wishlist', EP_ROOT | EP_PAGES], $GLOBALS['jp_wishlist_endpoint']);
        $test->assertSame([false], $GLOBALS['jp_seo_flushes']);
        $test->assertSame('1', get_option('jp_wishlist_endpoint_version'));
    },
    'wishlist persists privately, deduplicates retries, removes idempotently and rejects another owner' => static function (TestHarness $test): void {
        jp_test_wishlist_reset();
        $module = new WishlistModule();
        $test->assertTrue($module->processAction('add', 7, 41));
        $test->assertTrue($module->processAction('add', 7, 41));
        $test->assertSame([41], $GLOBALS['jp_account_meta'][7]['_jp_wishlist_product_ids']);
        $test->assertTrue(! $module->processAction('add', 8, 41));
        $test->assertTrue($module->processAction('remove', 7, 41));
        $test->assertTrue($module->processAction('remove', 7, 41));
        $test->assertSame([], $GLOBALS['jp_account_meta'][7]['_jp_wishlist_product_ids']);
    },
    'metadata read failure cannot erase an existing wishlist' => static function (TestHarness $test): void {
        jp_test_wishlist_reset();
        $GLOBALS['jp_account_meta'][7]['_jp_wishlist_product_ids'] = [41, 99];
        $GLOBALS['jp_account_read_fail'] = true;
        $module = new WishlistModule();
        $test->assertTrue(! $module->processAction('add', 7, 41));
        $test->assertTrue(! $module->processAction('remove', 7, 99));
        $test->assertSame([41, 99], $GLOBALS['jp_account_meta'][7]['_jp_wishlist_product_ids']);
        $test->assertSame(0, $GLOBALS['jp_account_saves']);
    },
    'full wishlist rejects a new item without reporting a successful save' => static function (TestHarness $test): void {
        jp_test_wishlist_reset();
        $GLOBALS['jp_account_meta'][7]['_jp_wishlist_product_ids'] = range(1, 100);
        $GLOBALS['jp_test_products'][101] = new JP_Test_Wishlist_Product(['id' => 101, 'name' => 'Another product', 'status' => 'publish']);
        $module = new WishlistModule();
        $test->assertTrue(! $module->processAction('add', 7, 101));
        $test->assertSame(range(1, 100), $GLOBALS['jp_account_meta'][7]['_jp_wishlist_product_ids']);
        $test->assertSame(0, $GLOBALS['jp_account_saves']);
    },
    'only public product details render and stale entries remain removable' => static function (TestHarness $test): void {
        jp_test_wishlist_reset();
        $GLOBALS['jp_account_meta'][7]['_jp_wishlist_product_ids'] = [41, 99];
        $GLOBALS['jp_test_products'][41] = new JP_Test_Wishlist_Product(['id' => 41, 'name' => 'Crème <script>bad</script>', 'status' => 'publish']);
        $module = new WishlistModule();
        $html = jp_test_seo_output([$module, 'renderWishlist']);
        $test->assertTrue(str_contains($html, 'Crème &lt;script&gt;bad&lt;/script&gt;'));
        $test->assertTrue(str_contains($html, 'Produit indisponible'));
        $test->assertTrue(str_contains($html, 'Retirer'));
        $hidden = new JP_Test_Wishlist_Product(['id' => 41, 'name' => 'Private product', 'status' => 'publish']);
        $hidden->visible = false;
        $GLOBALS['jp_test_products'][41] = $hidden;
        $hiddenHtml = jp_test_seo_output([$module, 'renderWishlist']);
        $test->assertTrue(! str_contains($hiddenHtml, 'Private product'));
    },
    'product page action is bound to the current owner and guest UI does not create a list' => static function (TestHarness $test): void {
        jp_test_wishlist_reset();
        $GLOBALS['product'] = $GLOBALS['jp_test_products'][41];
        $module = new WishlistModule();
        $html = jp_test_seo_output([$module, 'renderProductButton']);
        $test->assertTrue(str_contains($html, 'Ajouter à ma liste de souhaits'));
        $test->assertSame(['jp_wishlist_7_41', 'jp_wishlist_nonce'], $GLOBALS['jp_account_fields']['nonce']);
        $GLOBALS['jp_account_actor'] = 0;
        $guestHtml = jp_test_seo_output([$module, 'renderProductButton']);
        $test->assertTrue(str_contains($guestHtml, 'Connectez-vous pour enregistrer ce produit'));
        $test->assertTrue(! str_contains($guestHtml, 'jp_wishlist_action'));
        unset($GLOBALS['product']);
    },
    'invalid wishlist CSRF nonce is rejected before any customer write' => static function (TestHarness $test): void {
        jp_test_wishlist_reset();
        $_POST = [
            'jp_wishlist_action' => 'add',
            'jp_wishlist_product_id' => '41',
            'jp_wishlist_nonce' => 'invalid',
        ];
        try {
            (new WishlistModule())->handleRequest();
            $test->assertTrue(false, 'Invalid nonce was accepted');
        } catch (RuntimeException $exception) {
            $test->assertSame(403, $exception->getCode());
        }
        $test->assertSame([], $GLOBALS['jp_account_meta']);
        $_POST = [];
    },
    'cart replay is bounded, checks the cart, and adds again after removal' => static function (TestHarness $test): void {
        jp_test_wishlist_reset();
        $module = new WishlistModule();
        $token = 'aaaaaaaa-bbbb-4ccc-8ddd-eeeeeeeeeeee';
        $GLOBALS['jp_test_products'][42] = new JP_Test_Wishlist_Product(['id' => 42, 'name' => 'Product 42', 'status' => 'publish']);
        $test->assertTrue($module->processAction('cart', 7, 41, $token));
        $test->assertTrue($module->processAction('cart', 7, 41, $token));
        $test->assertSame(1, $GLOBALS['jp_test_woocommerce']->cart->additions);
        $test->assertTrue(! $module->processAction('cart', 7, 42, $token));
        $GLOBALS['jp_test_woocommerce']->cart->items = [];
        $test->assertTrue($module->processAction('cart', 7, 41, $token));
        $test->assertSame(2, $GLOBALS['jp_test_woocommerce']->cart->additions);
        for ($index = 0; $index < 40; ++$index) {
            $module->processAction('cart', 7, 41, sprintf('%08x-bbbb-4ccc-8ddd-%012x', $index + 1, $index + 1));
        }
        $test->assertSame(32, count($GLOBALS['jp_test_woocommerce']->session->get('jp_wishlist_cart_requests')));
        $product = new JP_Test_Wishlist_Product(['id' => 42, 'type' => 'variable', 'status' => 'publish']);
        $GLOBALS['jp_test_products'][42] = $product;
        $test->assertTrue(! $module->processAction('cart', 7, 42, 'bbbbbbbb-bbbb-4ccc-8ddd-eeeeeeeeeeee'));
        $product->inStock = false;
        $test->assertTrue(! $module->processAction('cart', 7, 42, 'cccccccc-bbbb-4ccc-8ddd-eeeeeeeeeeee'));
        $test->assertTrue(! $module->processAction('cart', 7, 41, 'invalid'));
    },
];
