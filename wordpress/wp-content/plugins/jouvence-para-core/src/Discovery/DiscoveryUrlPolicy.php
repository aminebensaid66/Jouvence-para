<?php

declare(strict_types=1);

namespace JouvencePara\Core\Discovery;

final class DiscoveryUrlPolicy
{
    /** @param array<string, mixed> $request */
    public static function hasVariantState(array $request): bool
    {
        foreach (array_keys(FilterState::TAXONOMIES) as $key) {
            if (! empty($request['jp_filter_' . $key])) {
                return true;
            }
        }
        $requestedOrder = $request['orderby'] ?? 'relevance';
        $orderby = SortPolicy::normalize(is_scalar($requestedOrder) ? (string) $requestedOrder : 'relevance');
        return $orderby !== 'relevance';
    }

    /** @param array<string, mixed> $request @return array<string, mixed> */
    public static function paginationArgs(array $request): array
    {
        $args = [];
        foreach (array_keys(FilterState::TAXONOMIES) as $key) {
            $name = 'jp_filter_' . $key;
            if (isset($request[$name])) {
                $state = FilterState::fromRequest([$name => $request[$name]]);
                if ($state->group($key) !== []) {
                    $args[$name] = $state->group($key);
                }
            }
        }
        if (isset($request['orderby'])) {
            $args['orderby'] = SortPolicy::normalize(is_scalar($request['orderby']) ? (string) $request['orderby'] : 'relevance');
        }
        return $args;
    }
}
