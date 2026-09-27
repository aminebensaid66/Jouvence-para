<?php

declare(strict_types=1);

namespace JouvencePara\Core\Discovery;

use WP_Query;

final class WpFacetGateway implements FacetGateway
{
    /** @var array<string, list<int>> */
    private array $cache = [];

    public function productIds(FilterState $state, ?string $excludeGroup, int $categoryId): array
    {
        $taxQuery = $state->taxQuery($excludeGroup);
        if ($categoryId > 0) {
            $taxQuery[] = ['taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => [$categoryId], 'operator' => 'IN'];
        }
        $key = md5((string) wp_json_encode($taxQuery));
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }
        $query = new WP_Query([
            'post_type' => 'product',
            'post_status' => 'publish',
            'fields' => 'ids',
            'posts_per_page' => 2000,
            'no_found_rows' => true,
            'orderby' => 'ID',
            'order' => 'ASC',
            'tax_query' => $taxQuery,
        ]);
        $ids = array_values(array_map('intval', (array) $query->posts));
        $this->cache[$key] = $ids;
        return $ids;
    }

    public function termsForProducts(string $taxonomy, array $productIds): array
    {
        if ($productIds === [] || ! taxonomy_exists($taxonomy)) {
            return [];
        }
        $terms = wp_get_object_terms($productIds, $taxonomy, ['fields' => 'all_with_object_id']);
        if (is_wp_error($terms) || ! is_array($terms)) {
            return [];
        }
        $counts = [];
        foreach ($terms as $term) {
            $termId = (int) ($term->term_id ?? 0);
            if ($termId <= 0) {
                continue;
            }
            if (! isset($counts[$termId])) {
                $counts[$termId] = ['slug' => (string) $term->slug, 'name' => (string) $term->name, 'count' => 0];
            }
            $counts[$termId]['count']++;
        }
        usort($counts, static fn (array $a, array $b): int => strcasecmp($a['name'], $b['name']));
        return array_slice(array_values($counts), 0, 100);
    }
}
