<?php

declare(strict_types=1);

use JouvencePara\Core\Discovery\FacetedFilterModule;
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/FilterState.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/FacetGateway.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/FacetProvider.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/WpFacetGateway.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/FacetedFilterModule.php';

return [
    'registers WooCommerce tax query and facet contracts' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_hooks'] = [];
        (new FacetedFilterModule())->register();
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['woocommerce_product_query_tax_query']));
        $test->assertTrue(isset($GLOBALS['jp_test_hooks']['filter']['jouvence_para_facets']));
    },
];
