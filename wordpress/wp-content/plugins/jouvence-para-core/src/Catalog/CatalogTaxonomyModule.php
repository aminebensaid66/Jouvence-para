<?php

declare(strict_types=1);

namespace JouvencePara\Core\Catalog;

use JouvencePara\Core\Contracts\Module;
use WP_Error;

final class CatalogTaxonomyModule implements Module
{
    private const ATTRIBUTE_VERSION_OPTION = 'jp_catalog_attribute_version';
    private const ATTRIBUTE_VERSION = '1';
    private const CATEGORY_VERSION_OPTION = 'jp_catalog_category_version';
    private const CATEGORY_VERSION = '1';

    public function register(): void
    {
        add_action('init', [$this, 'registerTaxonomies'], 9);
        add_action('admin_init', [$this, 'seedCategories']);
        add_action('admin_init', [$this, 'ensureGlobalAttributes']);
        add_filter('pre_insert_term', [$this, 'preventDuplicateControlledTerm'], 10, 3);
    }

    public function registerTaxonomies(): void
    {
        register_taxonomy(
            TaxonomyDefinition::BRAND,
            ['product'],
            [
                'labels' => [
                    'name' => __('Marques', 'jouvence-para-core'),
                    'singular_name' => __('Marque', 'jouvence-para-core'),
                    'search_items' => __('Rechercher des marques', 'jouvence-para-core'),
                    'all_items' => __('Toutes les marques', 'jouvence-para-core'),
                    'edit_item' => __('Modifier la marque', 'jouvence-para-core'),
                    'add_new_item' => __('Ajouter une marque', 'jouvence-para-core'),
                ],
                'public' => true,
                'show_ui' => true,
                'show_admin_column' => true,
                'show_in_rest' => true,
                'hierarchical' => false,
                'query_var' => TaxonomyDefinition::BRAND,
                'rewrite' => ['slug' => 'marque', 'with_front' => false],
                'capabilities' => [
                    'manage_terms' => 'manage_product_terms',
                    'edit_terms' => 'edit_product_terms',
                    'delete_terms' => 'delete_product_terms',
                    'assign_terms' => 'assign_product_terms',
                ],
            ]
        );

        register_taxonomy(
            TaxonomyDefinition::CONCERN,
            ['product'],
            [
                'labels' => [
                    'name' => __('Besoins', 'jouvence-para-core'),
                    'singular_name' => __('Besoin', 'jouvence-para-core'),
                    'search_items' => __('Rechercher des besoins', 'jouvence-para-core'),
                    'all_items' => __('Tous les besoins', 'jouvence-para-core'),
                    'edit_item' => __('Modifier le besoin', 'jouvence-para-core'),
                    'add_new_item' => __('Ajouter un besoin', 'jouvence-para-core'),
                ],
                'public' => true,
                'show_ui' => true,
                'show_admin_column' => false,
                'show_in_rest' => true,
                'hierarchical' => false,
                'query_var' => TaxonomyDefinition::CONCERN,
                'rewrite' => ['slug' => 'besoin', 'with_front' => false],
                'capabilities' => [
                    'manage_terms' => 'manage_product_terms',
                    'edit_terms' => 'edit_product_terms',
                    'delete_terms' => 'delete_product_terms',
                    'assign_terms' => 'assign_product_terms',
                ],
            ]
        );
    }

    public function seedCategories(): void
    {
        if (
            ! current_user_can('manage_product_terms')
            || ! taxonomy_exists('product_cat')
            || get_option(self::CATEGORY_VERSION_OPTION, '') === self::CATEGORY_VERSION
        ) {
            return;
        }

        $complete = true;
        foreach (TaxonomyDefinition::topLevelCategories() as $slug => $name) {
            if (term_exists($slug, 'product_cat')) {
                continue;
            }
            $result = wp_insert_term($name, 'product_cat', ['slug' => $slug, 'parent' => 0]);
            if (is_wp_error($result)) {
                $complete = false;
                do_action('jouvence_para_catalog_seed_error', $slug, $result);
            }
        }

        if ($complete) {
            update_option(self::CATEGORY_VERSION_OPTION, self::CATEGORY_VERSION, false);
        }
    }

    public function ensureGlobalAttributes(): void
    {
        if (! current_user_can('manage_product_terms')) {
            return;
        }
        if (get_option(self::ATTRIBUTE_VERSION_OPTION, '') === self::ATTRIBUTE_VERSION) {
            return;
        }
        if (! function_exists('wc_get_attribute_taxonomies') || ! function_exists('wc_create_attribute')) {
            return;
        }

        $existing = [];
        foreach ((array) wc_get_attribute_taxonomies() as $attribute) {
            $slug = isset($attribute->attribute_name) ? (string) $attribute->attribute_name : '';
            if ($slug !== '') {
                $existing[$slug] = true;
            }
        }

        foreach (TaxonomyDefinition::globalAttributes() as $definition) {
            if (isset($existing[$definition['slug']])) {
                continue;
            }
            $result = wc_create_attribute([
                'name' => $definition['name'],
                'slug' => $definition['slug'],
                'type' => 'select',
                'order_by' => 'menu_order',
                'has_archives' => false,
            ]);
            if (is_wp_error($result) || $result === 0) {
                do_action('jouvence_para_catalog_attribute_error', $definition['slug'], $result);
                return;
            }
        }

        update_option(self::ATTRIBUTE_VERSION_OPTION, self::ATTRIBUTE_VERSION, false);
        delete_transient('wc_attribute_taxonomies');
    }

    public function preventDuplicateControlledTerm(string $term, string $taxonomy, array|string $args = []): string|WP_Error
    {
        if (! in_array($taxonomy, [TaxonomyDefinition::BRAND, TaxonomyDefinition::CONCERN], true)) {
            return $term;
        }

        $requestedSlug = is_array($args) ? (string) ($args['slug'] ?? '') : '';
        $slug = $requestedSlug !== '' ? sanitize_title($requestedSlug) : TaxonomyDefinition::stableSlug($term);
        if ($slug === '') {
            return new WP_Error('jp_invalid_term_slug', __('Le libellé ne produit pas un identifiant valide.', 'jouvence-para-core'));
        }

        $existing = term_exists($slug, $taxonomy);
        if ($existing !== 0 && $existing !== null) {
            return new WP_Error('jp_duplicate_controlled_term', __('Cette valeur contrôlée existe déjà.', 'jouvence-para-core'));
        }

        return $term;
    }
}
