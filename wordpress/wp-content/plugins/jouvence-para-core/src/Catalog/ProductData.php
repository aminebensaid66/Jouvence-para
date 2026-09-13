<?php

declare(strict_types=1);

namespace JouvencePara\Core\Catalog;

final class ProductData
{
    /**
     * @param list<int> $categoryIds
     * @param list<int> $galleryImageIds
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $sku,
        public readonly string $ean,
        public readonly string $shortDescription,
        public readonly string $description,
        public readonly string $regularPrice,
        public readonly string $salePrice,
        public readonly int|float|null $stockQuantity,
        public readonly string $stockStatus,
        public readonly bool $manageStock,
        public readonly string $status,
        public readonly int $imageId,
        public readonly array $galleryImageIds,
        public readonly string $weight,
        public readonly string $length,
        public readonly string $width,
        public readonly string $height,
        public readonly array $categoryIds,
        public readonly bool $hasBrand,
        public readonly bool $variation = false
    ) {
    }
}
