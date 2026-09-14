<?php

declare(strict_types=1);

namespace JouvencePara\Core\Catalog;

interface CatalogCsvGateway
{
    public function categoryId(string $slug): int;
    public function brandId(string $slug): int;
    public function productIdBySku(string $sku): int;
    /** @param array<string, mixed> $row */
    public function save(array $row, int $productId, int $categoryId, int $brandId): int;
    /** @return list<array<string, scalar|null>> */
    public function exportPage(int $page, int $perPage): array;
}
