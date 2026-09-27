<?php

declare(strict_types=1);

namespace JouvencePara\Core\Discovery;

final class FacetProvider
{
    public function __construct(private readonly FacetGateway $gateway)
    {
    }

    /** @return list<array{key:string,taxonomy:string,label:string,options:list<array{slug:string,name:string,count:int,active:bool}>}> */
    public function groups(FilterState $state, int $categoryId = 0): array
    {
        $labels = [
            'brand' => 'Marque', 'need' => 'Besoin', 'skin' => 'Type de peau', 'hair' => 'Type de cheveux',
            'spf' => 'SPF', 'audience' => 'Public cible',
        ];
        $groups = [];
        foreach (FilterState::TAXONOMIES as $key => $taxonomy) {
            $ids = $this->gateway->productIds($state, $key, $categoryId);
            $options = [];
            foreach ($this->gateway->termsForProducts($taxonomy, $ids) as $term) {
                $term['active'] = in_array($term['slug'], $state->group($key), true);
                $options[] = $term;
            }
            if ($options !== []) {
                $groups[] = ['key' => $key, 'taxonomy' => $taxonomy, 'label' => $labels[$key], 'options' => $options];
            }
        }
        return $groups;
    }
}
