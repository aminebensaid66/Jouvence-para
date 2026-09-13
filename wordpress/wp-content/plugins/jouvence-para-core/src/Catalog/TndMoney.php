<?php

declare(strict_types=1);

namespace JouvencePara\Core\Catalog;

final class TndMoney
{
    public const DECIMALS = 3;

    public static function normalize(string $value): ?string
    {
        $value = trim(str_replace(',', '.', $value));
        if ($value === '') {
            return '';
        }
        if (preg_match('/^[+-]?\d+(?:\.\d+)?$/', $value) !== 1) {
            return null;
        }

        $negative = str_starts_with($value, '-');
        $unsigned = ltrim($value, '+-');
        [$whole, $fraction] = array_pad(explode('.', $unsigned, 2), 2, '');
        $whole = ltrim($whole, '0');
        $whole = $whole === '' ? '0' : $whole;

        $fractionPadded = str_pad($fraction, self::DECIMALS + 1, '0');
        $keptFraction = substr($fractionPadded, 0, self::DECIMALS);
        $scaled = ltrim($whole . $keptFraction, '0');
        $scaled = $scaled === '' ? '0' : $scaled;

        if ((int) $fractionPadded[self::DECIMALS] >= 5) {
            $scaled = self::incrementDigits($scaled);
        }

        $scaled = str_pad($scaled, self::DECIMALS + 1, '0', STR_PAD_LEFT);
        $whole = substr($scaled, 0, -self::DECIMALS);
        $fraction = substr($scaled, -self::DECIMALS);
        $normalized = $whole . '.' . $fraction;

        return $negative && $normalized !== '0.000' ? '-' . $normalized : $normalized;
    }

    public static function isNegative(string $value): bool
    {
        $normalized = self::normalize($value);
        return $normalized !== null && str_starts_with($normalized, '-');
    }

    private static function incrementDigits(string $digits): string
    {
        $characters = str_split($digits);
        for ($index = count($characters) - 1; $index >= 0; $index--) {
            if ($characters[$index] !== '9') {
                $characters[$index] = (string) ((int) $characters[$index] + 1);
                return implode('', $characters);
            }
            $characters[$index] = '0';
        }

        return '1' . implode('', $characters);
    }
}
