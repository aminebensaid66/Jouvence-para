<?php

declare(strict_types=1);

namespace JouvencePara\Core\Analytics;

final class AnalyticsEvent
{
    /** @var list<string> */
    public const NAMES = [
        'search', 'search_no_results', 'search_result_click', 'category_view', 'filter_use', 'product_view', 'whatsapp_click',
        'add_to_cart', 'remove_from_cart', 'cart_view', 'checkout_start', 'checkout_error', 'shipping_selected',
        'payment_selected', 'purchase', 'coupon_applied', 'coupon_rejected', 'newsletter_signup',
        'back_in_stock_request', 'account_registration', 'reorder',
    ];

    /** @param array<string, mixed> $payload @return array<string, mixed>|null */
    public static function make(string $name, array $payload = []): ?array
    {
        if (! in_array($name, self::NAMES, true)) {
            return null;
        }
        $result = ['schema_version' => 1, 'event' => $name];
        foreach ($payload as $key => $value) {
            if (! is_string($key) || ! preg_match('/^[a-z][a-z0-9_]{0,40}$/', $key)) {
                continue;
            }
            if (in_array($key, ['event', 'schema_version'], true)) {
                continue;
            }
            if (preg_match('/(?:email|phone|name|address|customer|user|password|token|secret|billing|shipping)/i', $key)) {
                continue;
            }
            $safe = self::safeValue($value, 0, (string) $key);
            if ($safe !== null) {
                $result[$key] = $safe;
            }
        }
        return $result;
    }

    private static function safeValue(mixed $value, int $depth, string $field): mixed
    {
        if ($depth > 2 || is_resource($value) || $value instanceof \Stringable) {
            return null;
        }
        if (is_int($value) || is_float($value) || is_bool($value)) {
            return $value;
        }
        if (is_string($value) && strlen($value) <= 160 && ! preg_match('/[\x00-\x1F\x7F]/', $value)) {
            if ($field === 'sku' && preg_match('/^[A-Za-z0-9._\/-]{1,80}$/', $value) === 1) {
                return $value;
            }
            if ($field === 'currency' && preg_match('/^[A-Z]{3}$/', $value) === 1) {
                return $value;
            }
            if (in_array($field, ['brand', 'category', 'context', 'method'], true)
                && preg_match('/^[a-z0-9_-]{1,80}$/i', $value) === 1) {
                return $value;
            }
            if ($field === 'purchase_id' && preg_match('/^[a-f0-9]{64}$/', $value) === 1) {
                return $value;
            }
            return null;
        }
        if (is_array($value)) {
            $safe = [];
            foreach (array_slice($value, 0, 100, true) as $key => $item) {
                if ((! is_string($key) && ! is_int($key))
                    || (is_string($key) && preg_match('/^[a-z][a-z0-9_]{0,40}$/', $key) !== 1)) {
                    continue;
                }
                if (is_string($key) && in_array($key, ['event', 'schema_version'], true)) {
                    continue;
                }
                if (is_string($key) && preg_match('/(?:email|phone|name|address|customer|user|password|token|secret|billing|shipping)/i', $key)) {
                    continue;
                }
                $normalized = self::safeValue($item, $depth + 1, (string) $key);
                if ($normalized !== null) {
                    $safe[$key] = $normalized;
                }
            }
            return $safe;
        }
        return null;
    }
}
