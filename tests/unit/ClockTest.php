<?php

declare(strict_types=1);

use JouvencePara\Core\Support\Clock;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Support/Clock.php';

return [
    'nowUtc returns a UTC instant' => static function (TestHarness $test): void {
        $test->assertSame('UTC', Clock::nowUtc()->getTimezone()->getName());
    },
    'business time uses Africa Tunis' => static function (TestHarness $test): void {
        $utc = new DateTimeImmutable('2026-09-13 12:00:00', new DateTimeZone('UTC'));
        $business = Clock::toBusinessTime($utc);

        $test->assertSame('Africa/Tunis', $business->getTimezone()->getName());
        $test->assertSame('2026-09-13 13:00:00', $business->format('Y-m-d H:i:s'));
    },
];
