<?php

declare(strict_types=1);

use JouvencePara\Core\Observability\MetricsSummary;
use JouvencePara\Core\Observability\Redactor;
use JouvencePara\Core\Observability\DailyMetrics;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Observability/MetricsSummary.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Observability/Redactor.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Observability/DailyMetrics.php';

return [
    'redacts sensitive context recursively' => static function (TestHarness $test): void {
        $result = Redactor::context([
            'token' => 'abc',
            'nested' => ['password' => 'secret', 'safe' => 'value'],
        ]);
        $test->assertSame('[redacted]', $result['token']);
        $test->assertSame('[redacted]', $result['nested']['password']);
        $test->assertSame('value', $result['nested']['safe']);
    },
    'redacts email addresses and bearer values in text' => static function (TestHarness $test): void {
        $result = Redactor::text('user@example.com Authorization: Bearer abc.def');
        $test->assertTrue(! str_contains($result, 'user@example.com'));
        $test->assertTrue(! str_contains($result, 'abc.def'));
    },
    'calculates request health metrics' => static function (TestHarness $test): void {
        $metrics = ['requests' => 20, 'server_errors' => 2, 'total_ms' => 4000.0, 'max_ms' => 900.0];
        $test->assertSame(10.0, MetricsSummary::serverErrorRate($metrics));
        $test->assertSame(200.0, MetricsSummary::averageMs($metrics));
    },
    'bounds redacted audit text without splitting Unicode or accepting invalid UTF-8' => static function (TestHarness $test): void {
        $test->assertSame('a' . str_repeat('é', 499), Redactor::text('a' . str_repeat('é', 600)));
        $test->assertSame(str_repeat('🙂', 250), Redactor::text(str_repeat('🙂', 400)));
        $test->assertSame('', Redactor::text("\xFF"));
    },
    'samples successful requests but always records server errors' => static function (TestHarness $test): void {
        $test->assertSame(10, DailyMetrics::sampleWeight(200, 1));
        $test->assertSame(0, DailyMetrics::sampleWeight(200, 2));
        $test->assertSame(1, DailyMetrics::sampleWeight(500, 2));
    },
];
