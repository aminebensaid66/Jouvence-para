<?php

declare(strict_types=1);

namespace JouvencePara\Core\Catalog;

final class ProductAdminPolicy
{
    public const SUPPLIER_REFERENCE = '_jp_supplier_reference';
    public const SOURCE_REFERENCE = '_jp_source_reference';

    public static function sanitizeReference(string $value): string
    {
        $value = trim(strip_tags($value));
        return substr($value, 0, 191);
    }

    /** @return array<string, string> */
    public static function internalFields(): array
    {
        return [
            self::SUPPLIER_REFERENCE => 'Référence fournisseur',
            self::SOURCE_REFERENCE => 'Référence de traçabilité interne',
        ];
    }
}
