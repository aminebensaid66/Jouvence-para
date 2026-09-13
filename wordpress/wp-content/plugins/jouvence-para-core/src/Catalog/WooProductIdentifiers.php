<?php

declare(strict_types=1);

namespace JouvencePara\Core\Catalog;

use WC_Product;

final class WooProductIdentifiers implements IdentifierLookup
{
    public const LEGACY_EAN_META_KEY = '_jp_ean_barcode';

    public function skuOwner(string $sku): int
    {
        if ($sku === '' || ! function_exists('wc_get_product_id_by_sku')) {
            return 0;
        }

        return (int) wc_get_product_id_by_sku($sku);
    }

    public function eanOwner(string $ean, int $excludeProductId = 0): int
    {
        if ($ean === '') {
            return 0;
        }

        if (function_exists('wc_get_product_id_by_global_unique_id')) {
            $owner = (int) wc_get_product_id_by_global_unique_id($ean);
            if ($owner > 0 && $owner !== $excludeProductId) {
                return $owner;
            }
        }

        if (! function_exists('get_posts')) {
            return 0;
        }

        $args = [
            'post_type' => ['product', 'product_variation'],
            'post_status' => 'any',
            'fields' => 'ids',
            'posts_per_page' => 1,
            'no_found_rows' => true,
            'meta_key' => self::LEGACY_EAN_META_KEY,
            'meta_value' => $ean,
        ];
        if ($excludeProductId > 0) {
            $args['post__not_in'] = [$excludeProductId];
        }

        $matches = get_posts($args);
        return is_array($matches) && isset($matches[0]) ? (int) $matches[0] : 0;
    }

    public function readEan(WC_Product $product): string
    {
        if (method_exists($product, 'get_global_unique_id')) {
            $native = ProductIdentifier::normalizeEan((string) $product->get_global_unique_id('edit'));
            if ($native !== '') {
                return $native;
            }
        }

        return ProductIdentifier::normalizeEan(
            (string) $product->get_meta(self::LEGACY_EAN_META_KEY, true, 'edit')
        );
    }

    public function writeEan(WC_Product $product, string $ean): void
    {
        $ean = ProductIdentifier::normalizeEan($ean);
        if (method_exists($product, 'set_global_unique_id')) {
            $product->set_global_unique_id($ean);
            $product->delete_meta_data(self::LEGACY_EAN_META_KEY);
            return;
        }

        if ($ean === '') {
            $product->delete_meta_data(self::LEGACY_EAN_META_KEY);
            return;
        }

        $product->update_meta_data(self::LEGACY_EAN_META_KEY, $ean);
    }

    public static function nativeGlobalIdAvailable(): bool
    {
        return method_exists(WC_Product::class, 'get_global_unique_id')
            && method_exists(WC_Product::class, 'set_global_unique_id');
    }
}
