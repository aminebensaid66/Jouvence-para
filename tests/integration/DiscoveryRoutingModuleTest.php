<?php

declare(strict_types=1);

use JouvencePara\Core\Discovery\DiscoveryRoutingModule;
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/SortPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/DiscoveryUrlPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/DiscoveryRoutingModule.php';

return [
    'registers sorting pagination robots and canonical hooks' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        (new DiscoveryRoutingModule())->register();
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['woocommerce_get_catalog_ordering_args']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['woocommerce_pagination_args']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['wp_robots']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['wp_head']));
    },
    'bestseller sort uses sales with deterministic ID tie break' => static function (TestHarness $test): void {
        $args = (new DiscoveryRoutingModule())->orderingArgs([], 'bestseller', '');
        $test->assertSame('total_sales', $args['meta_key']);
        $test->assertSame('meta_value_num ID', $args['orderby']);
    },
];
