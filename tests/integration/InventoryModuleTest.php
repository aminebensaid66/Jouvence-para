<?php

declare(strict_types=1);

use JouvencePara\Core\Inventory\InventoryModule;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Inventory/InventoryPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Inventory/InventoryModule.php';

return [
    'registers stock and purchase validation hooks' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        (new InventoryModule())->register();
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['woocommerce_product_is_in_stock']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['woocommerce_add_to_cart_validation']));
    },
    'forces backorders off' => static function (TestHarness $test): void {
        $test->assertSame('no', (new InventoryModule())->disableBackorders('notify'));
    },
];
