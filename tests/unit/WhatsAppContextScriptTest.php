<?php

declare(strict_types=1);

return [
    'WhatsApp analytics payload excludes message content and requires consent' => static function (TestHarness $test): void {
        $source = (string) file_get_contents(__DIR__ . '/../../wordpress/wp-content/themes/jouvence-para/assets/js/whatsapp-context.js');
        $test->assertTrue(str_contains($source, "allows?.('analytics')"));
        $test->assertTrue(str_contains($source, "event: 'whatsapp_click'"));
        $test->assertTrue(! str_contains($source, 'message'));
        $test->assertTrue(! str_contains($source, 'document.cookie'));
    },
];
