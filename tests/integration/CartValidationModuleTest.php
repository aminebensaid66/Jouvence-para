<?php

declare(strict_types=1);

use JouvencePara\Core\Cart\CartValidationModule;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Contracts/Module.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Inventory/InventoryPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Cart/CartValidationModule.php';
require_once __DIR__ . '/cart-validation-fixture.php';

return [
    'registers classic and Store API critical checks without changing native cart keys' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        (new CartValidationModule())->register();
        foreach (['woocommerce_cart_loaded_from_session', 'woocommerce_before_calculate_totals', 'woocommerce_check_cart_items', 'woocommerce_checkout_create_order', 'woocommerce_store_api_cart_errors', 'woocommerce_store_api_checkout_update_order_meta', 'woocommerce_store_api_checkout_order_processed'] as $hook) {
            $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action'][$hook]), $hook);
        }
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['woocommerce_add_cart_item']));
        $test->assertTrue(! isset($GLOBALS['jp_test_hooks']['filter']['woocommerce_add_cart_item_data']));
    },
    'cart load refreshes catalog prices preserves options and deduplicates change notices' => static function (TestHarness $test): void {
        $cart = jp_test_validation_cart();
        $GLOBALS['jp_cart_products'][41]->price = '30';
        $module = new CartValidationModule();
        $module->cartLoaded($cart);
        $module->cartLoaded($cart);
        $test->assertSame('30.000', $cart->cart_contents['line']['_jp_catalog_price']);
        $test->assertSame('30', $cart->cart_contents['line']['data']->get_price());
        $test->assertSame(2, $cart->cart_contents['line']['quantity']);
        $test->assertSame(['keep' => true], $cart->cart_contents['line']['options']);
        $test->assertSame(1, count($GLOBALS['jp_cart_notices']['notice']));
        $test->assertSame([], $GLOBALS['jp_cart_notices']['error'] ?? []);
        $test->assertSame(1, $cart->calculations);
        $test->assertSame('30.000', $GLOBALS['jp_cart_wc']->session->data['cart']['line']['_jp_catalog_price']);
        $test->assertSame('recalculated', $GLOBALS['jp_cart_wc']->session->data['cart_totals']);
    },
    'new and legacy lines establish a baseline without inventing historical changes' => static function (TestHarness $test): void {
        $cart = jp_test_validation_cart();
        $module = new CartValidationModule();
        $item = $module->rememberPrice(['product_id' => 41, 'variation_id' => 0], 'native-key');
        $test->assertSame('32.900', $item['_jp_catalog_price']);
        unset($cart->cart_contents['line']['_jp_catalog_price']);
        $module->cartLoaded($cart);
        $test->assertSame([], $GLOBALS['jp_cart_notices']);
        $module->finalOrder(jp_test_validation_order());
    },
    'checkout aggregates duplicate product demand and subtracts held stock and safety buffer' => static function (TestHarness $test): void {
        $cart = jp_test_validation_cart(6);
        $cart->cart_contents['second'] = $cart->cart_contents['line'];
        $cart->cart_contents['second']['quantity'] = 3;
        $GLOBALS['jp_cart_held'][41][0] = 3;
        (new CartValidationModule())->checkCart();
        $test->assertSame(1, count($GLOBALS['jp_cart_notices']['error']));
        $test->assertSame(6, $cart->cart_contents['line']['quantity']);
        $test->assertSame(3, $cart->cart_contents['second']['quantity']);
    },
    'same-product lines preserve independent mutable objects for per-line options and pricing' => static function (TestHarness $test): void {
        $cart = jp_test_validation_cart();
        $cart->cart_contents['second'] = $cart->cart_contents['line'];
        $cart->cart_contents['second']['options'] = ['different' => true];
        (new CartValidationModule())->refreshPrices($cart);
        $first = $cart->cart_contents['line']['data'];
        $second = $cart->cart_contents['second']['data'];
        $test->assertTrue($first !== $second);
        $first->set_price('10');
        $test->assertSame('32.900', $second->get_price());
        $test->assertSame('32.900', $GLOBALS['jp_cart_products'][41]->get_price());
    },
    'parent-managed variations aggregate against the actual stock owner' => static function (TestHarness $test): void {
        $cart = jp_test_validation_cart(5);
        $variation = jp_test_validation_product(['id' => 42, 'type' => 'variation', 'stock_quantity' => null]);
        $variation->ownerId = 41;
        $GLOBALS['jp_cart_products'][42] = $variation;
        $cart->cart_contents['line']['variation_id'] = 42;
        $cart->cart_contents['second'] = $cart->cart_contents['line'];
        $cart->cart_contents['second']['quantity'] = 7;
        (new CartValidationModule())->checkCart();
        $test->assertSame(1, count($GLOBALS['jp_cart_notices']['error']));
    },
    'unavailable removed unmanaged and invalid-quantity lines block ordering without deletion' => static function (TestHarness $test): void {
        foreach (['removed', 'private', 'outofstock', 'unmanaged', 'fractional', 'negative', 'infinite'] as $scenario) {
            $cart = jp_test_validation_cart();
            if ($scenario === 'removed') { unset($GLOBALS['jp_cart_products'][41]); }
            if ($scenario === 'private') { $GLOBALS['jp_cart_products'][41]->purchasable = false; }
            if ($scenario === 'outofstock') { $GLOBALS['jp_cart_products'][41]->inStock = false; }
            if ($scenario === 'unmanaged') { $GLOBALS['jp_cart_products'][41]->managed = false; }
            if ($scenario === 'fractional') { $cart->cart_contents['line']['quantity'] = 1.5; }
            if ($scenario === 'negative') { $cart->cart_contents['line']['quantity'] = -1; }
            if ($scenario === 'infinite') { $cart->cart_contents['line']['quantity'] = INF; }
            try { (new CartValidationModule())->finalOrder(jp_test_validation_order()); $test->assertTrue(false, $scenario); } catch (Exception $exception) {
                $test->assertTrue(str_contains($exception->getMessage(), 'Réduisez la quantité'), $scenario);
            }
            $test->assertSame(1, count($cart->cart_contents));
        }
    },
    'final order hook rejects a price change after totals and succeeds after review on a new request' => static function (TestHarness $test): void {
        $cart = jp_test_validation_cart();
        $module = new CartValidationModule();
        $module->refreshPrices($cart);
        $GLOBALS['jp_cart_products'][41]->price = '35';
        try { $module->finalOrder(jp_test_validation_order()); $test->assertTrue(false); } catch (Exception $exception) {
            $test->assertTrue(str_contains($exception->getMessage(), 'Les prix ont été actualisés'));
        }
        $test->assertSame('35.000', $cart->cart_contents['line']['_jp_catalog_price']);
        $test->assertSame(1, $cart->calculations);
        $test->assertSame('35.000', $GLOBALS['jp_cart_wc']->session->data['cart']['line']['_jp_catalog_price']);
        (new CartValidationModule())->finalOrder(jp_test_validation_order());
        $test->assertSame('35', $cart->cart_contents['line']['data']->get_price());
    },
    'Store API exposes validation errors and rejects final stale stock with a conflict response' => static function (TestHarness $test): void {
        $cart = jp_test_validation_cart(12);
        $errors = new WP_Error();
        $module = new CartValidationModule();
        $module->storeCartErrors($errors, $cart);
        $test->assertTrue(isset($errors->errors['jp_cart_validation_0']));
        try { $module->finalStoreOrder(jp_test_validation_order()); $test->assertTrue(false); } catch (Automattic\WooCommerce\StoreApi\Exceptions\RouteException $exception) {
            $test->assertSame(409, $exception->getCode());
            $test->assertSame('jp_cart_changed', $exception->errorCode);
        }
    },
    'native current order reservation is excluded while other reservations still block' => static function (TestHarness $test): void {
        jp_test_validation_cart(5);
        $GLOBALS['jp_cart_held'][41] = [0 => 7, 77 => 6];
        $GLOBALS['jp_cart_wc']->session->data['order_awaiting_payment'] = 77;
        $module = new CartValidationModule();
        $module->checkCart();
        $test->assertSame([], $GLOBALS['jp_cart_notices']);
        $module->finalOrder(jp_test_validation_order(77));
        $test->assertSame([77, 77, 77], $GLOBALS['jp_cart_excluded']);
    },
    'session-load retry does not leave a false error from its own order reservation' => static function (TestHarness $test): void {
        $cart = jp_test_validation_cart(8);
        $GLOBALS['jp_cart_held'][41] = [0 => 8, 77 => 0];
        $GLOBALS['jp_cart_wc']->session->data['order_awaiting_payment'] = 77;
        $module = new CartValidationModule();
        $module->cartLoaded($cart);
        $module->checkCart();
        $module->finalOrder(jp_test_validation_order(77));
        $test->assertSame([], $GLOBALS['jp_cart_notices']);
        $test->assertSame([77, 77, 77, 77], $GLOBALS['jp_cart_excluded']);
    },
    'classic and Store API retries select their own session order when both keys exist' => static function (TestHarness $test): void {
        foreach (['shortcode' => 77, 'store-api' => 88] as $context => $expected) {
            $cart = jp_test_validation_cart(8);
            $cart->cart_context = $context;
            $GLOBALS['jp_cart_wc']->session->data = ['order_awaiting_payment' => 77, 'store_api_draft_order' => 88];
            $GLOBALS['jp_cart_held'][41] = [0 => 8, $expected => 0];
            $module = new CartValidationModule();
            $module->cartLoaded($cart);
            $module->checkCart();
            $test->assertSame([], $GLOBALS['jp_cart_notices']);
            $test->assertSame([$expected, $expected], $GLOBALS['jp_cart_excluded']);
        }
    },
    'actual final order quantities are checked independently of the cart snapshot' => static function (TestHarness $test): void {
        jp_test_validation_cart(1);
        $order = jp_test_validation_order();
        $order->rows = [['product_id' => 41, 'variation_id' => 0, 'quantity' => 12]];
        try { (new CartValidationModule())->finalOrder($order); $test->assertTrue(false); } catch (Exception $exception) {
            $test->assertTrue(str_contains($exception->getMessage(), 'Réduisez la quantité'));
        }
        $order->rows = [];
        try { (new CartValidationModule())->finalOrder($order); $test->assertTrue(false); } catch (Exception $exception) {
            $test->assertTrue(str_contains($exception->getMessage(), 'articles de votre commande'));
        }
    },
    'final checks invalidate stale product metadata and inherited parent stock' => static function (TestHarness $test): void {
        $cart = jp_test_validation_cart();
        $module = new CartValidationModule();
        $module->refreshPrices($cart);
        $test->assertSame('32.900', JouvencePara\Core\Cart\wc_get_product(41)->get_price());
        $GLOBALS['jp_cart_products'][41]->price = '40';
        $test->assertSame('32.900', JouvencePara\Core\Cart\wc_get_product(41)->get_price(), 'Model the native request metadata cache');
        try { $module->finalOrder(jp_test_validation_order()); $test->assertTrue(false); } catch (Exception $exception) {
            $test->assertTrue(str_contains($exception->getMessage(), 'Les prix ont été actualisés'));
        }
        $cart = jp_test_validation_cart();
        $variation = jp_test_validation_product(['id' => 42, 'type' => 'variation', 'stock_quantity' => null]);
        $variation->ownerId = 41;
        $GLOBALS['jp_cart_products'][42] = $variation;
        $cart->cart_contents['line']['variation_id'] = 42;
        $module = new CartValidationModule();
        $module->refreshPrices($cart);
        $GLOBALS['jp_cart_products'][41]->set_stock_quantity(1);
        $test->assertSame(true, JouvencePara\Core\Cart\wc_get_product(42)->is_in_stock(), 'Cached inherited parent data still looks available');
        try { $module->finalOrder(jp_test_validation_order()); $test->assertTrue(false); } catch (Exception $exception) {
            $test->assertTrue(str_contains($exception->getMessage(), 'Réduisez la quantité'));
        }
    },
    'optional native product instance caching cannot hide a final price change' => static function (TestHarness $test): void {
        $cart = jp_test_validation_cart();
        $GLOBALS['jp_cart_instance_enabled'] = true;
        $module = new CartValidationModule();
        $module->refreshPrices($cart);
        $GLOBALS['jp_cart_products'][41]->price = '45';
        $test->assertSame('32.900', JouvencePara\Core\Cart\wc_get_product(41)->get_price());
        try { $module->finalOrder(jp_test_validation_order()); $test->assertTrue(false); } catch (Exception $exception) {
            $test->assertTrue(str_contains($exception->getMessage(), 'Les prix ont été actualisés'));
        }
        $test->assertSame('45.000', $cart->cart_contents['line']['_jp_catalog_price']);
        $GLOBALS['jp_cart_instance_enabled'] = false;
    },
];
