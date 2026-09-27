<?php

declare(strict_types=1);

use JouvencePara\Core\Discovery\DiscoveryUrlPolicy;
use JouvencePara\Core\Discovery\FilterState;
use JouvencePara\Core\Discovery\SortPolicy;
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/FilterState.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/SortPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/DiscoveryUrlPolicy.php';

return [
    'accepts only supported deterministic sort keys' => static function (TestHarness $test): void {
        $test->assertSame('bestseller', SortPolicy::normalize('bestseller'));
        $test->assertSame('relevance', SortPolicy::normalize('random'));
        $test->assertSame(5, count(SortPolicy::options()));
    },
    'marks filters and nondefault sorting as crawl variants' => static function (TestHarness $test): void {
        $test->assertTrue(DiscoveryUrlPolicy::hasVariantState(['jp_filter_brand' => ['avene']]));
        $test->assertTrue(DiscoveryUrlPolicy::hasVariantState(['orderby' => 'price']));
        $test->assertTrue(! DiscoveryUrlPolicy::hasVariantState(['orderby' => 'relevance']));
    },
    'pagination carries only sanitized discovery state' => static function (TestHarness $test): void {
        $args = DiscoveryUrlPolicy::paginationArgs(['jp_filter_brand' => ['avene', '../../x'], 'orderby' => 'newest', 'evil' => 'x']);
        $test->assertSame(['avene', 'x'], $args['jp_filter_brand']);
        $test->assertSame('newest', $args['orderby']);
        $test->assertTrue(! isset($args['evil']));
        $test->assertSame('relevance', DiscoveryUrlPolicy::paginationArgs(['orderby' => ['price']])['orderby']);
    },
];
