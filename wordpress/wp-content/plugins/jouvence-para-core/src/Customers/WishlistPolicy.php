<?php

declare(strict_types=1);

namespace JouvencePara\Core\Customers;

final class WishlistPolicy
{
    public const MAX_ITEMS = 100;

    /** @return list<int> */
    public static function productIds(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $ids = [];
        foreach ($value as $candidate) {
            if (! is_int($candidate) && ! (is_string($candidate) && ctype_digit($candidate))) {
                continue;
            }
            $id = (int) $candidate;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        sort($ids, SORT_NUMERIC);
        return array_values($ids);
    }

    /** @param list<int> $productIds @return list<int> */
    public static function add(array $productIds, int $productId): array
    {
        $productIds = self::productIds($productIds);
        if ($productId < 1 || (! in_array($productId, $productIds, true) && count($productIds) >= self::MAX_ITEMS)) {
            return $productIds;
        }

        return self::productIds([...$productIds, $productId]);
    }

    /** @param list<int> $productIds @return list<int> */
    public static function remove(array $productIds, int $productId): array
    {
        return array_values(array_filter(self::productIds($productIds), static fn (int $id): bool => $id !== $productId));
    }
}
