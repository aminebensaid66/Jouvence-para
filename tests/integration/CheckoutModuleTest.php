<?php

declare(strict_types=1);

use JouvencePara\Core\Checkout\CheckoutModule;

require_once __DIR__ . '/checkout-fixture.php';
foreach (['TunisianPhone', 'CheckoutFields', 'CheckoutModule'] as $class) {
    require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Checkout/' . $class . '.php';
}

return [
    'registers native classic and Store API checkout validation contracts' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        (new CheckoutModule())->register();
        foreach (['woocommerce_states', 'woocommerce_countries_base_country', 'woocommerce_get_country_locale', 'woocommerce_checkout_fields', 'woocommerce_checkout_posted_data', 'pre_option_woocommerce_checkout_phone_field'] as $hook) {
            $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter'][$hook]));
        }
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['woocommerce_after_checkout_validation']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['woocommerce_store_api_checkout_update_order_from_request']));
    },
    'requires native fields and exact boundaries while retaining unrelated field definitions' => static function (TestHarness $test): void {
        $module = new CheckoutModule();
        $fields = $module->fields(['billing' => ['billing_first_name' => ['label' => 'Prénom']], 'account' => ['custom' => ['required' => false]]]);
        $test->assertSame('Prénom', $fields['billing']['billing_first_name']['label']);
        $test->assertSame(80, $fields['billing']['billing_first_name']['custom_attributes']['maxlength']);
        $test->assertSame(180, $fields['shipping']['shipping_address_1']['custom_attributes']['maxlength']);
        $test->assertSame(false, $fields['billing']['billing_postcode']['required']);
        $test->assertSame(true, $fields['billing']['billing_phone']['required']);
        $test->assertSame(500, $fields['order']['order_comments']['custom_attributes']['maxlength']);
        $test->assertSame(['required' => false], $fields['account']['custom']);
        $test->assertSame(24, count($module->states(['FR' => ['AA' => 'Other']])['TN']));
        $test->assertSame('TN', $module->baseCountry('US'));
        $test->assertSame(true, $module->locale([])['TN']['state']['required']);
    },
    'valid guest input needs no account and normalization preserves entered fields' => static function (TestHarness $test): void {
        $module = new CheckoutModule(); $data = jp_checkout_data();
        $normalized = $module->normalize($data); $errors = new WP_Error();
        $module->validate($normalized, $errors);
        $test->assertSame([], $errors->errors);
        $test->assertSame('+21629302202', $normalized['billing_phone']);
        unset($normalized['billing_phone'], $data['billing_phone']);
        $test->assertSame($data, $normalized);
    },
    'invalid field errors preserve valid input and reject overflow notes and address' => static function (TestHarness $test): void {
        $module = new CheckoutModule(); $data = jp_checkout_data();
        $data['billing_first_name'] = str_repeat('é', 81); $data['billing_phone'] = 'bad';
        $data['billing_address_1'] = str_repeat('a', 181); $data['order_comments'] = str_repeat('é', 501);
        $original = $data; $errors = new WP_Error(); $module->validate($data, $errors);
        foreach (['billing_first_name', 'billing_phone', 'billing_address_1', 'order_comments'] as $key) {
            $test->assertTrue(isset($errors->errors['jp_' . $key]));
        }
        $test->assertSame($original, $data);
        $test->assertSame($original, $module->normalize($data));
    },
    'different shipping address receives independent required-field validation' => static function (TestHarness $test): void {
        $data = jp_checkout_data(); $data['ship_to_different_address'] = true; $errors = new WP_Error();
        (new CheckoutModule())->validate($data, $errors);
        foreach (['first_name', 'last_name', 'address_1', 'city', 'state', 'country'] as $key) {
            $test->assertTrue(isset($errors->errors['jp_shipping_' . $key]));
        }
        $test->assertTrue(! isset($errors->errors['jp_shipping_email']));
    },
    'Store API validates final submission before save and payment but permits incomplete PATCH' => static function (TestHarness $test): void {
        $GLOBALS['jp_checkout_wc'] = (object) ['cart' => new class { public function needs_shipping(): bool { return true; } }];
        $order = new class extends WC_Order {
            public array $fields = [];
            public function __call(string $name, array $args): mixed {
                if (str_starts_with($name, 'set_')) { $this->fields[substr($name, 4)] = $args[0]; return null; }
                return $this->fields[substr($name, 4)] ?? '';
            }
        };
        $module = new CheckoutModule(); $module->storeOrder($order, jp_checkout_request('PATCH'));
        try { $module->storeOrder($order, jp_checkout_request('POST')); $test->assertTrue(false); }
        catch (\Automattic\WooCommerce\StoreApi\Exceptions\RouteException $error) { $test->assertSame(400, $error->getCode()); }
        foreach (jp_checkout_data() as $key => $value) { $order->fields[$key] = $value; }
        foreach (['first_name', 'last_name', 'address_1', 'city', 'state', 'country'] as $key) { $order->fields['shipping_' . $key] = $order->fields['billing_' . $key]; }
        $module->storeOrder($order, jp_checkout_request('POST'));
        $test->assertSame('+21629302202', $order->fields['billing_phone']);
        $test->assertSame(0, $order->saves);
    },
];
