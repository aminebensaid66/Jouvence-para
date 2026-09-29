<?php

declare(strict_types=1);

use JouvencePara\Core\Customers\CommunicationPreferences;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Customers/CommunicationPreferences.php';

return [
    'email preferences default off and only honor complete typed records' => static function (TestHarness $test): void {
        foreach ([null, '', [], ['email_marketing' => true], ['version' => '1', 'email_marketing' => true, 'updated_at' => '2026-09-29T12:00:00Z'], ['version' => 1, 'email_marketing' => 'yes', 'updated_at' => '2026-09-29T12:00:00Z']] as $record) {
            $test->assertSame(false, CommunicationPreferences::emailEnabled($record));
        }
        $record = ['version' => 1, 'email_marketing' => true, 'updated_at' => '2026-09-29T12:00:00Z'];
        $test->assertSame(true, CommunicationPreferences::emailEnabled($record));
        $record['email_marketing'] = false;
        $test->assertSame(false, CommunicationPreferences::emailEnabled($record));
        $record['email_marketing'] = true;
        $record['extra'] = 'unrecognized';
        $test->assertSame(false, CommunicationPreferences::emailEnabled($record));
    },
];
