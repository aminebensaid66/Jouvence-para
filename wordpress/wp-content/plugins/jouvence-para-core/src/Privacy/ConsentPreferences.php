<?php

declare(strict_types=1);

namespace JouvencePara\Core\Privacy;

final class ConsentPreferences
{
    public const COOKIE_NAME = 'jp_consent_v1';
    public const VERSION = 1;

    /** @return array{v: int, analytics: bool, marketing: bool}|null */
    public static function decode(string $value): ?array
    {
        $padded = strtr($value, '-_', '+/');
        $padding = strlen($padded) % 4;
        if ($padding > 0) {
            $padded .= str_repeat('=', 4 - $padding);
        }

        $json = base64_decode($padded, true);
        if (! is_string($json)) {
            return null;
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded) || ($decoded['v'] ?? null) !== self::VERSION) {
            return null;
        }
        if (! is_bool($decoded['analytics'] ?? null) || ! is_bool($decoded['marketing'] ?? null)) {
            return null;
        }

        return [
            'v' => self::VERSION,
            'analytics' => $decoded['analytics'],
            'marketing' => $decoded['marketing'],
        ];
    }

    /** @param array{analytics: bool, marketing: bool} $preferences */
    public static function encode(array $preferences): string
    {
        $json = json_encode([
            'v' => self::VERSION,
            'analytics' => $preferences['analytics'],
            'marketing' => $preferences['marketing'],
        ], JSON_UNESCAPED_SLASHES);

        if (! is_string($json)) {
            return '';
        }

        return rtrim(strtr(base64_encode($json), '+/', '-_'), '=');
    }

    public static function allows(string $category, ?string $cookieValue = null): bool
    {
        if ($category === 'essential') {
            return true;
        }
        if (! in_array($category, ['analytics', 'marketing'], true)) {
            return false;
        }

        if ($cookieValue === null) {
            $cookieValue = isset($_COOKIE[self::COOKIE_NAME]) ? (string) $_COOKIE[self::COOKIE_NAME] : '';
        }

        $preferences = self::decode($cookieValue);
        return $preferences !== null && $preferences[$category] === true;
    }
}
