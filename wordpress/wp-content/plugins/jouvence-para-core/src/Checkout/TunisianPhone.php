<?php

declare(strict_types=1);

namespace JouvencePara\Core\Checkout;

final class TunisianPhone
{
    public static function normalize(mixed $value): ?string
    {
        if (! is_string($value) || strlen($value) > 128 || preg_match('/[\x00-\x1F\x7F]/', $value)
            || ! preg_match('/^[+0-9\s().-]+$/u', $value)) {
            return null;
        }
        $digits = preg_replace('/[\s().-]+/u', '', $value);
        if (! is_string($digits)) {
            return null;
        }
        foreach (['+216', '00216'] as $prefix) {
            if (str_starts_with($digits, $prefix)) {
                $digits = substr($digits, strlen($prefix));
                break;
            }
        }
        // A compact national number may legitimately begin with 216; only treat
        // bare 216 as a country prefix when the complete input has 11 digits.
        if (strlen($digits) === 11 && str_starts_with($digits, '216')) {
            $digits = substr($digits, 3);
        }
        return preg_match('/^[2-9][0-9]{7}$/D', $digits) === 1 ? '+216' . $digits : null;
    }
}
