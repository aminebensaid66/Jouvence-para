<?php

declare(strict_types=1);

use JouvencePara\Core\Discovery\FilterState;
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/FilterState.php';

return [
    'sanitizes deduplicates and bounds request filter values' => static function (TestHarness $test): void {
        $state = FilterState::fromRequest(['jp_filter_brand' => ['avene', 'avene', '../bad', 'cerave']]);
        $test->assertSame(['avene', 'bad', 'cerave'], $state->group('brand'));
    },
    'uses OR inside a group and AND between groups' => static function (TestHarness $test): void {
        $state = FilterState::fromRequest(['jp_filter_brand' => ['avene', 'cerave'], 'jp_filter_spf' => ['50']]);
        $query = $state->taxQuery();
        $test->assertSame('AND', $query['relation']);
        $test->assertSame('IN', $query[0]['operator']);
        $test->assertSame(['avene', 'cerave'], $query[0]['terms']);
        $test->assertSame('IN', $query[1]['operator']);
    },
    'can exclude one group for consistent facet counts' => static function (TestHarness $test): void {
        $state = FilterState::fromRequest(['jp_filter_brand' => ['avene'], 'jp_filter_spf' => ['50']]);
        $query = $state->taxQuery('brand');
        $test->assertSame('pa_spf', $query[0]['taxonomy']);
        $test->assertSame(1, count(array_filter(array_keys($query), 'is_int')));
    },
];
