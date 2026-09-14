<?php

declare(strict_types=1);

use JouvencePara\Core\Catalog\CatalogTaxonomyModule;
use JouvencePara\Core\Catalog\TaxonomyDefinition;

if (! class_exists('WP_Error')) {
    class WP_Error
    {
        public function __construct(public string $code = '', public string $message = '') {}
    }
}
if (! function_exists('register_taxonomy')) {
    function register_taxonomy(string $taxonomy, array|string $objectType, array $args = []): object
    {
        $GLOBALS['jp_test_taxonomies'][$taxonomy] = [$objectType, $args];
        return (object) ['name' => $taxonomy];
    }
}
if (! function_exists('term_exists')) {
    function term_exists(string|int $term, string $taxonomy = '', int $parentTerm = 0): mixed
    {
        return $GLOBALS['jp_test_terms'][$taxonomy][(string) $term] ?? null;
    }
}
if (! function_exists('wp_insert_term')) {
    function wp_insert_term(string $term, string $taxonomy, array $args = []): array|WP_Error
    {
        $GLOBALS['jp_test_inserted_terms'][] = [$term, $taxonomy, $args];
        return ['term_id' => count($GLOBALS['jp_test_inserted_terms']), 'term_taxonomy_id' => 1];
    }
}
if (! function_exists('is_wp_error')) {
    function is_wp_error(mixed $value): bool { return $value instanceof WP_Error; }
}
if (! function_exists('sanitize_title')) {
    function sanitize_title(string $value): string { return TaxonomyDefinition::stableSlug($value); }
}
if (! function_exists('delete_transient')) {
    function delete_transient(string $name): bool { $GLOBALS['jp_test_deleted_transients'][] = $name; return true; }
}

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/TaxonomyDefinition.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/CatalogTaxonomyModule.php';

return [
    'registers controlled brand and concern taxonomies' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_taxonomies'] = [];
        (new CatalogTaxonomyModule())->registerTaxonomies();
        $test->assertTrue(isset($GLOBALS['jp_test_taxonomies'][TaxonomyDefinition::BRAND]));
        $test->assertTrue(isset($GLOBALS['jp_test_taxonomies'][TaxonomyDefinition::CONCERN]));
        $test->assertSame(false, $GLOBALS['jp_test_taxonomies'][TaxonomyDefinition::BRAND][1]['hierarchical']);
    },
    'seeds approved categories idempotently and no brands' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_terms'] = [];
        $GLOBALS['jp_test_inserted_terms'] = [];
        (new CatalogTaxonomyModule())->seedCategories();
        $test->assertSame(12, count($GLOBALS['jp_test_inserted_terms']));
        $test->assertSame('product_cat', $GLOBALS['jp_test_inserted_terms'][0][1]);
    },
    'rejects duplicate controlled slugs' => static function (TestHarness $test): void {
        $GLOBALS['jp_test_terms'] = [TaxonomyDefinition::BRAND => ['avene' => ['term_id' => 12]]];
        $result = (new CatalogTaxonomyModule())->preventDuplicateControlledTerm('Avène', TaxonomyDefinition::BRAND, []);
        $test->assertTrue($result instanceof WP_Error);
    },
    'does not change unrelated taxonomy terms' => static function (TestHarness $test): void {
        $result = (new CatalogTaxonomyModule())->preventDuplicateControlledTerm('Nouveau', 'post_tag', []);
        $test->assertSame('Nouveau', $result);
    },
];
