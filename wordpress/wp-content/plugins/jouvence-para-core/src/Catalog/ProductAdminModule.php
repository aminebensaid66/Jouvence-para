<?php

declare(strict_types=1);

namespace JouvencePara\Core\Catalog;

use JouvencePara\Core\Contracts\Module;
use WP_Post;

final class ProductAdminModule implements Module
{
    private const NONCE_ACTION = 'jp_product_admin_fields';
    private const NONCE_FIELD = 'jp_product_admin_nonce';

    public function register(): void
    {
        add_action('init', [$this, 'registerMeta']);
        add_action('add_meta_boxes_product', [$this, 'addMetaBox']);
        add_action('woocommerce_admin_process_product_object', [$this, 'saveInternalFields'], 30);
    }

    public function registerMeta(): void
    {
        foreach (ProductAdminPolicy::internalFields() as $key => $label) {
            unset($label);
            register_post_meta('product', $key, [
                'type' => 'string',
                'single' => true,
                'show_in_rest' => false,
                'sanitize_callback' => [ProductAdminPolicy::class, 'sanitizeReference'],
                'auth_callback' => static function (bool $allowed, string $metaKey, int $postId): bool {
                    unset($allowed, $metaKey);
                    return current_user_can('edit_post', $postId) && current_user_can('edit_products');
                },
            ]);
        }
    }

    public function addMetaBox(): void
    {
        add_meta_box(
            'jp-product-readiness',
            __('Jouvence Para — contrôle catalogue', 'jouvence-para-core'),
            [$this, 'renderMetaBox'],
            'product',
            'side',
            'high'
        );
    }

    public function renderMetaBox(WP_Post $post): void
    {
        if (! current_user_can('edit_post', $post->ID) || ! current_user_can('edit_products')) {
            return;
        }
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_FIELD);
        $product = wc_get_product($post->ID);
        if (! $product) {
            echo '<p>' . esc_html__('Produit WooCommerce indisponible.', 'jouvence-para-core') . '</p>';
            return;
        }
        $data = (new WooProductDataMapper(new WooProductIdentifiers()))->map($product);
        $result = (new ProductDataValidator(new WooProductIdentifiers()))->validate($data);
        if ($result->publicationReady()) {
            echo '<p><strong>' . esc_html__('Prêt à publier', 'jouvence-para-core') . '</strong></p>';
        } else {
            echo '<p><strong>' . esc_html__('Publication bloquée :', 'jouvence-para-core') . '</strong></p><ul>';
            foreach ($result->allErrors() as $error) {
                echo '<li>' . esc_html($error) . '</li>';
            }
            echo '</ul>';
        }

        foreach (ProductAdminPolicy::internalFields() as $key => $label) {
            $value = (string) $product->get_meta($key, true, 'edit');
            printf(
                '<p><label for="%1$s"><strong>%2$s</strong></label><input class="widefat" id="%1$s" name="%1$s" value="%3$s" maxlength="191"></p>',
                esc_attr($key),
                esc_html__($label, 'jouvence-para-core'),
                esc_attr($value)
            );
        }
        echo '<p class="description">' . esc_html__('Ces références sont internes et ne sont pas exposées sur la boutique.', 'jouvence-para-core') . '</p>';
    }

    public function saveInternalFields(object $product): void
    {
        $productId = method_exists($product, 'get_id') ? (int) $product->get_id() : 0;
        if ($productId <= 0 || ! current_user_can('edit_post', $productId) || ! current_user_can('edit_products')) {
            return;
        }
        $nonce = isset($_POST[self::NONCE_FIELD]) ? sanitize_text_field(wp_unslash((string) $_POST[self::NONCE_FIELD])) : '';
        if ($nonce === '' || ! wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            return;
        }
        foreach (ProductAdminPolicy::internalFields() as $key => $label) {
            unset($label);
            $raw = isset($_POST[$key]) ? wp_unslash((string) $_POST[$key]) : '';
            $value = ProductAdminPolicy::sanitizeReference($raw);
            if ($value === '') {
                $product->delete_meta_data($key);
            } else {
                $product->update_meta_data($key, $value);
            }
        }
    }
}
