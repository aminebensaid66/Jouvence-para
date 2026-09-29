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
        foreach ([['jp_filter_brand' => ''], ['min_price' => '10'], ['filter_skin' => 'dry'],
            ['query_type_skin' => 'or'], ['orderby' => 'invalid'], ['orderby' => ['price']]] as $request) {
            $test->assertTrue(DiscoveryUrlPolicy::hasVariantState($request));
        }
        $test->assertTrue(! DiscoveryUrlPolicy::hasVariantState(['paged' => '2', 'utm_source' => 'mail']));
    },
    'pagination carries only sanitized discovery state' => static function (TestHarness $test): void {
        $args = DiscoveryUrlPolicy::paginationArgs(['jp_filter_brand' => ['avene', '../../x'], 'orderby' => 'newest', 'evil' => 'x']);
        $test->assertSame(['avene', 'x'], $args['jp_filter_brand']);
        $test->assertSame('newest', $args['orderby']);
        $test->assertTrue(! isset($args['evil']));
        $test->assertSame('relevance', DiscoveryUrlPolicy::paginationArgs(['orderby' => ['price']])['orderby']);
    },
];
