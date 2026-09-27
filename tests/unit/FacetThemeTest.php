<?php

declare(strict_types=1);

return [
    'facet UI exposes active chips and individual/all clear actions' => static function (TestHarness $test): void {
        $source = (string) file_get_contents(__DIR__ . '/../../wordpress/wp-content/themes/jouvence-para/functions.php');
        $test->assertTrue(str_contains($source, 'jp-active-filters'));
        $test->assertTrue(str_contains($source, 'Retirer le filtre'));
        $test->assertTrue(str_contains($source, 'Effacer tous les filtres'));
        $test->assertTrue(str_contains($source, 'aria-live'));
    },
];
