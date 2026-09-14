<?php

declare(strict_types=1);

use JouvencePara\Core\Geography\GeographyModule;
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Geography/GeographyNode.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Geography/GeographyCatalog.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Geography/TunisiaGeography.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Geography/GeographyModule.php';

return [
    'registers geography extension and catalog contracts' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        (new GeographyModule())->register();
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['jouvence_para_geography_nodes']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['jouvence_para_geography_catalog']));
    },
];
