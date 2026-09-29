<?php

declare(strict_types=1);

use JouvencePara\Core\Checkout\CheckoutFields;
use JouvencePara\Core\Checkout\TunisianPhone;

foreach (['TunisianPhone', 'CheckoutFields'] as $class) {
    require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Checkout/' . $class . '.php';
}
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Geography/GeographyNode.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Geography/TunisiaGeography.php';

return [
    'normalizes national international and formatted TN numbers without operator assumptions' => static function (TestHarness $test): void {
        foreach (['29 302 202', '+216 29 302 202', '21629302202', '00216-29-302-202', '(29) 302.202', "+216\u{00A0}29\u{00A0}302202"] as $value) {
            $test->assertSame('+21629302202', TunisianPhone::normalize($value));
        }
        foreach (['70123456', '31123456', '50123456', '80123456', '21612345'] as $value) {
            $test->assertSame('+216' . $value, TunisianPhone::normalize($value));
        }
        $test->assertSame('+21621612345', TunisianPhone::normalize('+216 21612345'));
    },
    'rejects foreign prefixes extensions controls arrays and malformed lengths' => static function (TestHarness $test): void {
        foreach (['', '12345678', '01234567', '+3329302202', '293022020', '29302202ext1', '29+302202', "29302202\0", "29\n302202", [], 29302202, str_repeat(' ', 129)] as $value) {
            $test->assertSame(null, TunisianPhone::normalize($value));
        }
    },
    'counts unicode characters and rejects controls without truncation' => static function (TestHarness $test): void {
        $test->assertTrue(CheckoutFields::text(str_repeat('é', 80), 80));
        $test->assertTrue(! CheckoutFields::text(str_repeat('é', 81), 80));
        $test->assertTrue(CheckoutFields::text("Ligne 1\nLigne 2", 500, true));
        foreach (["\0", "A\nB", "\xFF", [], '   '] as $value) {
            $test->assertTrue(! CheckoutFields::text($value, 80));
        }
    },
    'validates every required address contact field and stable governorate identifier' => static function (TestHarness $test): void {
        $address = ['first_name' => 'Élodie', 'last_name' => 'Ben Ali', 'address_1' => '1 rue de test', 'city' => 'Tunis', 'state' => 'TN-TUNIS', 'country' => 'TN', 'phone' => '29 302 202', 'email' => 'fixture@example.invalid'];
        $test->assertSame([], CheckoutFields::invalidAddress($address, true));
        foreach (array_keys($address) as $key) {
            $bad = $address; $bad[$key] = '';
            $test->assertTrue(in_array($key, CheckoutFields::invalidAddress($bad, true), true));
        }
        $address['state'] = 'Tunis';
        $test->assertSame(['state'], CheckoutFields::invalidAddress($address, true));
        $test->assertSame(24, count(CheckoutFields::states()));
    },
];
