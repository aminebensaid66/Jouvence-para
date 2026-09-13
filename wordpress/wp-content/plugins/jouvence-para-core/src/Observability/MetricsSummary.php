<?php

declare(strict_types=1);

namespace JouvencePara\Core\Observability;

final class MetricsSummary
{
    /** @param array{requests?: int, server_errors?: int, total_ms?: float, max_ms?: float} $metrics */
    public static function normalize(array $metrics): array
    {
        return [
            'requests' => max(0, (int) ($metrics['requests'] ?? 0)),
            'server_errors' => max(0, (int) ($metrics['server_errors'] ?? 0)),
            'total_ms' => max(0.0, (float) ($metrics['total_ms'] ?? 0.0)),
            'max_ms' => max(0.0, (float) ($metrics['max_ms'] ?? 0.0)),
        ];
    }

    /** @param array{requests?: int, server_errors?: int, total_ms?: float, max_ms?: float} $metrics */
    public static function serverErrorRate(array $metrics): float
    {
        $normalized = self::normalize($metrics);
        if ($normalized['requests'] === 0) {
            return 0.0;
        }

        return ($normalized['server_errors'] / $normalized['requests']) * 100;
    }

    /** @param array{requests?: int, server_errors?: int, total_ms?: float, max_ms?: float} $metrics */
    public static function averageMs(array $metrics): float
    {
        $normalized = self::normalize($metrics);
        if ($normalized['requests'] === 0) {
            return 0.0;
        }

        return $normalized['total_ms'] / $normalized['requests'];
    }
}
