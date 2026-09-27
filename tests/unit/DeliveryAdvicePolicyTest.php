<?php

declare(strict_types=1);

use JouvencePara\Core\Support\DeliveryAdvicePolicy;
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Support/DeliveryAdvicePolicy.php';

return [
    'contains only approved launch delivery values' => static function (TestHarness $test): void {
        $rules = DeliveryAdvicePolicy::publicRules();
        $test->assertSame(7000, $rules['delivery_fee_milli']);
        $test->assertSame(200000, $rules['free_threshold_milli']);
        $test->assertSame(2, $rules['delivery_working_days']);
        $test->assertSame('First Delivery', $rules['carrier']);
        $test->assertSame(true, $rules['store_pickup_free']);
    },
];
