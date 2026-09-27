<?php

declare(strict_types=1);

use JouvencePara\Core\Inventory\InventoryPolicy;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Inventory/InventoryPolicy.php';

return [
    'retains one operational unit from online availability' => static function (TestHarness $test): void {
        $test->assertSame(0, InventoryPolicy::onlineAvailable(1));
        $test->assertSame(1, InventoryPolicy::onlineAvailable(2));
        $test->assertSame(9, InventoryPolicy::onlineAvailable(10));
    },
    'rejects purchases into the safety buffer' => static function (TestHarness $test): void {
        $test->assertTrue(InventoryPolicy::canPurchase(5, 4));
        $test->assertTrue(! InventoryPolicy::canPurchase(5, 5));
    },
    'public availability contains no exact quantity' => static function (TestHarness $test): void {
        $test->assertSame('available', InventoryPolicy::publicAvailability(100));
        $test->assertSame('out_of_stock', InventoryPolicy::publicAvailability(1));
    },
];
