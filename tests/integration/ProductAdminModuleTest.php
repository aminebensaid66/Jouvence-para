<?php

declare(strict_types=1);

use JouvencePara\Core\Catalog\ProductAdminModule;
use JouvencePara\Core\Catalog\ProductAdminPolicy;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/ProductAdminPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/ProductAdminModule.php';

return [
    'registers private traceability metadata' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_registered_meta'] = [];
        (new ProductAdminModule())->registerMeta();
        foreach (array_keys(ProductAdminPolicy::internalFields()) as $key) {
            $test->assertTrue(isset($GLOBALS['jp_test_registered_meta']['product'][$key]));
            $test->assertSame(false, $GLOBALS['jp_test_registered_meta']['product'][$key]['show_in_rest']);
        }
    },
    'registers the product admin lifecycle hooks' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        (new ProductAdminModule())->register();
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['add_meta_boxes_product']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['woocommerce_admin_process_product_object']));
    },
];
