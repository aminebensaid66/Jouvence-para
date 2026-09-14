<?php

declare(strict_types=1);

namespace JouvencePara\Core\Shipping;

use WC_Shipping_Method;

final class StorePickupShippingMethod extends WC_Shipping_Method
{
    public function __construct(int $instanceId = 0)
    {
        $this->id = 'jp_store_pickup';
        $this->instance_id = absint($instanceId);
        $this->method_title = __('Retrait en boutique', 'jouvence-para-core');
        $this->method_description = __('Retrait gratuit à Jouvence Para, Avenue de la Liberté, Midoun 4116, Djerba.', 'jouvence-para-core');
        $this->supports = ['shipping-zones'];
        $this->enabled = 'yes';
        $this->title = __('Retrait en boutique', 'jouvence-para-core');
    }

    /** @param array<string, mixed> $package */
    public function calculate_shipping($package = []): void
    {
        $this->add_rate([
            'id' => $this->get_rate_id(),
            'label' => $this->title,
            'cost' => '0.000',
            'package' => $package,
        ]);
    }
}
