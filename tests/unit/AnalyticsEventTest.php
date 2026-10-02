<?php

declare(strict_types=1);

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Analytics/AnalyticsEvent.php';

use JouvencePara\Core\Analytics\AnalyticsEvent;

return [
    'accepts the stable event list and bounded scalar and item payloads' => static function (TestHarness $test): void {
        $event = AnalyticsEvent::make('purchase', ['event' => 'search', 'schema_version' => 99, 'purchase_id' => str_repeat('a', 64), 'value' => 45.9, 'currency' => 'TND', 'items' => [['product_id' => 12, 'sku' => 'ABC', 'quantity' => 1, 'price' => 45.9, 'event' => 'search', 'schema_version' => 99]]]);
        $test->assertSame('purchase', $event['event']);
        $test->assertSame(1, $event['schema_version']);
        $test->assertSame(12, $event['items'][0]['product_id']);
        $test->assertTrue(! isset($event['items'][0]['event'], $event['items'][0]['schema_version']));
        $test->assertSame(null, AnalyticsEvent::make('not_a_contract_event'));
    },
    'drops direct personal identifiers, controls and unsupported values' => static function (TestHarness $test): void {
        $event = AnalyticsEvent::make('search', ['query' => 'cream', 'email' => 'person@example.invalid', 'phone' => '+21629302202', 'address_line_1' => 'private', 'raw' => "bad\0value", 'object' => new stdClass(), 'items' => [['product_id' => 1, 'email' => 'nested@example.invalid', 'phone' => 'hidden']]]);
        $test->assertTrue(! isset($event['query'], $event['email'], $event['phone'], $event['address_line_1'], $event['raw'], $event['object']));
        $test->assertSame(['product_id' => 1], $event['items'][0]);
    },
];
