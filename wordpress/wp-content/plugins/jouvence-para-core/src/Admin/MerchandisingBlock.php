<?php

declare(strict_types=1);

namespace JouvencePara\Core\Admin;

final class MerchandisingBlock
{
    public const TYPES = ['banner', 'category', 'brand', 'product'];

    public static function validType(string $type): string
    {
        return in_array($type, self::TYPES, true) ? $type : 'banner';
    }

    public static function requiresReference(string $type): bool
    {
        return in_array($type, ['category', 'brand', 'product'], true);
    }
}
