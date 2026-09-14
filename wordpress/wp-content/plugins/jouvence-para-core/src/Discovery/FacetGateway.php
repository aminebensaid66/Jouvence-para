<?php

declare(strict_types=1);

namespace JouvencePara\Core\Discovery;

interface FacetGateway
{
    /** @return list<int> */
    public function productIds(FilterState $state, ?string $excludeGroup, int $categoryId): array;
    /** @param list<int> $productIds @return list<array{slug:string,name:string,count:int}> */
    public function termsForProducts(string $taxonomy, array $productIds): array;
}
