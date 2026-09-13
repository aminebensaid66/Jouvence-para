<?php

declare(strict_types=1);

namespace JouvencePara\Core\Catalog;

use WC_Product;

final class WooProductDataMapper
{
    public function __construct(private readonly WooProductIdentifiers $identifiers)
    {
    }

    public function map(WC_Product $product): ProductData
    {
        $id = (int) $product->get_id();
        $hasBrand = $id > 0
            && function_exists('taxonomy_exists')
            && taxonomy_exists('jp_brand')
            && function_exists('has_term')
            && has_term('', 'jp_brand', $id);

        return new ProductData(
            id: $id,
            name: (string) $product->get_name('edit'),
            sku: trim((string) $product->get_sku('edit')),
            ean: $this->identifiers->readEan($product),
            shortDescription: (string) $product->get_short_description('edit'),
            description: (string) $product->get_description('edit'),
            regularPrice: (string) $product->get_regular_price('edit'),
            salePrice: (string) $product->get_sale_price('edit'),
            stockQuantity: $product->get_stock_quantity('edit'),
            stockStatus: (string) $product->get_stock_status('edit'),
            manageStock: (bool) $product->get_manage_stock('edit'),
            status: (string) $product->get_status('edit'),
            imageId: (int) $product->get_image_id('edit'),
            galleryImageIds: array_values(array_map('intval', $product->get_gallery_image_ids('edit'))),
            weight: (string) $product->get_weight('edit'),
            length: (string) $product->get_length('edit'),
            width: (string) $product->get_width('edit'),
            height: (string) $product->get_height('edit'),
            categoryIds: array_values(array_map('intval', $product->get_category_ids('edit'))),
            hasBrand: (bool) $hasBrand,
            variation: $product->is_type('variation')
        );
    }
}
