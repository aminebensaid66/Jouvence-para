<?php

declare(strict_types=1);

namespace JouvencePara\Core\Inventory;

final class InventoryPolicy
{
    public const LOW_STOCK_THRESHOLD = 3;
    public const RESERVATION_MINUTES = 30;
    public const SAFETY_BUFFER = 1;

    public static function onlineAvailable(int|float|null $physicalStock): int
    {
        if ($physicalStock === null) {
            return 0;
        }
        return max(0, (int) floor($physicalStock) - self::SAFETY_BUFFER);
    }

    public static function canPurchase(int|float|null $physicalStock, int $quantity): bool
    {
        return $quantity > 0 && self::onlineAvailable($physicalStock) >= $quantity;
    }

    public static function publicAvailability(int|float|null $physicalStock): string
    {
        return self::onlineAvailable($physicalStock) > 0 ? 'available' : 'out_of_stock';
    }
}
