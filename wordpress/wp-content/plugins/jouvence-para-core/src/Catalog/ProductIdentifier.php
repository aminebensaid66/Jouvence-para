<?php

declare(strict_types=1);

namespace JouvencePara\Core\Catalog;

final class ProductIdentifier
{
    public static function normalizeEan(string $value): string
    {
        $value = strip_tags($value);
        $value = preg_replace('/[\x00-\x1F\x7F]+/', '', $value) ?? '';
        $value = strtoupper(trim($value));
        return preg_replace('/\s+/', '', $value) ?? '';
    }
}
