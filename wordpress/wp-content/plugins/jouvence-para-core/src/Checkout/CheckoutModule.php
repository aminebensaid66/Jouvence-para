<?php

declare(strict_types=1);

namespace JouvencePara\Core\Checkout;

use JouvencePara\Core\Contracts\Module;
use WC_Order;
use WP_Error;
use WP_REST_Request;

final class CheckoutModule implements Module
{
    public function register(): void
    {
        add_filter('woocommerce_states', [$this, 'states']);
        add_filter('woocommerce_countries_base_country', [$this, 'baseCountry']);
        add_filter('woocommerce_get_country_locale', [$this, 'locale']);
        add_filter('woocommerce_checkout_fields', [$this, 'fields']);
        add_filter('woocommerce_checkout_posted_data', [$this, 'normalize']);
        add_action('woocommerce_after_checkout_validation', [$this, 'validate'], 10, 2);
        add_filter('pre_option_woocommerce_checkout_phone_field', static fn (): string => 'required');
        add_action('woocommerce_store_api_checkout_update_order_from_request', [$this, 'storeOrder'], 20, 2);
    }

    public function states(array $states): array
    {
        $states['TN'] = CheckoutFields::states();
        return $states;
    }

    public function baseCountry(string $country): string
    {
        unset($country);
        return 'TN';
    }

    public function locale(array $locale): array
    {
        $locale['TN']['state'] = ['required' => true, 'hidden' => false, 'label' => __('Gouvernorat', 'jouvence-para-core')];
        $locale['TN']['city'] = ['required' => true, 'hidden' => false, 'label' => __('Délégation ou localité', 'jouvence-para-core')];
        $locale['TN']['postcode']['required'] = false;
        return $locale;
    }

    public function fields(array $fields): array
    {
        foreach (['billing', 'shipping'] as $group) {
            foreach (CheckoutFields::LIMITS as $name => $maximum) {
                $fields[$group][$group . '_' . $name]['required'] = true;
                $fields[$group][$group . '_' . $name]['custom_attributes']['maxlength'] = $maximum;
            }
            $fields[$group][$group . '_postcode']['required'] = false;
        }
        $fields['billing']['billing_phone']['required'] = true;
        $fields['billing']['billing_phone']['description'] = __('Numéro tunisien à 8 chiffres, avec ou sans +216.', 'jouvence-para-core');
        $fields['billing']['billing_email']['required'] = true;
        $fields['order']['order_comments']['custom_attributes']['maxlength'] = 500;
        return $fields;
    }

    public function normalize(array $data): array
    {
        $phone = TunisianPhone::normalize($data['billing_phone'] ?? null);
        if ($phone !== null) {
            $data['billing_phone'] = $phone;
        }
        return $data;
    }

    public function validate(array $data, WP_Error $errors): void
    {
        foreach (['billing', 'shipping'] as $group) {
            if ($group === 'shipping' && empty($data['ship_to_different_address'])) {
                continue;
            }
            $address = [];
            foreach (['first_name', 'last_name', 'address_1', 'city', 'state', 'country', 'phone', 'email'] as $key) {
                $address[$key] = $data[$group . '_' . $key] ?? null;
            }
            foreach (CheckoutFields::invalidAddress($address, $group === 'billing') as $key) {
                $field = $group . '_' . $key;
                $errors->add('jp_' . $field, self::message($key), ['id' => $field]);
            }
        }
        $notes = $data['order_comments'] ?? '';
        if ($notes !== '' && ! CheckoutFields::text($notes, 500, true)) {
            $errors->add('jp_order_comments', self::message('notes'), ['id' => 'order_comments']);
        }
    }

    public function storeOrder(WC_Order $order, WP_REST_Request $request): void
    {
        // Partial native PATCH updates retain data without forcing a complete submission.
        if ($request->get_method() !== 'POST') {
            return;
        }
        $invalid = [];
        foreach (['billing', 'shipping'] as $group) {
            if ($group === 'shipping' && ! WC()->cart->needs_shipping()) {
                continue;
            }
            $address = [];
            foreach (['first_name', 'last_name', 'address_1', 'city', 'state', 'country', 'phone', 'email'] as $key) {
                $getter = 'get_' . $group . '_' . $key;
                // Shipping does not have an email field in the native order model.
                $address[$key] = $key === 'email' && $group === 'shipping' ? '' : $order->$getter('edit');
            }
            foreach (CheckoutFields::invalidAddress($address, $group === 'billing') as $key) {
                $invalid[$group . '_address.' . $key] = self::message($key);
            }
        }
        $notes = $order->get_customer_note('edit');
        if ($notes !== '' && ! CheckoutFields::text($notes, 500, true)) {
            $invalid['customer_note'] = self::message('notes');
        }
        if ($invalid !== []) {
            throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException(
                'jp_checkout_fields', __('Vérifiez les champs indiqués et réessayez.', 'jouvence-para-core'), 400, ['params' => $invalid]
            );
        }
        $phone = TunisianPhone::normalize($order->get_billing_phone('edit'));
        if ($phone !== null) {
            $order->set_billing_phone($phone);
        }
        // Native Store API saves the order after this hook, before payment processing.
    }

    private static function message(string $field): string
    {
        return match ($field) {
            'first_name' => __('Prénom requis : 80 caractères maximum.', 'jouvence-para-core'),
            'last_name' => __('Nom requis : 80 caractères maximum.', 'jouvence-para-core'),
            'address_1' => __('Adresse requise : 180 caractères maximum.', 'jouvence-para-core'),
            'city' => __('Indiquez une délégation ou localité valide.', 'jouvence-para-core'),
            'state' => __('Choisissez un gouvernorat dans la liste.', 'jouvence-para-core'),
            'country' => __('La livraison est disponible en Tunisie.', 'jouvence-para-core'),
            'phone' => __('Indiquez un numéro tunisien à 8 chiffres, avec ou sans +216.', 'jouvence-para-core'),
            'email' => __('Indiquez une adresse e-mail valide.', 'jouvence-para-core'),
            default => __('Notes de commande : 500 caractères maximum.', 'jouvence-para-core'),
        };
    }
}
