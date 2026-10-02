<?php

declare(strict_types=1);

return [
    'theme color tokens provide accessible body text and focus indicator contrast' => static function (TestHarness $test): void {
        $css = (string) file_get_contents(__DIR__ . '/../../wordpress/wp-content/themes/jouvence-para/style.css');
        preg_match('/--jp-color-ink:\s*(#[0-9a-f]{6})/i', $css, $inkMatch);
        preg_match('/--jp-color-background:\s*(#[0-9a-f]{6})/i', $css, $backgroundMatch);
        preg_match('/--jp-color-accent:\s*(#[0-9a-f]{6})/i', $css, $accentMatch);

        $test->assertTrue(isset($inkMatch[1], $backgroundMatch[1], $accentMatch[1]));
        $test->assertTrue(jp_test_a11y_contrast_ratio($inkMatch[1], $backgroundMatch[1]) >= 4.5);
        $test->assertTrue(jp_test_a11y_contrast_ratio($accentMatch[1], $backgroundMatch[1]) >= 3.0);
        $test->assertTrue(jp_test_a11y_contrast_ratio($accentMatch[1], '#ffffff') >= 3.0);
    },
    'theme keeps a visible keyboard focus indicator, larger primary targets, and global reduced motion' => static function (TestHarness $test): void {
        $css = (string) file_get_contents(__DIR__ . '/../../wordpress/wp-content/themes/jouvence-para/style.css');
        $test->assertTrue(str_contains($css, ':focus-visible { outline: 3px solid var(--jp-color-accent)'));
        $test->assertTrue(str_contains($css, '.site-header nav .menu a, .site-header nav .jp-account-link { display: inline-flex; min-height: 2.75rem; align-items: center; }'));
        $test->assertTrue(str_contains($css, '.jp-facets label, .jp-active-filters a { min-height: 2.75rem; }'));
        $test->assertTrue(str_contains($css, 'body.woocommerce .woocommerce-breadcrumb, body.woocommerce .woocommerce-breadcrumb a { color: var(--jp-color-ink); }'));
        $test->assertTrue(str_contains($css, '.woocommerce .show-password-input { min-width: 2.75rem; min-height: 2.75rem; }'));
        $test->assertTrue(str_contains($css, '@media (prefers-reduced-motion: reduce)'));
        $test->assertTrue(str_contains($css, 'animation-iteration-count: 1 !important;'));
        $test->assertTrue(str_contains($css, 'transition-duration: .01ms !important;'));
        $test->assertTrue(str_contains($css, '.woocommerce ul.products li.jp-product-card.product { float: none; width: auto; margin: 0; }'));
    },
    'critical theme pages retain a skip link and main landmark and product sort remains labeled' => static function (TestHarness $test): void {
        $theme = __DIR__ . '/../../wordpress/wp-content/themes/jouvence-para/';
        $header = (string) file_get_contents($theme . 'header.php');
        $test->assertTrue(str_contains($header, 'href="#main-content"'));

        foreach (['index.php', 'woocommerce/archive-product.php', 'woocommerce/single-product.php'] as $template) {
            $source = (string) file_get_contents($theme . $template);
            $test->assertTrue(str_contains($source, '<main id="main-content"'));
        }

        $ordering = (string) file_get_contents($theme . 'woocommerce/loop/orderby.php');
        $test->assertTrue(str_contains($ordering, '<label for="jp-orderby"'));
        $test->assertTrue(str_contains($ordering, 'id="jp-orderby"'));

        $functions = (string) file_get_contents($theme . 'functions.php');
        $test->assertTrue(str_contains($functions, "function_exists('is_checkout') && is_checkout()"));
        $test->assertTrue(str_contains($functions, 'assets/js/accessibility.js'));

        $script = (string) file_get_contents($theme . 'assets/js/accessibility.js');
        $test->assertTrue(str_contains($script, "input.setAttribute('autocomplete', 'email')"));
        $test->assertTrue(str_contains($script, 'new MutationObserver(normalizeCheckoutEmail)'));
    },
];

function jp_test_a11y_contrast_ratio(string $first, string $second): float
{
    $firstLuminance = jp_test_a11y_relative_luminance($first);
    $secondLuminance = jp_test_a11y_relative_luminance($second);
    $lighter = max($firstLuminance, $secondLuminance);
    $darker = min($firstLuminance, $secondLuminance);

    return ($lighter + 0.05) / ($darker + 0.05);
}

function jp_test_a11y_relative_luminance(string $color): float
{
    $channels = array_map(
        static function (string $channel): float {
            $value = hexdec($channel) / 255;

            return $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        },
        str_split(substr($color, 1), 2)
    );

    return ($channels[0] * 0.2126) + ($channels[1] * 0.7152) + ($channels[2] * 0.0722);
}
