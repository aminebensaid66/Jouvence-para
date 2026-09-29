<?php

declare(strict_types=1);

namespace JouvencePara\Core\Checkout;

use JouvencePara\Core\Geography\TunisiaGeography;

final class CheckoutFields
{
    public const LIMITS = ['first_name' => 80, 'last_name' => 80, 'address_1' => 180];

    /** @return array<string, string> */
    public static function states(): array
    {
        $states = [];
        foreach (TunisiaGeography::governorates() as $node) {
            // WooCommerce normalizes native state keys to upper case during validation.
            $states[strtoupper($node->id)] = $node->labelFr;
        }
        return $states;
    }

    public static function text(mixed $value, ?int $maximum = null, bool $multiline = false): bool
    {
        if (! is_string($value) || preg_match('//u', $value) !== 1 || trim($value) === '') {
            return false;
        }
        $control = $multiline ? '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/' : '/[\x00-\x1F\x7F]/';
        if (preg_match($control, $value) === 1) {
            return false;
        }
        return $maximum === null || preg_match_all('/./us', $value) <= $maximum;
    }

    /** @param array<string, mixed> $address @return list<string> */
    public static function invalidAddress(array $address, bool $contact): array
    {
        $invalid = [];
        foreach (self::LIMITS + ['city' => null] as $key => $maximum) {
            if (! self::text($address[$key] ?? null, $maximum)) {
                $invalid[] = $key;
            }
        }
        if (($address['country'] ?? null) !== 'TN') {
            $invalid[] = 'country';
        }
        $state = $address['state'] ?? null;
        if (! is_string($state) || ! isset(self::states()[strtoupper($state)])) {
            $invalid[] = 'state';
        }
        if ($contact) {
            if (TunisianPhone::normalize($address['phone'] ?? null) === null) {
                $invalid[] = 'phone';
            }
            $email = $address['email'] ?? null;
            if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $invalid[] = 'email';
            }
        }
        return $invalid;
    }
}
