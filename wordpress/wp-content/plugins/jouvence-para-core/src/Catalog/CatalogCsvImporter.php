<?php

declare(strict_types=1);

namespace JouvencePara\Core\Catalog;

use Throwable;

final class CatalogCsvImporter
{
    public function __construct(private readonly CatalogCsvGateway $gateway)
    {
    }

    /** @param list<array<string, mixed>> $rows @return list<array{row:int, sku:string, status:string, product_id:int, errors:list<string>}> */
    public function import(array $rows): array
    {
        $report = [];
        $seen = [];
        foreach ($rows as $index => $row) {
            $sku = trim((string) ($row['sku'] ?? ''));
            $errors = $this->validateRow($row, $seen);
            if ($sku !== '') {
                $seen[strtolower($sku)] = true;
            }
            if ($errors !== []) {
                $report[] = ['row' => $index + 2, 'sku' => $sku, 'status' => 'rejected', 'product_id' => 0, 'errors' => $errors];
                continue;
            }

            $categoryId = $this->gateway->categoryId((string) $row['category']);
            $brandId = $this->gateway->brandId((string) $row['brand']);
            if ($categoryId <= 0) {
                $errors[] = 'unknown_category';
            }
            if ($brandId <= 0) {
                $errors[] = 'unknown_brand';
            }
            if ($errors !== []) {
                $report[] = ['row' => $index + 2, 'sku' => $sku, 'status' => 'rejected', 'product_id' => 0, 'errors' => $errors];
                continue;
            }

            try {
                $existingId = $this->gateway->productIdBySku($sku);
                $productId = $this->gateway->save($row, $existingId, $categoryId, $brandId);
                if ($productId <= 0) {
                    throw new \RuntimeException('Product persistence did not return an ID.');
                }
                $report[] = [
                    'row' => $index + 2,
                    'sku' => $sku,
                    'status' => $existingId > 0 ? 'updated' : 'created',
                    'product_id' => $productId,
                    'errors' => [],
                ];
            } catch (Throwable $throwable) {
                $report[] = ['row' => $index + 2, 'sku' => $sku, 'status' => 'failed', 'product_id' => 0, 'errors' => ['persistence_failed']];
            }
        }
        return $report;
    }

    /** @param array<string, mixed> $row @param array<string, bool> $seen @return list<string> */
    private function validateRow(array &$row, array $seen): array
    {
        $errors = [];
        foreach (CatalogCsvSchema::headers() as $header) {
            $row[$header] = $row[$header] ?? '';
        }
        $sku = trim((string) $row['sku']);
        if ($sku === '') {
            $errors[] = 'missing_sku';
        } elseif (isset($seen[strtolower($sku)])) {
            $errors[] = 'duplicate_sku_in_file';
        }
        if (trim((string) $row['name']) === '') {
            $errors[] = 'missing_name';
        }
        if (! $this->validNonNegativeDecimal((string) $row['regular_price'], false)) {
            $errors[] = 'invalid_regular_price';
        }
        if ((string) $row['sale_price'] !== '' && ! $this->validNonNegativeDecimal((string) $row['sale_price'], false)) {
            $errors[] = 'invalid_sale_price';
        }
        if (
            (string) $row['sale_price'] !== ''
            && $this->validNonNegativeDecimal((string) $row['regular_price'], false)
            && $this->decimalMilli((string) $row['sale_price']) > $this->decimalMilli((string) $row['regular_price'])
        ) {
            $errors[] = 'sale_price_above_regular_price';
        }
        if (filter_var((string) $row['stock_quantity'], FILTER_VALIDATE_INT) === false || (int) $row['stock_quantity'] < 0) {
            $errors[] = 'invalid_stock_quantity';
        }
        if (! in_array((string) $row['stock_status'], ['instock', 'outofstock'], true)) {
            $errors[] = 'invalid_stock_status';
        }
        if (trim((string) $row['brand']) === '') {
            $errors[] = 'missing_brand';
        }
        if (trim((string) $row['category']) === '') {
            $errors[] = 'missing_category';
        }
        $status = (string) $row['status'];
        if ($status === '') {
            $row['status'] = 'draft';
        } elseif (! in_array($status, ['draft', 'publish'], true)) {
            $errors[] = 'invalid_status';
        }
        $ean = ProductIdentifier::normalizeEan((string) $row['ean']);
        if ((string) $row['ean'] !== '' && $ean === '') {
            $errors[] = 'invalid_ean';
        }
        $row['ean'] = $ean;
        return $errors;
    }

    private function validNonNegativeDecimal(string $value, bool $allowEmpty): bool
    {
        $value = trim(str_replace(',', '.', $value));
        if ($value === '') {
            return $allowEmpty;
        }
        return preg_match('/^\d+(?:\.\d{1,3})?$/', $value) === 1;
    }

    private function decimalMilli(string $value): int
    {
        $parts = explode('.', str_replace(',', '.', trim($value)), 2);
        return ((int) $parts[0] * 1000) + (int) str_pad(substr($parts[1] ?? '', 0, 3), 3, '0');
    }
}
