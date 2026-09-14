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
];
