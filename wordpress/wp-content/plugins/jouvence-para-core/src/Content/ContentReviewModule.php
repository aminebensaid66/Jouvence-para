<?php

declare(strict_types=1);

namespace JouvencePara\Core\Content;

use JouvencePara\Core\Catalog\ProductAdminPolicy;
use JouvencePara\Core\Contracts\Module;
use WC_Admin_Meta_Boxes;
use WC_Product;

final class ContentReviewModule implements Module
{
    public const META_APPROVED = '_jp_content_review_approved';
    public const META_REVIEWER = '_jp_content_reviewer_id';
    public const META_REVIEWED_AT = '_jp_content_reviewed_at';
    private const NONCE_ACTION = 'jp_content_review';
    private const NONCE_FIELD = 'jp_content_review_nonce';

    public function register(): void
    {
        add_action('add_meta_boxes_product', [$this, 'addMetaBox']);
        add_action('woocommerce_admin_process_product_object', [$this, 'saveReview'], 35);
        add_action('woocommerce_before_product_object_save', [$this, 'enforcePublication'], 25);
    }

    public function addMetaBox(): void
    {
        add_meta_box(
            'jp-content-review',
            __('Jouvence Para — revue du contenu', 'jouvence-para-core'),
            [$this, 'renderMetaBox'],
            'product',
            'side',
            'default'
        );
    }

    public function renderMetaBox(object $post): void
    {
        $productId = (int) ($post->ID ?? 0);
        if ($productId <= 0 || ! current_user_can('edit_post', $productId)) {
            return;
        }
        wp_nonce_field(self::NONCE_ACTION, self::NONCE_FIELD);
        $approved = get_post_meta($productId, self::META_APPROVED, true) === '1';
        $reviewerId = (int) get_post_meta($productId, self::META_REVIEWER, true);
        ?>
        <p><?php esc_html_e('La publication exige une source fabricant/fournisseur autorisée et une revue humaine.', 'jouvence-para-core'); ?></p>
        <label><input type="checkbox" name="jp_content_review_approved" value="1" <?php checked($approved); ?>> <?php esc_html_e('J’ai vérifié la source, l’usage, les précautions et l’absence de diagnostic/promesse de guérison.', 'jouvence-para-core'); ?></label>
        <?php if ($reviewerId > 0) : ?><p class="description"><?php echo esc_html(sprintf(__('Dernier réviseur : utilisateur #%d', 'jouvence-para-core'), $reviewerId)); ?></p><?php endif; ?>
        <?php
    }

    public function saveReview(WC_Product $product): void
    {
        $productId = $product->get_id();
        if ($productId <= 0 || ! current_user_can('edit_post', $productId) || ! current_user_can('publish_products')) {
            return;
        }
        $nonce = isset($_POST[self::NONCE_FIELD]) ? sanitize_text_field(wp_unslash((string) $_POST[self::NONCE_FIELD])) : '';
        if ($nonce === '' || ! wp_verify_nonce($nonce, self::NONCE_ACTION)) {
            return;
        }
        $approved = isset($_POST['jp_content_review_approved']);
        $product->update_meta_data(self::META_APPROVED, $approved ? '1' : '0');
        if ($approved) {
            $product->update_meta_data(self::META_REVIEWER, get_current_user_id());
            $product->update_meta_data(self::META_REVIEWED_AT, gmdate('c'));
        } else {
            $product->delete_meta_data(self::META_REVIEWER);
            $product->delete_meta_data(self::META_REVIEWED_AT);
        }
    }

    public function enforcePublication(WC_Product $product): void
    {
        if (! in_array($product->get_status('edit'), ['publish', 'future'], true)) {
            return;
        }
        $source = (string) $product->get_meta(ProductAdminPolicy::SOURCE_REFERENCE, true, 'edit');
        $approved = $product->get_meta(self::META_APPROVED, true, 'edit') === '1';
        $reviewerId = (int) $product->get_meta(self::META_REVIEWER, true, 'edit');
        $content = $product->get_name('edit') . "\n" . $product->get_short_description('edit') . "\n" . $product->get_description('edit');
        if (ContentClaimPolicy::publishReady($source, $approved, $reviewerId, $content)) {
            return;
        }
        $product->set_status('draft');
        if (class_exists(WC_Admin_Meta_Boxes::class)) {
            WC_Admin_Meta_Boxes::add_error(__('Publication bloquée : source autorisée, revue humaine et règles de contenu santé requises.', 'jouvence-para-core'));
        }
    }
}
