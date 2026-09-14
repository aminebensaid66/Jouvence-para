<?php

declare(strict_types=1);

namespace JouvencePara\Core\Catalog;

use RuntimeException;
use WC_Product;
use WC_Product_Simple;
use WC_Product_Query;

final class WooCatalogCsvGateway implements CatalogCsvGateway
{
    public function __construct(private readonly WooProductIdentifiers $identifiers = new WooProductIdentifiers())
    {
    }

    public function categoryId(string $slug): int
    {
        $term = get_term_by('slug', sanitize_title($slug), 'product_cat');
        return $term && ! is_wp_error($term) ? (int) $term->term_id : 0;
    }

    public function brandId(string $slug): int
    {
        $term = get_term_by('slug', sanitize_title($slug), TaxonomyDefinition::BRAND);
        return $term && ! is_wp_error($term) ? (int) $term->term_id : 0;
    }

    public function productIdBySku(string $sku): int
    {
        return (int) wc_get_product_id_by_sku($sku);
    }

    /** @param array<string, mixed> $row */
    public function save(array $row, int $productId, int $categoryId, int $brandId): int
    {
        $product = $productId > 0 ? wc_get_product($productId) : new WC_Product_Simple();
        if (! $product instanceof WC_Product) {
            throw new RuntimeException('Unable to load product.');
        }

        $product->set_name(sanitize_text_field((string) $row['name']));
        $product->set_sku(sanitize_text_field((string) $row['sku']));
        $product->set_regular_price(TndMoney::normalize((string) $row['regular_price']));
        $product->set_sale_price((string) $row['sale_price'] === '' ? '' : TndMoney::normalize((string) $row['sale_price']));
        $product->set_manage_stock(true);
        $product->set_stock_quantity((int) $row['stock_quantity']);
        $product->set_stock_status((string) $row['stock_status']);
        $product->set_backorders('no');
        $product->set_short_description(wp_kses_post((string) $row['short_description']));
        $product->set_description(wp_kses_post((string) $row['description']));
        $product->set_category_ids([$categoryId]);
        $product->set_image_id(absint($row['image_id']));
        $requestedStatus = (string) $row['status'];
        $product->set_status($productId > 0 ? $requestedStatus : 'draft');
        $this->identifiers->writeEan($product, (string) $row['ean']);
        $savedId = (int) $product->save();
        if ($savedId <= 0) {
            throw new RuntimeException('WooCommerce product save failed.');
        }
        $termResult = wp_set_object_terms($savedId, [$brandId], TaxonomyDefinition::BRAND, false);
        if (is_wp_error($termResult)) {
            throw new RuntimeException('Brand assignment failed.');
        }
        if ((string) $product->get_status('edit') !== $requestedStatus) {
            $product->set_status($requestedStatus);
            $savedId = (int) $product->save();
            if ($savedId <= 0) {
                throw new RuntimeException('WooCommerce product status save failed.');
            }
        }
        return $savedId;
    }

    /** @return list<array<string, scalar|null>> */
    public function exportPage(int $page, int $perPage): array
    {
        $query = new WC_Product_Query([
            'limit' => max(1, min(200, $perPage)),
            'page' => max(1, $page),
            'paginate' => false,
            'orderby' => 'ID',
            'order' => 'ASC',
            'return' => 'objects',
        ]);
        $rows = [];
        foreach ($query->get_products() as $product) {
            if (! $product instanceof WC_Product || $product->is_type('variation')) {
                continue;
            }
            $brandTerms = wp_get_object_terms($product->get_id(), TaxonomyDefinition::BRAND, ['fields' => 'slugs']);
            $categoryTerms = wp_get_object_terms($product->get_id(), 'product_cat', ['fields' => 'slugs']);
            $rows[] = [
                'sku' => $product->get_sku('edit'),
                'name' => $product->get_name('edit'),
                'ean' => $this->identifiers->readEan($product),
                'regular_price' => $product->get_regular_price('edit'),
                'sale_price' => $product->get_sale_price('edit'),
                'stock_quantity' => $product->get_stock_quantity('edit'),
                'stock_status' => $product->get_stock_status('edit'),
                'short_description' => $product->get_short_description('edit'),
                'description' => $product->get_description('edit'),
                'brand' => is_array($brandTerms) ? (string) ($brandTerms[0] ?? '') : '',
                'category' => is_array($categoryTerms) ? (string) ($categoryTerms[0] ?? '') : '',
                'image_id' => $product->get_image_id('edit'),
                'status' => $product->get_status('edit'),
            ];
        }
        return $rows;
    }
}
