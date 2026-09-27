<?php

declare(strict_types=1);

namespace JouvencePara\Core\I18n;

final class I18nPolicy
{
    public const LAUNCH_LOCALE = 'fr_FR';
    public const BUSINESS_TIMEZONE = 'Africa/Tunis';

    public static function isRtlLocale(string $locale): bool
    {
        $language = strtolower(strtok(str_replace('-', '_', $locale), '_') ?: '');
        return in_array($language, ['ar', 'fa', 'he', 'ur'], true);
    }
}
