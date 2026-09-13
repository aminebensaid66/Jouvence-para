<?php

declare(strict_types=1);

namespace JouvencePara\Core\Support;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

final class Clock
{
    public const BUSINESS_TIMEZONE = 'Africa/Tunis';

    public static function nowUtc(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

    public static function businessTimezone(): DateTimeZone
    {
        return new DateTimeZone(self::BUSINESS_TIMEZONE);
    }

    public static function toBusinessTime(DateTimeInterface $instant): DateTimeImmutable
    {
        return DateTimeImmutable::createFromInterface($instant)->setTimezone(self::businessTimezone());
    }
}
