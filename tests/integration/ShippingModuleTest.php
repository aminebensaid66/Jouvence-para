<?php

declare(strict_types=1);

if (! class_exists('WC_Shipping_Method')) {
    class WC_Shipping_Method
    {
        public string $id = ''; public int $instance_id = 0; public string $method_title = ''; public string $method_description = '';
        public array $supports = []; public string $enabled = 'yes'; public string $title = ''; public array $instance_form_fields = [];
        public function init_settings(): void {} public function get_option(string $key, mixed $default = ''): mixed { return $default; }
        public function get_rate_id(): string { return $this->id . ':' . $this->instance_id; }
        public function add_rate(array $rate): void { $GLOBALS['jp_test_rates'][] = $rate; }
    }
}
if (! function_exists('absint')) { function absint(mixed $value): int { return abs((int) $value); } }
if (! function_exists('__')) { function __(string $text, string $domain = ''): string { return $text; } }

use JouvencePara\Core\Shipping\ShippingModule;
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Shipping/ShippingPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Shipping/ShippingMatrix.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Shipping/FirstDeliveryShippingMethod.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Shipping/StorePickupShippingMethod.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Shipping/ShippingModule.php';

return [
    'registers First Delivery and store pickup methods' => static function (TestHarness $test): void {
        $methods = (new ShippingModule())->methods([]);
        $test->assertTrue(isset($methods['jp_first_delivery']));
        $test->assertTrue(isset($methods['jp_store_pickup']));
    },
    'First Delivery exposes approved estimate and deterministic post-discount rates' => static function (TestHarness $test): void {
        $method = new \JouvencePara\Core\Shipping\FirstDeliveryShippingMethod();
        $GLOBALS['jp_test_rates'] = [];
        $method->calculate_shipping([
            'destination' => ['country' => 'TN'],
            'contents' => ['item' => ['line_total' => 190.0, 'line_tax' => 5.0]],
        ]);
        $test->assertSame(1, count($GLOBALS['jp_test_rates']));
        $test->assertSame('First Delivery — 2 jours ouvrés', $GLOBALS['jp_test_rates'][0]['label']);
        $test->assertSame('7.000', $GLOBALS['jp_test_rates'][0]['cost']);

        $GLOBALS['jp_test_rates'] = [];
        $method->calculate_shipping([
            'destination' => ['country' => 'TN'],
            'contents' => ['item' => ['line_total' => 200.0, 'line_tax' => 0.0]],
        ]);
        $test->assertSame('0.000', $GLOBALS['jp_test_rates'][0]['cost']);

        $GLOBALS['jp_test_rates'] = [];
        $method->calculate_shipping(['destination' => ['country' => 'FR'], 'contents' => []]);
        $test->assertSame([], $GLOBALS['jp_test_rates']);
        unset($GLOBALS['jp_test_rates']);
    },
];
