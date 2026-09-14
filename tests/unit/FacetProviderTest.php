<?php

declare(strict_types=1);

use JouvencePara\Core\Discovery\FacetGateway;
use JouvencePara\Core\Discovery\FacetProvider;
use JouvencePara\Core\Discovery\FilterState;
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/FilterState.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/FacetGateway.php';
require_once __DIR__ . '/../../wordpress/wp-content/plugins/jouvence-para-core/src/Discovery/FacetProvider.php';

return [
    'marks active options while omitting empty groups' => static function (TestHarness $test): void {
        $gateway = new class implements FacetGateway {
            public function productIds(FilterState $state, ?string $excludeGroup, int $categoryId): array { return [1, 2]; }
            public function termsForProducts(string $taxonomy, array $productIds): array {
                return $taxonomy === 'jp_brand' ? [['slug' => 'avene', 'name' => 'Avène', 'count' => 2]] : [];
            }
        };
        $groups = (new FacetProvider($gateway))->groups(FilterState::fromRequest(['jp_filter_brand' => ['avene']]), 8);
        $test->assertSame(1, count($groups));
        $test->assertSame(true, $groups[0]['options'][0]['active']);
        $test->assertSame(2, $groups[0]['options'][0]['count']);
    },
];
