<?php

declare(strict_types=1);

namespace JouvencePara\Core\Shipping;

final class ShippingPolicy
{
    public const COUNTRY = 'TN';
    public const FIRST_DELIVERY = 'first_delivery';
    public const STORE_PICKUP = 'store_pickup';
    public const DELIVERY_FEE_MILLI = 7000;
    public const FREE_THRESHOLD_MILLI = 200000;
    public const WORKING_DAYS = 2;

    public static function deliveryFeeMilli(int $discountedSubtotalMilli): int
    {
        return $discountedSubtotalMilli >= self::FREE_THRESHOLD_MILLI ? 0 : self::DELIVERY_FEE_MILLI;
    }

    public static function tnd(int $milli): string
    {
        return number_format($milli / 1000, 3, '.', '');
    }
}
