<?php

declare(strict_types=1);

namespace JouvencePara\Core\Observability;

final class DailyMetrics
{
    private const OPTION_PREFIX = 'jp_observability_';
    private const SUCCESS_SAMPLE_RATE = 10;

    public function record(int $statusCode, float $durationMs): void
    {
        $weight = self::sampleWeight($statusCode, random_int(1, self::SUCCESS_SAMPLE_RATE));
        if ($weight === 0) {
            return;
        }

        $option = self::optionName();
        $lock = $option . '_lock';
        if (! add_option($lock, time(), '', false)) {
            return;
        }

        try {
            $metrics = MetricsSummary::normalize((array) get_option($option, []));
            $metrics['requests'] += $weight;
            if ($statusCode >= 500) {
                $metrics['server_errors']++;
            }
            $metrics['total_ms'] += max(0.0, $durationMs) * $weight;
            $metrics['max_ms'] = max($metrics['max_ms'], max(0.0, $durationMs));

            update_option($option, $metrics, false);
        } finally {
            delete_option($lock);
        }
    }

    /** @return array{requests: int, server_errors: int, total_ms: float, max_ms: float} */
    public function current(): array
    {
        return MetricsSummary::normalize((array) get_option(self::optionName(), []));
    }

    public static function optionName(?int $timestamp = null): string
    {
        $timestamp ??= time();
        return self::OPTION_PREFIX . gmdate('Ymd', $timestamp);
    }

    public static function sampleWeight(int $statusCode, int $roll): int
    {
        if ($statusCode >= 500) {
            return 1;
        }

        return $roll === 1 ? self::SUCCESS_SAMPLE_RATE : 0;
    }
}
