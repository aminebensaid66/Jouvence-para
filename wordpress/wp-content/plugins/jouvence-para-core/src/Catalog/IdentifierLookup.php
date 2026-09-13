<?php

declare(strict_types=1);

namespace JouvencePara\Core\Catalog;

interface IdentifierLookup
{
    public function skuOwner(string $sku): int;

    public function eanOwner(string $ean, int $excludeProductId = 0): int;
}
