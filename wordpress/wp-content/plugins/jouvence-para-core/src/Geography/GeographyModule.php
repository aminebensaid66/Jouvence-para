<?php

declare(strict_types=1);

namespace JouvencePara\Core\Geography;

use JouvencePara\Core\Contracts\Module;

final class GeographyModule implements Module
{
    public function register(): void
    {
        add_filter('jouvence_para_geography_nodes', [$this, 'nodes']);
        add_filter('jouvence_para_geography_catalog', [$this, 'catalog']);
    }

    /** @param list<GeographyNode> $nodes @return list<GeographyNode> */
    public function nodes(array $nodes = []): array
    {
        return array_merge(TunisiaGeography::governorates(), $nodes);
    }

    public function catalog(mixed $catalog = null): GeographyCatalog
    {
        if ($catalog instanceof GeographyCatalog) {
            return $catalog;
        }
        $nodes = apply_filters('jouvence_para_geography_nodes', []);
        return new GeographyCatalog(is_array($nodes) ? $nodes : []);
    }
}
