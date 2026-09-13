<?php

declare(strict_types=1);

use JouvencePara\Core\Audit\AuditEvent;
use JouvencePara\Core\Observability\Redactor;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Observability/Redactor.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Audit/AuditEvent.php';

return [
    'normalizes audit identifiers' => static function (TestHarness $test): void {
        $test->assertSame('order_status_changed', AuditEvent::action('Order Status Changed'));
        $test->assertSame('payment_config', AuditEvent::objectType('Payment Config'));
    },
    'redacts sensitive audit snapshots' => static function (TestHarness $test): void {
        $snapshot = AuditEvent::snapshot(['token' => 'do-not-store', 'status' => 'ok']);
        $test->assertSame('[redacted]', $snapshot['token']);
        $test->assertSame('ok', $snapshot['status']);
    },
];
