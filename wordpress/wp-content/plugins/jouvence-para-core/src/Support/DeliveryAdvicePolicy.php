<?php

declare(strict_types=1);

namespace JouvencePara\Core\Support;

final class DeliveryAdvicePolicy
{
    public const DELIVERY_FEE_MILLI = 7000;
    public const FREE_THRESHOLD_MILLI = 200000;
    public const DELIVERY_WORKING_DAYS = 2;
    public const CARRIER = 'First Delivery';

    /** @return array<string, int|string|bool> */
    public static function publicRules(): array
    {
        return [
            'delivery_fee_milli' => self::DELIVERY_FEE_MILLI,
            'free_threshold_milli' => self::FREE_THRESHOLD_MILLI,
            'delivery_working_days' => self::DELIVERY_WORKING_DAYS,
            'carrier' => self::CARRIER,
            'manual_processing' => true,
            'store_pickup_free' => true,
        ];
    }
}
