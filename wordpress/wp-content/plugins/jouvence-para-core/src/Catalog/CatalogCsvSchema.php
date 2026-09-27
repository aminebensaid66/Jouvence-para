<?php

declare(strict_types=1);

namespace JouvencePara\Core\Catalog;

final class CatalogCsvSchema
{
    /** @return list<string> */
    public static function headers(): array
    {
        return [
            'sku', 'name', 'ean', 'regular_price', 'sale_price', 'stock_quantity', 'stock_status',
            'short_description', 'description', 'brand', 'category', 'image_id', 'status',
        ];
    }

    /** @param list<string> $headers */
    public static function normalizeHeaders(array $headers): array
    {
        return array_map(static fn (string $header): string => sanitize_key(trim($header)), $headers);
    }
}
