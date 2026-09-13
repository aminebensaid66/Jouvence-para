<?php

declare(strict_types=1);

namespace JouvencePara\Core\Catalog;

final class ProductDataValidator
{
    private const VALID_STOCK_STATUSES = ['instock', 'outofstock'];

    public function __construct(private readonly IdentifierLookup $identifiers)
    {
    }

    public function validate(ProductData $product): ProductValidationResult
    {
        $result = new ProductValidationResult();

        $this->validateIdentifiers($product, $result);
        $this->validateMoney($product->regularPrice, 'regular_price', $result);
        if ($product->salePrice !== '') {
            $this->validateMoney($product->salePrice, 'sale_price', $result);
        }
        if ($product->stockQuantity !== null && $product->stockQuantity < 0) {
            $result->addHardError('negative_stock_quantity');
        }
        if (! in_array($product->stockStatus, self::VALID_STOCK_STATUSES, true)) {
            $result->addHardError('invalid_stock_status');
        }

        foreach (
            [
                'weight' => $product->weight,
                'length' => $product->length,
                'width' => $product->width,
                'height' => $product->height,
            ] as $field => $value
        ) {
            $this->validateMeasurement($field, $value, $result);
        }

        if (! $product->variation) {
            $this->validatePublicationReadiness($product, $result);
        }

        return $result;
    }

    private function validateIdentifiers(ProductData $product, ProductValidationResult $result): void
    {
        if ($product->sku !== '') {
            $owner = $this->identifiers->skuOwner($product->sku);
            if ($owner > 0 && $owner !== $product->id) {
                $result->addHardError('duplicate_sku');
            }
        }

        if ($product->ean !== '') {
            $owner = $this->identifiers->eanOwner($product->ean, $product->id);
            if ($owner > 0) {
                $result->addHardError('duplicate_ean');
            }
        }
    }

    private function validateMoney(string $value, string $field, ProductValidationResult $result): void
    {
        if ($value === '') {
            return;
        }

        $normalized = TndMoney::normalize($value);
        if ($normalized === null) {
            $result->addHardError('invalid_' . $field);
            return;
        }
        if (str_starts_with($normalized, '-')) {
            $result->addHardError('negative_' . $field);
        }
    }

    private function validateMeasurement(string $field, string $value, ProductValidationResult $result): void
    {
        $value = trim(str_replace(',', '.', $value));
        if ($value === '') {
            return;
        }
        if (preg_match('/^[+-]?\d+(?:\.\d+)?$/', $value) !== 1) {
            $result->addHardError('invalid_' . $field);
            return;
        }
        if (str_starts_with($value, '-')) {
            $result->addHardError('negative_' . $field);
        }
    }

    private function validatePublicationReadiness(ProductData $product, ProductValidationResult $result): void
    {
        if (trim($product->name) === '') {
            $result->addPublicationError('missing_name');
        }
        if (trim($product->sku) === '') {
            $result->addPublicationError('missing_sku');
        }
        if (trim($product->regularPrice) === '') {
            $result->addPublicationError('missing_regular_price');
        }
        if (! $product->manageStock) {
            $result->addPublicationError('stock_management_required');
        }
        if ($product->stockQuantity === null) {
            $result->addPublicationError('missing_stock_quantity');
        }
        if (! self::hasText($product->shortDescription) && ! self::hasText($product->description)) {
            $result->addPublicationError('missing_description');
        }
        if (! $product->hasBrand) {
            $result->addPublicationError('missing_brand');
        }
        if ($product->categoryIds === []) {
            $result->addPublicationError('missing_category');
        }
        if ($product->imageId <= 0) {
            $result->addPublicationError('missing_featured_image');
        }
    }

    private static function hasText(string $value): bool
    {
        $plainText = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim($plainText) !== '';
    }
}
