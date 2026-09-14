<?php

declare(strict_types=1);

use JouvencePara\Core\Geography\GeographyCatalog;
use JouvencePara\Core\Geography\GeographyNode;
use JouvencePara\Core\Geography\TunisiaGeography;
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Geography/GeographyNode.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Geography/GeographyCatalog.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Geography/TunisiaGeography.php';

return [
    'seeds all 24 Tunisian governorates with stable IDs' => static function (TestHarness $test): void {
        $nodes = TunisiaGeography::governorates();
        $test->assertSame(24, count($nodes));
        $catalog = new GeographyCatalog($nodes);
        $test->assertTrue($catalog->has('tn-tunis'));
        $test->assertTrue($catalog->has('tn-medenine'));
    },
    'validates governorate delegation locality parent paths' => static function (TestHarness $test): void {
        $catalog = new GeographyCatalog([
            new GeographyNode('tn-test', GeographyNode::GOVERNORATE, 'Test'),
            new GeographyNode('tn-test-del', GeographyNode::DELEGATION, 'Délégation', 'tn-test'),
            new GeographyNode('tn-test-loc', GeographyNode::LOCALITY, 'Localité', 'tn-test-del'),
        ]);
        $test->assertTrue($catalog->validPath('tn-test', 'tn-test-del', 'tn-test-loc'));
        $test->assertTrue(! $catalog->validPath('tn-test', 'tn-test-loc'));
    },
    'rejects duplicate stable identifiers' => static function (TestHarness $test): void {
        $thrown = false;
        try {
            new GeographyCatalog([
                new GeographyNode('same', GeographyNode::GOVERNORATE, 'A'),
                new GeographyNode('same', GeographyNode::GOVERNORATE, 'B'),
            ]);
        } catch (InvalidArgumentException) { $thrown = true; }
        $test->assertTrue($thrown);
    },
];
