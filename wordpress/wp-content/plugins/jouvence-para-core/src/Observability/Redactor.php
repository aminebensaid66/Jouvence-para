<?php

declare(strict_types=1);

namespace JouvencePara\Core\Observability;

final class Redactor
{
    private const SENSITIVE_KEY_PATTERN = '/password|passwd|token|secret|authorization|cookie|card|nonce|session|cvv|cvc|cryptogram|transaction.?id|provider.?payload|provider.?response|gateway.?response|payment.?payload|payment.?record/i';

    public static function context(mixed $value): mixed
    {
        if (! is_array($value)) {
            return is_string($value) ? self::text($value) : $value;
        }

        $redacted = [];
        foreach ($value as $key => $child) {
            $keyString = (string) $key;
            if (preg_match(self::SENSITIVE_KEY_PATTERN, $keyString) === 1) {
                $redacted[$key] = '[redacted]';
                continue;
            }

            $redacted[$key] = self::context($child);
        }

        return $redacted;
    }

    public static function text(string $value): string
    {
        $value = preg_replace('/Bearer\s+[A-Za-z0-9._~+\/-]+=*/i', 'Bearer [redacted]', $value) ?? $value;
        $value = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[redacted-email]', $value) ?? $value;

        if (preg_match('//u', $value) !== 1) {
            return '';
        }
        $bounded = substr($value, 0, 1000);
        // Keep the existing byte budget without breaking a multibyte character in audit JSON.
        while (preg_match('//u', $bounded) !== 1) {
            $bounded = substr($bounded, 0, -1);
        }
        return $bounded;
    }
}
