<?php

declare(strict_types=1);

namespace JouvencePara\Core\Discovery;

use JouvencePara\Core\Contracts\Module;

final class DiscoveryRoutingModule implements Module
{
    public function register(): void
    {
        add_filter('woocommerce_catalog_orderby', [$this, 'orderbyOptions']);
        add_filter('woocommerce_default_catalog_orderby_options', [$this, 'orderbyOptions']);
        add_filter('woocommerce_get_catalog_ordering_args', [$this, 'orderingArgs'], 20, 3);
        add_filter('woocommerce_pagination_args', [$this, 'paginationArgs']);
        add_filter('wp_robots', [$this, 'robots']);
        add_action('wp_head', [$this, 'canonical'], 1);
    }

    /** @param array<string, string> $options @return array<string, string> */
    public function orderbyOptions(array $options): array
    {
        unset($options);
        return array_map(static fn (string $label): string => __($label, 'jouvence-para-core'), SortPolicy::options());
    }

    /** @param array<string, mixed> $args @return array<string, mixed> */
    public function orderingArgs(array $args, string $orderby = '', string $order = ''): array
    {
        unset($order);
        $requestOrder = $_GET['orderby'] ?? 'relevance';
        $orderby = $orderby !== '' ? $orderby : (is_scalar($requestOrder) ? (string) $requestOrder : 'relevance');
        $normalized = SortPolicy::normalize($orderby);
        if ($normalized === 'bestseller') {
            $args['orderby'] = 'meta_value_num ID';
            $args['order'] = 'DESC';
            $args['meta_key'] = 'total_sales';
            return $args;
        }
        return SortPolicy::orderingArgs($args, $normalized);
    }

    /** @param array<string, mixed> $args @return array<string, mixed> */
    public function paginationArgs(array $args): array
    {
        $args['add_args'] = DiscoveryUrlPolicy::paginationArgs(wp_unslash($_GET));
        return $args;
    }

    /** @param array<string, bool|string> $robots @return array<string, bool|string> */
    public function robots(array $robots): array
    {
        if ($this->isProductArchive() && DiscoveryUrlPolicy::hasVariantState(wp_unslash($_GET))) {
            $robots['noindex'] = true;
            $robots['follow'] = true;
        }
        return $robots;
    }

    public function canonical(): void
    {
        if (! $this->isProductArchive() || ! DiscoveryUrlPolicy::hasVariantState(wp_unslash($_GET))) {
            return;
        }
        $url = '';
        $object = get_queried_object();
        if ($object instanceof \WP_Term && is_object_in_taxonomy('product', $object->taxonomy)) {
            $termLink = get_term_link($object);
            $url = is_string($termLink) ? $termLink : '';
        } elseif (function_exists('wc_get_page_permalink')) {
            $url = (string) wc_get_page_permalink('shop');
        }
        if ($url !== '') {
            echo '<link rel="canonical" href="' . esc_url($url) . '">' . "\n";
        }
    }

    private function isProductArchive(): bool
    {
        return (function_exists('is_shop') && is_shop()) || (function_exists('is_product_taxonomy') && is_product_taxonomy());
    }
}
