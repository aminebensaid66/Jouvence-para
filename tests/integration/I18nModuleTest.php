<?php

declare(strict_types=1);

use JouvencePara\Core\I18n\I18nModule;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/I18n/I18nPolicy.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/I18n/I18nModule.php';

return [
    'registers translation loading without overriding the selected locale' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        (new I18nModule())->register();
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['action']['init']));
        $test->assertTrue(! isset($GLOBALS['jp_test_hooks']['filter']['locale']));
    },
];
