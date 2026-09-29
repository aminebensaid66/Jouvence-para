<?php

declare(strict_types=1);

use JouvencePara\Core\Discovery\DiscoveryRoutingModule;
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/SortPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/DiscoveryUrlPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/DiscoveryRoutingModule.php';
require_once __DIR__ . '/seo-fixture.php';

return [
    'registers sorting pagination and robots while SEO owns archive canonicals' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        (new DiscoveryRoutingModule())->register();
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['woocommerce_get_catalog_ordering_args']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['woocommerce_pagination_args']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['wp_robots']));
        $test->assertTrue(! isset($GLOBALS['jp_test_hooks']['action']['wp_head']));
    },
    'bestseller sort uses sales with deterministic ID tie break' => static function (TestHarness $test): void {
        $args = (new DiscoveryRoutingModule())->orderingArgs([], 'bestseller', '');
        $test->assertSame('total_sales', $args['meta_key']);
        $test->assertSame('meta_value_num ID', $args['orderby']);
    },
    'crawl variants cannot retain index or override a stricter nofollow directive' => static function (TestHarness $test): void {
        jp_test_seo_page(['shop' => true]);
        $_GET = ['jp_filter_brand' => ''];
        try {
            $module = new DiscoveryRoutingModule();
            $robots = $module->robots(['index' => true, 'max-image-preview' => 'large']);
            $test->assertTrue(! isset($robots['index']));
            $test->assertSame(true, $robots['noindex']);
            $test->assertSame(true, $robots['follow']);
            $test->assertSame('large', $robots['max-image-preview']);
            $stricter = $module->robots(['nofollow' => true, 'follow' => true]);
            $test->assertSame(true, $stricter['nofollow']);
            $test->assertTrue(! isset($stricter['follow']));
            $_GET = ['paged' => '2'];
            $test->assertSame([], $module->robots([]));
            jp_test_seo_page(['post' => new WP_Post(['post_type' => 'product'])]);
            $_GET = ['min_price' => '10'];
            $test->assertSame([], $module->robots([]), 'An unrelated page does not inherit archive crawl rules');
        } finally {
            $_GET = [];
        }
    },
];
