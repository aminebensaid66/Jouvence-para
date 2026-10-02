<?php

declare(strict_types=1);

use JouvencePara\Core\Analytics\AnalyticsModule;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Analytics/AnalyticsEvent.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Analytics/AnalyticsModule.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Privacy/ConsentPreferences.php';
require_once __DIR__ . '/analytics-fixture.php';

return [
    'registers only consent-aware frontend and native order observation hooks' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        (new AnalyticsModule())->register();
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['wp_enqueue_scripts']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['woocommerce_thankyou']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['woocommerce_add_to_cart']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['woocommerce_cart_item_removed']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['woocommerce_applied_coupon']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['woocommerce_coupon_error']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['woocommerce_registration_redirect']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['wp_print_footer_scripts']));
    },
    'analytics script waits for the consent API before emitting configured page context' => static function (TestHarness $test): void {
        $GLOBALS['jp_analytics_enqueued_script'] = [];
        (new AnalyticsModule())->enqueue();
        $test->assertSame('jouvence-para-analytics', $GLOBALS['jp_analytics_enqueued_script'][0] ?? null);
        $test->assertTrue(in_array('jouvence-para-cookie-consent', $GLOBALS['jp_analytics_enqueued_script'][3] ?? [], true));
        unset($GLOBALS['jp_analytics_enqueued_script']);
    },
    'purchase data requires the matching guest order key and emits an opaque identifier' => static function (TestHarness $test): void {
        $order = new class extends WC_Order {
            public function get_customer_id(): int { return 0; }
            public function get_order_key(): string { return 'order-key-fixture'; }
            public function get_status(): string { return 'processing'; }
            public function get_items(string $type = ''): array { return []; }
            public function get_total(): string { return '20.00'; }
            public function get_currency(): string { return 'TND'; }
            public function get_coupon_codes(): array { return []; }
            public function get_id(): int { return 4321; }
        };
        $GLOBALS['jp_analytics_orders'][4321] = $order;
        $module = new AnalyticsModule();
        unset($_GET['key']);
        $module->capturePurchase(4321);
        $property = new ReflectionProperty(AnalyticsModule::class, 'purchase');
        $test->assertSame(null, $property->getValue($module));
        $_GET['key'] = 'wrong-order-key';
        $module->capturePurchase(4321);
        $test->assertSame(null, $property->getValue($module));
        $_GET['key'] = 'order-key-fixture';
        $module->capturePurchase(4321);
        $purchase = $property->getValue($module);
        $test->assertSame('purchase', $purchase['event']);
        $test->assertSame('TND', $purchase['currency']);
        $test->assertTrue($purchase['purchase_id'] !== '4321');
        unset($_GET['key'], $GLOBALS['jp_analytics_orders'][4321]);
    },
    'classic add remove and account registration events queue only with analytics consent' => static function (TestHarness $test): void {
        $session = new class {
            public array $values = [];
            public function get(string $key, mixed $default = null): mixed { return $this->values[$key] ?? $default; }
            public function set(string $key, mixed $value): void { $this->values[$key] = $value; }
        };
        $GLOBALS['jp_analytics_wc'] = (object) ['session' => $session];
        $cookie = JouvencePara\Core\Privacy\ConsentPreferences::COOKIE_NAME;
        $old = $_COOKIE[$cookie] ?? null;
        $_COOKIE[$cookie] = JouvencePara\Core\Privacy\ConsentPreferences::encode(['analytics' => false, 'marketing' => false]);
        $module = new AnalyticsModule();
        $module->captureAddToCart('key', 1, 1, 0, [], []);
        $test->assertSame([], $session->values);
        $_COOKIE[$cookie] = JouvencePara\Core\Privacy\ConsentPreferences::encode(['analytics' => true, 'marketing' => false]);
        $module->captureAddToCart('key', 1, 1, 0, [], []);
        $module->captureRemoveFromCart('key', new stdClass());
        $module->captureCouponApplied('private-coupon-code');
        $test->assertSame('Coupon failed', $module->captureCouponRejected('Coupon failed', 1, new stdClass()));
        $test->assertSame('/account/', $module->captureRegistration('/account/'));
        $property = new ReflectionMethod(AnalyticsModule::class, 'drainPendingEvents');
        $property->setAccessible(true);
        $test->assertSame([
            'add_to_cart' => 1,
            'remove_from_cart' => 1,
            'coupon_applied' => 1,
            'coupon_rejected' => 1,
            'account_registration' => 1,
        ], $property->invoke($module));
        $test->assertSame([], $property->invoke($module));
        if ($old === null) unset($_COOKIE[$cookie]); else $_COOKIE[$cookie] = $old;
        unset($GLOBALS['jp_analytics_wc']);
    },
];
