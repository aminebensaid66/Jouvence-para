<?php

declare(strict_types=1);

use JouvencePara\Core\Promotions\PromotionSchedule;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Promotions/PromotionSchedule.php';

return [
    'treats a blank start as immediately available' => static function (TestHarness $test): void {
        $test->assertSame(null, PromotionSchedule::startTimestamp(''));
        $test->assertTrue(PromotionSchedule::hasStarted(null, 1));
    },
    'stores Africa Tunis local start as UTC and enforces the exact boundary' => static function (TestHarness $test): void {
        $start = PromotionSchedule::startTimestamp('2026-10-02T12:30');
        $test->assertSame(strtotime('2026-10-02 11:30:00 UTC'), $start);
        $test->assertTrue(! PromotionSchedule::hasStarted($start, $start - 1));
        $test->assertTrue(PromotionSchedule::hasStarted($start, $start));
    },
    'rejects malformed and normalized impossible local dates' => static function (TestHarness $test): void {
        foreach (['2026-02-30T12:00', '2026-10-02 12:00', '2026-10-02T12:00:00'] as $value) {
            $rejected = false;
            try {
                PromotionSchedule::startTimestamp($value);
            } catch (InvalidArgumentException) {
                $rejected = true;
            }
            $test->assertTrue($rejected);
        }
    },
];
