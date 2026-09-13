<?php

declare(strict_types=1);

namespace JouvencePara\Core\Admin;

use JouvencePara\Core\Contracts\Module;
use WP_Post;
use WP_Query;

final class MerchandisingModule implements Module
{
    private const POST_TYPE = 'jp_merch_block';
    private const NONCE_ACTION = 'jp_save_merchandising_block';
    private const NONCE_FIELD = 'jp_merchandising_nonce';

    public function register(): void
    {
        add_action('init', [$this, 'registerPostType']);
        add_action('init', [$this, 'registerMeta']);
        add_action('add_meta_boxes', [$this, 'registerMetaBox']);
        add_action('save_post_' . self::POST_TYPE, [$this, 'saveMeta'], 10, 2);
        add_filter('jouvence_para_homepage_blocks', [$this, 'homepageBlocks']);
        add_filter('manage_' . self::POST_TYPE . '_posts_columns', [$this, 'columns']);
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', [$this, 'columnValue'], 10, 2);
    }

    public function registerPostType(): void
    {
        register_post_type(
            self::POST_TYPE,
            [
                'labels' => [
                    'name' => __('Homepage merchandising', 'jouvence-para-core'),
                    'singular_name' => __('Merchandising block', 'jouvence-para-core'),
                    'add_new_item' => __('Add merchandising block', 'jouvence-para-core'),
                    'edit_item' => __('Edit merchandising block', 'jouvence-para-core'),
                ],
                'public' => false,
                'publicly_queryable' => false,
                'exclude_from_search' => true,
                'show_ui' => true,
                'show_in_menu' => 'woocommerce',
                'show_in_rest' => false,
                'supports' => ['title', 'revisions', 'page-attributes'],
                'capability_type' => 'post',
                'map_meta_cap' => true,
                'capabilities' => [
                    'edit_posts' => 'manage_woocommerce',
                    'edit_others_posts' => 'manage_woocommerce',
                    'publish_posts' => 'manage_woocommerce',
                    'read_private_posts' => 'manage_woocommerce',
                    'delete_posts' => 'manage_woocommerce',
                    'delete_private_posts' => 'manage_woocommerce',
                    'delete_published_posts' => 'manage_woocommerce',
                    'delete_others_posts' => 'manage_woocommerce',
                    'edit_private_posts' => 'manage_woocommerce',
                    'edit_published_posts' => 'manage_woocommerce',
                ],
            ]
        );
    }

    public function registerMeta(): void
    {
        $common = [
            'single' => true,
            'show_in_rest' => false,
            'auth_callback' => static fn (): bool => current_user_can('manage_woocommerce'),
            'revisions_enabled' => true,
        ];

        register_post_meta(self::POST_TYPE, 'jp_block_type', $common + ['type' => 'string']);
        register_post_meta(self::POST_TYPE, 'jp_enabled', $common + ['type' => 'boolean']);
        register_post_meta(self::POST_TYPE, 'jp_reference', $common + ['type' => 'string']);
        register_post_meta(self::POST_TYPE, 'jp_media_id', $common + ['type' => 'integer']);
        register_post_meta(self::POST_TYPE, 'jp_link_url', $common + ['type' => 'string']);
        register_post_meta(self::POST_TYPE, 'jp_eyebrow', $common + ['type' => 'string']);
        register_post_meta(self::POST_TYPE, 'jp_body', $common + ['type' => 'string']);
        register_post_meta(self::POST_TYPE, 'jp_cta_label', $common + ['type' => 'string']);
    }

    public function registerMetaBox(): void
    {
        add_meta_box(
            'jp-merchandising-details',
            __('Block details', 'jouvence-para-core'),
            [$this, 'renderMetaBox'],
            self::POST_TYPE,
            'normal',
            'high'
        );
    }

    public function renderMetaBox(WP_Post $post): void
    {
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_FIELD);
        $type = MerchandisingBlock::validType((string) get_post_meta($post->ID, 'jp_block_type', true));
        $enabled = (bool) get_post_meta($post->ID, 'jp_enabled', true);
        $mediaId = (int) get_post_meta($post->ID, 'jp_media_id', true);
        ?>
        <p>
            <label for="jp_block_type"><strong><?php esc_html_e('Block type', 'jouvence-para-core'); ?></strong></label><br>
            <select id="jp_block_type" name="jp_block_type">
                <?php foreach (MerchandisingBlock::TYPES as $candidate) : ?>
                    <option value="<?php echo esc_attr($candidate); ?>" <?php selected($type, $candidate); ?>><?php echo esc_html(ucfirst($candidate)); ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label><input type="checkbox" name="jp_enabled" value="1" <?php checked($enabled); ?>> <?php esc_html_e('Enabled on homepage', 'jouvence-para-core'); ?></label>
        </p>
        <p>
            <label for="jp_reference"><strong><?php esc_html_e('Reference', 'jouvence-para-core'); ?></strong></label><br>
            <input class="widefat" id="jp_reference" name="jp_reference" value="<?php echo esc_attr((string) get_post_meta($post->ID, 'jp_reference', true)); ?>">
            <span class="description"><?php esc_html_e('Category/brand slug or product SKU. Leave empty for a banner.', 'jouvence-para-core'); ?></span>
        </p>
        <p>
            <label for="jp_link_url"><strong><?php esc_html_e('Link override', 'jouvence-para-core'); ?></strong></label><br>
            <input class="widefat" type="url" id="jp_link_url" name="jp_link_url" value="<?php echo esc_attr((string) get_post_meta($post->ID, 'jp_link_url', true)); ?>">
        </p>
        <p>
            <label for="jp_media_id"><strong><?php esc_html_e('Image attachment ID', 'jouvence-para-core'); ?></strong></label><br>
            <input type="number" min="0" id="jp_media_id" name="jp_media_id" value="<?php echo esc_attr((string) $mediaId); ?>">
        </p>
        <p>
            <label for="jp_eyebrow"><strong><?php esc_html_e('Eyebrow', 'jouvence-para-core'); ?></strong></label><br>
            <input class="widefat" id="jp_eyebrow" name="jp_eyebrow" value="<?php echo esc_attr((string) get_post_meta($post->ID, 'jp_eyebrow', true)); ?>">
        </p>
        <p>
            <label for="jp_body"><strong><?php esc_html_e('Short body', 'jouvence-para-core'); ?></strong></label><br>
            <textarea class="widefat" rows="4" id="jp_body" name="jp_body"><?php echo esc_textarea((string) get_post_meta($post->ID, 'jp_body', true)); ?></textarea>
        </p>
        <p>
            <label for="jp_cta_label"><strong><?php esc_html_e('CTA label', 'jouvence-para-core'); ?></strong></label><br>
            <input class="widefat" id="jp_cta_label" name="jp_cta_label" value="<?php echo esc_attr((string) get_post_meta($post->ID, 'jp_cta_label', true)); ?>">
        </p>
        <?php
    }

    public function saveMeta(int $postId, WP_Post $post): void
    {
        if ($post->post_type !== self::POST_TYPE || wp_is_post_revision($postId)) {
            return;
        }
        if (! isset($_POST[self::NONCE_FIELD]) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_FIELD])), self::NONCE_ACTION)) {
            return;
        }
        if (! current_user_can('manage_woocommerce')) {
            return;
        }

        $type = MerchandisingBlock::validType(sanitize_key((string) ($_POST['jp_block_type'] ?? 'banner')));
        $reference = sanitize_text_field(wp_unslash((string) ($_POST['jp_reference'] ?? '')));
        $enabled = isset($_POST['jp_enabled']) && $this->referenceExists($type, $reference);

        update_post_meta($postId, 'jp_block_type', $type);
        update_post_meta($postId, 'jp_enabled', $enabled ? '1' : '0');
        update_post_meta($postId, 'jp_reference', $reference);
        update_post_meta($postId, 'jp_media_id', absint($_POST['jp_media_id'] ?? 0));
        update_post_meta($postId, 'jp_link_url', esc_url_raw(wp_unslash((string) ($_POST['jp_link_url'] ?? ''))));
        update_post_meta($postId, 'jp_eyebrow', sanitize_text_field(wp_unslash((string) ($_POST['jp_eyebrow'] ?? ''))));
        update_post_meta($postId, 'jp_body', sanitize_textarea_field(wp_unslash((string) ($_POST['jp_body'] ?? ''))));
        update_post_meta($postId, 'jp_cta_label', sanitize_text_field(wp_unslash((string) ($_POST['jp_cta_label'] ?? ''))));
    }

    /** @param list<array<string, mixed>> $blocks */
    public function homepageBlocks(array $blocks): array
    {
        $query = new WP_Query([
            'post_type' => self::POST_TYPE,
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'orderby' => ['menu_order' => 'ASC', 'date' => 'DESC'],
            'meta_key' => 'jp_enabled',
            'meta_value' => '1',
            'no_found_rows' => true,
        ]);

        foreach ($query->posts as $post) {
            $type = MerchandisingBlock::validType((string) get_post_meta($post->ID, 'jp_block_type', true));
            $reference = (string) get_post_meta($post->ID, 'jp_reference', true);
            if (MerchandisingBlock::requiresReference($type) && $reference === '') {
                continue;
            }

            $mediaId = (int) get_post_meta($post->ID, 'jp_media_id', true);
            $blocks[] = [
                'id' => $post->ID,
                'type' => $type,
                'title' => get_the_title($post),
                'eyebrow' => (string) get_post_meta($post->ID, 'jp_eyebrow', true),
                'body' => (string) get_post_meta($post->ID, 'jp_body', true),
                'cta_label' => (string) get_post_meta($post->ID, 'jp_cta_label', true),
                'image_url' => $mediaId > 0 ? (string) wp_get_attachment_image_url($mediaId, 'jp-product-gallery') : '',
                'image_alt' => $mediaId > 0 ? (string) get_post_meta($mediaId, '_wp_attachment_image_alt', true) : '',
                'link_url' => $this->resolveLink($type, $reference, (string) get_post_meta($post->ID, 'jp_link_url', true)),
            ];
        }

        wp_reset_postdata();
        return $blocks;
    }

    private function resolveLink(string $type, string $reference, string $override): string
    {
        if ($override !== '') {
            return $override;
        }
        if ($type === 'category' && taxonomy_exists('product_cat')) {
            $term = get_term_by('slug', $reference, 'product_cat');
            $link = $term ? get_term_link($term) : false;
            return is_string($link) ? $link : '';
        }
        if ($type === 'brand' && taxonomy_exists('jp_brand')) {
            $term = get_term_by('slug', $reference, 'jp_brand');
            $link = $term ? get_term_link($term) : false;
            return is_string($link) ? $link : '';
        }
        if ($type === 'product' && function_exists('wc_get_product_id_by_sku')) {
            $productId = (int) wc_get_product_id_by_sku($reference);
            return $productId > 0 ? (string) get_permalink($productId) : '';
        }

        return '';
    }

    private function referenceExists(string $type, string $reference): bool
    {
        if (! MerchandisingBlock::requiresReference($type)) {
            return true;
        }
        if ($reference === '') {
            return false;
        }
        if ($type === 'category' && taxonomy_exists('product_cat')) {
            return get_term_by('slug', $reference, 'product_cat') !== false;
        }
        if ($type === 'brand' && taxonomy_exists('jp_brand')) {
            return get_term_by('slug', $reference, 'jp_brand') !== false;
        }
        if ($type === 'product' && function_exists('wc_get_product_id_by_sku')) {
            return (int) wc_get_product_id_by_sku($reference) > 0;
        }

        return false;
    }

    /** @param array<string, string> $columns */
    public function columns(array $columns): array
    {
        $columns['jp_type'] = __('Type', 'jouvence-para-core');
        $columns['jp_enabled'] = __('Homepage', 'jouvence-para-core');
        return $columns;
    }

    public function columnValue(string $column, int $postId): void
    {
        if ($column === 'jp_type') {
            echo esc_html(ucfirst(MerchandisingBlock::validType((string) get_post_meta($postId, 'jp_block_type', true))));
        }
        if ($column === 'jp_enabled') {
            echo esc_html((bool) get_post_meta($postId, 'jp_enabled', true) ? __('Enabled', 'jouvence-para-core') : __('Disabled', 'jouvence-para-core'));
        }
    }

}
