<?php

declare(strict_types=1);

use JouvencePara\Core\Jobs\RetryPolicy;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Jobs/RetryPolicy.php';

return [
    'uses bounded exponential delays and stops after the maximum attempt' => static function (TestHarness $test): void {
        $policy = new RetryPolicy(5, 60, 180, 2);

        $test->assertSame(60, $policy->delayAfterFailure(1));
        $test->assertSame(120, $policy->delayAfterFailure(2));
        $test->assertSame(180, $policy->delayAfterFailure(3));
        $test->assertSame(180, $policy->delayAfterFailure(4));
        $test->assertSame(null, $policy->delayAfterFailure(5));
    },
    'rejects a retry policy with an invalid range' => static function (TestHarness $test): void {
        try {
            new RetryPolicy(0, 60, 3600, 2);
        } catch (InvalidArgumentException) {
            $test->assertTrue(true);
            return;
        }

        $test->assertTrue(false, 'Expected an invalid retry policy to throw.');
    },
];
