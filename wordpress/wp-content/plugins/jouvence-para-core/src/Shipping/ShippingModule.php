<?php

declare(strict_types=1);

namespace JouvencePara\Core\Shipping;

use JouvencePara\Core\Contracts\Module;

final class ShippingModule implements Module
{
    public function register(): void
    {
        add_filter('woocommerce_shipping_methods', [$this, 'methods']);
        add_filter('jouvence_para_shipping_matrix', [$this, 'matrix']);
    }

    /** @param array<string, class-string> $methods @return array<string, class-string> */
    public function methods(array $methods): array
    {
        $methods['jp_first_delivery'] = FirstDeliveryShippingMethod::class;
        $methods['jp_store_pickup'] = StorePickupShippingMethod::class;
        return $methods;
    }

    /** @param array<string, mixed> $matrix @return array<string, mixed> */
    public function matrix(array $matrix = []): array
    {
        return ShippingMatrix::nationwide() + $matrix;
    }
}
