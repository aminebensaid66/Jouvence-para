<?php

declare(strict_types=1);

namespace JouvencePara\Core\Audit;

use JouvencePara\Core\Observability\Redactor;

final class AuditEvent
{
    public static function action(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9_.-]+/', '_', $value) ?? '';
        return substr(trim($value, '_'), 0, 100);
    }

    public static function objectType(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9_.-]+/', '_', $value) ?? '';
        return substr(trim($value, '_'), 0, 80);
    }

    public static function objectId(string|int $value): string
    {
        return substr((string) $value, 0, 191);
    }

    public static function snapshot(mixed $value): mixed
    {
        return Redactor::context($value);
    }
}
