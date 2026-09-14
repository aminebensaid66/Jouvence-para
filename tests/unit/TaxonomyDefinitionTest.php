<?php

declare(strict_types=1);

use JouvencePara\Core\Catalog\TaxonomyDefinition;

require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Catalog/TaxonomyDefinition.php';

return [
    'defines the twelve approved top-level categories with stable slugs' => static function (TestHarness $test): void {
        $categories = TaxonomyDefinition::topLevelCategories();
        $test->assertSame(12, count($categories));
        $test->assertSame('Visage', $categories['visage']);
        $test->assertSame('Bébé & Maman', $categories['bebe-maman']);
        $test->assertSame('Matériel médical et orthopédique', $categories['materiel-medical-orthopedique']);
    },
    'defines controlled global attributes' => static function (TestHarness $test): void {
        $attributes = TaxonomyDefinition::globalAttributes();
        $test->assertSame('skin_type', $attributes['skin_type']['slug']);
        $test->assertSame('size_volume', $attributes['size_volume']['slug']);
        $test->assertSame(5, count($attributes));
    },
    'normalizes French labels into deterministic stable slugs' => static function (TestHarness $test): void {
        $test->assertSame('bebe-maman', TaxonomyDefinition::stableSlug('Bébé & Maman'));
        $test->assertSame('mains-pieds-et-levres', TaxonomyDefinition::stableSlug('Mains, pieds et lèvres'));
    },
];
