<?php

declare(strict_types=1);

namespace JouvencePara\Core\Shipping;

use JouvencePara\Core\Geography\TunisiaGeography;

final class ShippingMatrix
{
    /** @return array<string, array{carrier:string,manual:bool,working_days:int}> */
    public static function nationwide(): array
    {
        $rules = [];
        foreach (TunisiaGeography::governorates() as $governorate) {
            $rules[$governorate->id] = [
                'carrier' => 'First Delivery',
                'manual' => true,
                'working_days' => ShippingPolicy::WORKING_DAYS,
            ];
        }
        return $rules;
    }

    public static function supports(string $governorateId): bool
    {
        return isset(self::nationwide()[$governorateId]);
    }
}
