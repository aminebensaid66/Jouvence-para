<?php

declare(strict_types=1);

namespace JouvencePara\Core\Discovery;

final class DiscoveryUrlPolicy
{
    /** @param array<string, mixed> $request */
    public static function hasVariantState(array $request): bool
    {
        foreach (array_keys(FilterState::TAXONOMIES) as $key) {
            if (array_key_exists('jp_filter_' . $key, $request)) {
                return true;
            }
        }
        foreach (array_keys($request) as $key) {
            if (str_starts_with((string) $key, 'filter_') || str_starts_with((string) $key, 'query_type_')
                || in_array($key, ['min_price', 'max_price', 'rating_filter', 'stock_status', 'on_sale'], true)) {
                return true;
            }
        }
        $requestedOrder = $request['orderby'] ?? 'relevance';
        return ! is_scalar($requestedOrder) || (string) $requestedOrder !== 'relevance';
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
