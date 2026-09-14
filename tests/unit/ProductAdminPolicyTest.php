<?php

declare(strict_types=1);

use JouvencePara\Core\Catalog\ProductAdminPolicy;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/ProductAdminPolicy.php';

return [
    'defines only internal traceability fields' => static function (TestHarness $test): void {
        $fields = ProductAdminPolicy::internalFields();
        $test->assertTrue(isset($fields[ProductAdminPolicy::SUPPLIER_REFERENCE]));
        $test->assertTrue(isset($fields[ProductAdminPolicy::SOURCE_REFERENCE]));
        $test->assertSame(2, count($fields));
    },
    'sanitizes and bounds internal references' => static function (TestHarness $test): void {
        $test->assertSame('ABC-123', ProductAdminPolicy::sanitizeReference(' <b>ABC-123</b> '));
        $test->assertSame(191, strlen(ProductAdminPolicy::sanitizeReference(str_repeat('x', 250))));
    },
];
