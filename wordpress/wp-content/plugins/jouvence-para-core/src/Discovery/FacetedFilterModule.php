<?php

declare(strict_types=1);

namespace JouvencePara\Core\Discovery;

use JouvencePara\Core\Contracts\Module;

final class FacetedFilterModule implements Module
{
    public function register(): void
    {
        add_filter('woocommerce_product_query_tax_query', [$this, 'applyTaxQuery'], 20, 2);
        add_filter('jouvence_para_filter_state', [$this, 'state']);
        add_filter('jouvence_para_facets', [$this, 'facets']);
    }

    /** @param array<int|string, mixed> $taxQuery @return array<int|string, mixed> */
    public function applyTaxQuery(array $taxQuery, mixed $query = null): array
    {
        unset($query);
        $state = FilterState::fromRequest(wp_unslash($_GET));
        $extra = $state->taxQuery();
        foreach ($extra as $key => $clause) {
            if ($key === 'relation') {
                continue;
            }
            $taxQuery[] = $clause;
        }
        if (count($taxQuery) > 1 && ! isset($taxQuery['relation'])) {
            $taxQuery['relation'] = 'AND';
        }
        return $taxQuery;
    }

    public function state(mixed $state = null): FilterState
    {
        return $state instanceof FilterState ? $state : FilterState::fromRequest(wp_unslash($_GET));
    }

    /** @param array<int, mixed> $groups @return array<int, mixed> */
    public function facets(array $groups = []): array
    {
        $state = $this->state();
        $categoryId = 0;
        $object = get_queried_object();
        if ($object instanceof \WP_Term && $object->taxonomy === 'product_cat') {
            $categoryId = (int) $object->term_id;
        }
        return array_merge($groups, (new FacetProvider(new WpFacetGateway()))->groups($state, $categoryId));
    }
}
