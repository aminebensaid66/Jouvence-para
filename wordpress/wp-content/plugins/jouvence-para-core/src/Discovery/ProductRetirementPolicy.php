<?php

declare(strict_types=1);

namespace JouvencePara\Core\Discovery;

final class ProductRetirementPolicy
{
    public const CONFIRMED = '_jp_seo_retirement_confirmed';
    public const REPLACEMENT = '_jp_seo_replacement_id';
    public const ROUTE = '_jp_seo_retired_route';

    /** Normalize a resource URL without retaining tracking/filter parameters. */
    public static function route(string $url): string
    {
        $parts = parse_url($url);
        if (! is_array($parts)) {
            return '';
        }
        $path = '/' . trim(rawurldecode((string) ($parts['path'] ?? '/')), '/');
        if (preg_match('/[\x00-\x20\x7f\\\\]/', $path) === 1
            || preg_match('~/\.{1,2}(/|$)~', $path) === 1) {
            return '';
        }
        $query = [];
        parse_str((string) ($parts['query'] ?? ''), $parameters);
        foreach (['p', 'post_type', 'product'] as $key) {
            if (isset($parameters[$key]) && is_string($parameters[$key]) && $parameters[$key] !== '') {
                $query[$key] = $parameters[$key];
            }
        }
        if ($path === '/' && ! isset($query['p']) && ! isset($query['product'])) {
            return '';
        }
        ksort($query);
        return $path . ($query === [] ? '' : '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986));
    }

    public static function optionName(string $route): string
    {
        return 'jp_seo_retired_' . hash('sha256', $route);
    }

    /** Only a public replacement on the same site is eligible for a permanent redirect. */
    public static function replacementUrl(string $url, string $home, string $retiredRoute): string
    {
        $target = parse_url($url);
        $site = parse_url($home);
        if (! is_array($target) || ! is_array($site)
            || ! in_array($target['scheme'] ?? '', ['http', 'https'], true)
            || ($target['scheme'] ?? '') !== ($site['scheme'] ?? '')
            || empty($target['host']) || strtolower($target['host']) !== strtolower((string) ($site['host'] ?? ''))
            || isset($target['user']) || isset($target['pass'])
            || ($target['port'] ?? null) !== ($site['port'] ?? null)
            || self::route($url) === '' || self::route($url) === $retiredRoute) {
            return '';
        }
        return $url;
    }
}
