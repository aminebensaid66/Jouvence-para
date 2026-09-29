<?php

declare(strict_types=1);

namespace JouvencePara\Core\Checkout {
    function __(string $message, string $domain = ''): string { return $message; }
    function WC(): object { return $GLOBALS['jp_checkout_wc']; }
}

namespace {
    function jp_checkout_request(string $method): WP_REST_Request
    {
        return new class($method) extends WP_REST_Request {
            public function __construct(private string $method) { parent::__construct(''); }
            public function get_method(): string { return $this->method; }
        };
    }
    function jp_checkout_data(): array
    {
        return ['billing_first_name' => 'Fixture', 'billing_last_name' => 'Customer', 'billing_address_1' => '1 Rue de test', 'billing_city' => 'Tunis', 'billing_state' => 'TN-TUNIS', 'billing_country' => 'TN', 'billing_email' => 'fixture@example.invalid', 'billing_phone' => '29 302 202', 'order_comments' => '', 'ship_to_different_address' => false];
    }
}
