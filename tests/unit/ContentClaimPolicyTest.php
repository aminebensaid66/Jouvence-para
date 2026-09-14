<?php

declare(strict_types=1);

if (! function_exists('wp_strip_all_tags')) { function wp_strip_all_tags(string $text): string { return strip_tags($text); } }
use JouvencePara\Core\Content\ContentClaimPolicy;
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Content/ContentClaimPolicy.php';

return [
    'blocks explicit cure diagnosis and guarantee language' => static function (TestHarness $test): void {
        $test->assertTrue(ContentClaimPolicy::violations('Guérit définitivement ce problème.') !== []);
        $test->assertTrue(ContentClaimPolicy::violations('Résultats garantis à 100% efficace.') !== []);
    },
    'requires provenance reviewer and approval' => static function (TestHarness $test): void {
        $test->assertTrue(ContentClaimPolicy::publishReady('MAN-REF-1', true, 7, 'Nettoie la peau en douceur.'));
        $test->assertTrue(! ContentClaimPolicy::publishReady('', true, 7, 'Texte'));
        $test->assertTrue(! ContentClaimPolicy::publishReady('REF', false, 7, 'Texte'));
    },
];
