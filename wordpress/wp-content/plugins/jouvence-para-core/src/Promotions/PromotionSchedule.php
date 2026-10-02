<?php

declare(strict_types=1);

namespace JouvencePara\Core\Promotions;

use DateTimeImmutable;
use DateTimeZone;
use Throwable;

final class PromotionSchedule
{
    public const TIMEZONE = 'Africa/Tunis';

    /**
     * Parse an admin's local start time and return its UTC timestamp.
     *
     * @throws \InvalidArgumentException When a non-empty value is not a real local date and time.
     */
    public static function startTimestamp(string $value): ?int
    {
        if ($value === '') {
            return null;
        }

        try {
            $timezone = new DateTimeZone(self::TIMEZONE);
            $start = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value, $timezone);
            $errors = DateTimeImmutable::getLastErrors();
        } catch (Throwable $error) {
            throw new \InvalidArgumentException('Invalid promotion start time.', 0, $error);
        }

        if (! $start instanceof DateTimeImmutable
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $start->format('Y-m-d\TH:i') !== $value) {
            throw new \InvalidArgumentException('Invalid promotion start time.');
        }

        return $start->setTimezone(new DateTimeZone('UTC'))->getTimestamp();
    }

    public static function hasStarted(?int $startTimestamp, int $nowTimestamp): bool
    {
        return $startTimestamp === null || $nowTimestamp >= $startTimestamp;
    }
}
