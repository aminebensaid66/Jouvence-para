<?php

declare(strict_types=1);

use JouvencePara\Core\Geography\GeographyNode;
use JouvencePara\Core\Geography\TunisiaGeography;
use JouvencePara\Core\Shipping\ShippingMatrix;
use JouvencePara\Core\Shipping\ShippingPolicy;
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Geography/GeographyNode.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Geography/TunisiaGeography.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Shipping/ShippingPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Shipping/ShippingMatrix.php';

return [
    'charges seven dinars below threshold and zero at threshold' => static function (TestHarness $test): void {
        $test->assertSame(7000, ShippingPolicy::deliveryFeeMilli(199999));
        $test->assertSame(0, ShippingPolicy::deliveryFeeMilli(200000));
        $test->assertSame('7.000', ShippingPolicy::tnd(7000));
    },
    'maps every approved governorate to First Delivery manual processing' => static function (TestHarness $test): void {
        $matrix = ShippingMatrix::nationwide();
        $test->assertSame(24, count($matrix));
        $test->assertSame('First Delivery', $matrix['tn-medenine']['carrier']);
        $test->assertSame(true, $matrix['tn-medenine']['manual']);
    },
    'rejects unsupported geography identifiers' => static function (TestHarness $test): void {
        $test->assertTrue(! ShippingMatrix::supports('outside-tunisia'));
    },
];
