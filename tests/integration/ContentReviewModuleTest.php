<?php

declare(strict_types=1);

use JouvencePara\Core\Content\ContentReviewModule;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Content/ContentClaimPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Content/ContentReviewModule.php';

return [
    'registers review save and publication enforcement hooks' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        (new ContentReviewModule())->register();
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['woocommerce_admin_process_product_object']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['woocommerce_before_product_object_save']));
    },
];
