<?php

declare(strict_types=1);

use JouvencePara\Core\I18n\I18nPolicy;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/I18n/I18nPolicy.php';

return [
    'uses French for launch without changing business timezone' => static function (TestHarness $test): void {
        $test->assertSame('fr_FR', I18nPolicy::LAUNCH_LOCALE);
        $test->assertSame('Africa/Tunis', I18nPolicy::BUSINESS_TIMEZONE);
    },
    'recognizes future RTL locales without enabling Arabic content' => static function (TestHarness $test): void {
        $test->assertTrue(I18nPolicy::isRtlLocale('ar_TN'));
        $test->assertTrue(! I18nPolicy::isRtlLocale('fr_FR'));
    },
];
