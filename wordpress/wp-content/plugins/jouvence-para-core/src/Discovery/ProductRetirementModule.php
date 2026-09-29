<?php

declare(strict_types=1);

namespace JouvencePara\Core\Discovery;

use JouvencePara\Core\Contracts\Module;
use WC_Product;

final class ProductRetirementModule implements Module
{
    private const NONCE_ACTION = 'jp_product_retirement';
    private const NONCE_FIELD = 'jp_product_retirement_nonce';

    public function register(): void
    {
        add_action('woocommerce_product_options_advanced', [$this, 'adminFields']);
        add_action('woocommerce_admin_process_product_object', [$this, 'saveFields'], 35);
        add_action('wp_trash_post', [$this, 'recordRemoval']);
        add_action('before_delete_post', [$this, 'recordRemoval']);
        add_action('untrashed_post', [$this, 'restore']);
        add_action('template_redirect', [$this, 'handleMissingProduct'], 1);
        add_filter('do_redirect_guess_404_permalink', [$this, 'allowGuessedRedirect']);
    }

    public function adminFields(): void
    {
        global $product_object;
        if (! $product_object instanceof WC_Product
            || ! current_user_can('edit_post', $product_object->get_id()) || ! current_user_can('edit_products')) {
            return;
        }
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_FIELD);
        woocommerce_wp_checkbox([
            'id' => ProductRetirementPolicy::CONFIRMED,
            'label' => __('Retrait définitif confirmé', 'jouvence-para-core'),
            'value' => $product_object->get_meta(ProductRetirementPolicy::CONFIRMED),
            'description' => __('Effet SEO après suppression du produit. Une rupture de stock conserve sa page.', 'jouvence-para-core'),
        ]);
        woocommerce_wp_text_input([
            'id' => ProductRetirementPolicy::REPLACEMENT,
            'label' => __('Produit de remplacement (ID)', 'jouvence-para-core'),
            'type' => 'number',
            'value' => $product_object->get_meta(ProductRetirementPolicy::REPLACEMENT),
            'custom_attributes' => ['min' => '0', 'step' => '1'],
            'description' => __('Choisissez un remplacement pertinent et publié. Sans remplacement : HTTP 410 après retrait confirmé.', 'jouvence-para-core'),
        ]);
    }

    public function saveFields(WC_Product $product): void
    {
        $nonce = $_POST[self::NONCE_FIELD] ?? '';
        if ($product->get_id() <= 0 || $product->is_type('variation')
            || ! current_user_can('edit_post', $product->get_id()) || ! current_user_can('edit_products')
            || ! is_string($nonce) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($nonce)), self::NONCE_ACTION)) {
            return;
        }
        $raw = $_POST[ProductRetirementPolicy::REPLACEMENT] ?? '';
        if (! is_string($raw) || ($raw !== '' && ! ctype_digit($raw))) {
            \WC_Admin_Meta_Boxes::add_error(__('ID de remplacement invalide.', 'jouvence-para-core'));
            return;
        }
        $replacementId = (int) $raw;
        if ($replacementId > 0) {
            $replacement = wc_get_product($replacementId);
            if (! $replacement instanceof WC_Product || $replacement->get_status() !== 'publish'
                || $replacement->is_type('variation') || $replacementId === $product->get_id()) {
                \WC_Admin_Meta_Boxes::add_error(__('Le remplacement doit être un autre produit publié.', 'jouvence-para-core'));
                return;
            }
        }
        $product->update_meta_data(ProductRetirementPolicy::CONFIRMED, ($_POST[ProductRetirementPolicy::CONFIRMED] ?? '') === 'yes' ? 'yes' : 'no');
        $product->update_meta_data(ProductRetirementPolicy::REPLACEMENT, $replacementId);
    }

    /** Duplicate trash/delete hooks upsert the same non-autoloaded route record. */
    public function recordRemoval(int $productId): void
    {
        $product = wc_get_product($productId);
        if (! $product instanceof WC_Product || $product->is_type('variation')
            || $product->get_meta(ProductRetirementPolicy::CONFIRMED) !== 'yes') {
            return;
        }
        $route = (string) $product->get_meta(ProductRetirementPolicy::ROUTE);
        if ($product->get_status() !== 'publish' && ($product->get_status() !== 'trash' || $route === '')) {
            return;
        }
        if ($route === '') {
            $route = ProductRetirementPolicy::route((string) get_permalink($productId));
        }
        if ($route === '') {
            return;
        }
        update_option(ProductRetirementPolicy::optionName($route), [
            'route' => $route, 'product_id' => $productId,
            'replacement_id' => (int) $product->get_meta(ProductRetirementPolicy::REPLACEMENT),
        ], false);
        $product->update_meta_data(ProductRetirementPolicy::ROUTE, $route);
        $product->save_meta_data();
    }

    public function restore(int $productId): void
    {
        $product = wc_get_product($productId);
        if (! $product instanceof WC_Product) {
            return;
        }
        $route = (string) $product->get_meta(ProductRetirementPolicy::ROUTE);
        if ($route !== '') {
            $key = ProductRetirementPolicy::optionName($route);
            $record = get_option($key, []);
            if (is_array($record) && (int) ($record['product_id'] ?? 0) === $productId) {
                delete_option($key);
            }
            $product->delete_meta_data(ProductRetirementPolicy::ROUTE);
            $product->update_meta_data(ProductRetirementPolicy::CONFIRMED, 'no');
            $product->save_meta_data();
        }
    }

    /** @return array{status: int, url: string}|null */
    public function response(string $requestUri): ?array
    {
        $route = ProductRetirementPolicy::route($requestUri);
        if ($route === '') {
            return null;
        }
        $record = get_option(ProductRetirementPolicy::optionName($route), []);
        if (! is_array($record) || ($record['route'] ?? '') !== $route || (int) ($record['product_id'] ?? 0) <= 0) {
            return null;
        }
        $replacementId = (int) ($record['replacement_id'] ?? 0);
        $replacement = $replacementId > 0 ? wc_get_product($replacementId) : false;
        $url = '';
        if ($replacement instanceof WC_Product && $replacement->get_status() === 'publish'
            && ! $replacement->is_type('variation') && $replacementId !== (int) $record['product_id']) {
            $url = ProductRetirementPolicy::replacementUrl((string) get_permalink($replacementId), home_url('/'), $route);
        }
        return ['status' => $url === '' ? 410 : 301, 'url' => $url];
    }

    public function handleMissingProduct(): void
    {
        if (! is_404() || is_admin() || ! isset($_SERVER['REQUEST_URI']) || ! is_string($_SERVER['REQUEST_URI'])) {
            return;
        }
        $response = $this->response(wp_unslash($_SERVER['REQUEST_URI']));
        if ($response === null) {
            return;
        }
        if ($response['status'] === 301 && wp_safe_redirect($response['url'], 301, 'Jouvence Para')) {
            exit;
        }
        // Keep WordPress's missing-page template, preventing its guessed canonical redirect.
        remove_action('template_redirect', 'redirect_canonical');
        status_header(410);
        nocache_headers();
        add_filter('wp_robots', static function (array $robots): array {
            unset($robots['index']);
            $robots['noindex'] = true;
            return $robots;
        });
    }

    public function allowGuessedRedirect(bool $allowed): bool
    {
        return in_array('product', (array) get_query_var('post_type'), true) || ! empty(get_query_var('product')) ? false : $allowed;
    }
}
