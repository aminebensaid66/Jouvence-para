<?php

declare(strict_types=1);

namespace JouvencePara\Core\Customers;

final class CommunicationPreferences
{
    public const META_KEY = '_jp_communication_preferences';

    public static function valid(mixed $record): bool
    {
        return is_array($record) && count($record) === 3 && ($record['version'] ?? null) === 1
            && is_bool($record['email_marketing'] ?? null) && is_string($record['updated_at'] ?? null)
            && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/D', $record['updated_at']) === 1;
    }

    public static function emailEnabled(mixed $record): bool
    {
        return self::valid($record) && $record['email_marketing'];
    }
}
