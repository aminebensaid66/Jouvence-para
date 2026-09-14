<?php

declare(strict_types=1);

namespace JouvencePara\Core\Shipping;

use WC_Shipping_Method;

final class FirstDeliveryShippingMethod extends WC_Shipping_Method
{
    public function __construct(int $instanceId = 0)
    {
        $this->id = 'jp_first_delivery';
        $this->instance_id = absint($instanceId);
        $this->method_title = __('First Delivery', 'jouvence-para-core');
        $this->method_description = __('Livraison nationale traitée manuellement. Estimation : deux jours ouvrés.', 'jouvence-para-core');
        $this->supports = ['shipping-zones', 'instance-settings'];
        $this->enabled = 'yes';
        $this->title = __('First Delivery', 'jouvence-para-core');
        $this->init();
    }

    public function init(): void
    {
        $this->init_form_fields();
        $this->init_settings();
        $this->title = (string) $this->get_option('title', __('First Delivery', 'jouvence-para-core'));
    }

    public function init_form_fields(): void
    {
        $this->instance_form_fields = [
            'title' => [
                'title' => __('Titre', 'jouvence-para-core'),
                'type' => 'text',
                'default' => __('First Delivery', 'jouvence-para-core'),
                'description' => __('Le tarif est fixé par la décision logistique Jouvence Para.', 'jouvence-para-core'),
            ],
        ];
    }

    /** @param array<string, mixed> $package */
    public function calculate_shipping($package = []): void
    {
        $country = strtoupper((string) ($package['destination']['country'] ?? ''));
        if ($country !== ShippingPolicy::COUNTRY) {
            return;
        }
        $subtotalMilli = $this->discountedSubtotalMilli((array) ($package['contents'] ?? []));
        $cost = ShippingPolicy::tnd(ShippingPolicy::deliveryFeeMilli($subtotalMilli));
        $this->add_rate([
            'id' => $this->get_rate_id(),
            'label' => $this->title,
            'cost' => $cost,
            'package' => $package,
        ]);
    }

    /** @param array<int|string, mixed> $contents */
    private function discountedSubtotalMilli(array $contents): int
    {
        $amount = 0.0;
        foreach ($contents as $item) {
            if (! is_array($item)) {
                continue;
            }
            $amount += (float) ($item['line_total'] ?? 0) + (float) ($item['line_tax'] ?? 0);
        }
        return max(0, (int) round($amount * 1000));
    }
}
