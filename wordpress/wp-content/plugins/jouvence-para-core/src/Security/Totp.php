<?php

declare(strict_types=1);

namespace JouvencePara\Core\Security;

final class Totp
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function generateSecret(int $bytes = 20): string
    {
        return self::base32Encode(random_bytes(max(16, $bytes)));
    }

    public static function code(string $secret, int $timestamp, int $digits = 6, int $period = 30): string
    {
        $key = self::base32Decode($secret);
        if ($key === '') {
            return '';
        }
        $counter = intdiv($timestamp, $period);
        $binaryCounter = pack('N2', ($counter >> 32) & 0xffffffff, $counter & 0xffffffff);
        $hash = hash_hmac('sha1', $binaryCounter, $key, true);
        $offset = ord($hash[19]) & 0x0f;
        $binary = ((ord($hash[$offset]) & 0x7f) << 24)
            | ((ord($hash[$offset + 1]) & 0xff) << 16)
            | ((ord($hash[$offset + 2]) & 0xff) << 8)
            | (ord($hash[$offset + 3]) & 0xff);
        $modulo = 10 ** $digits;
        return str_pad((string) ($binary % $modulo), $digits, '0', STR_PAD_LEFT);
    }

    public static function verify(string $secret, string $code, ?int $timestamp = null, int $window = 1): bool
    {
        $code = preg_replace('/\D+/', '', $code) ?? '';
        if (strlen($code) !== 6 || $secret === '') {
            return false;
        }
        $timestamp ??= time();
        for ($offset = -$window; $offset <= $window; $offset++) {
            if (hash_equals(self::code($secret, $timestamp + ($offset * 30)), $code)) {
                return true;
            }
        }
        return false;
    }

    public static function uri(string $secret, string $account, string $issuer = 'Jouvence Para'): string
    {
        $label = rawurlencode($issuer . ':' . $account);
        return 'otpauth://totp/' . $label . '?secret=' . rawurlencode($secret) . '&issuer=' . rawurlencode($issuer) . '&period=30&digits=6';
    }

    private static function base32Encode(string $bytes): string
    {
        $bits = '';
        foreach (str_split($bytes) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }
        $encoded = '';
        foreach (str_split($bits, 5) as $chunk) {
            $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            $encoded .= self::ALPHABET[bindec($chunk)];
        }
        return $encoded;
    }

    private static function base32Decode(string $value): string
    {
        $value = strtoupper(preg_replace('/[^A-Z2-7]/i', '', $value) ?? '');
        if ($value === '') {
            return '';
        }
        $bits = '';
        foreach (str_split($value) as $character) {
            $position = strpos(self::ALPHABET, $character);
            if ($position === false) {
                return '';
            }
            $bits .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }
        $decoded = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $decoded .= chr(bindec($chunk));
            }
        }
        return $decoded;
    }
}
