<?php

declare(strict_types=1);

namespace JouvencePara\Core\Audit;

use JouvencePara\Core\Contracts\Module;
use WP_Post;
use WP_User;

final class AuditModule implements Module
{
    private const PRODUCT_META = [
        '_regular_price' => 'product_price_changed',
        '_sale_price' => 'product_sale_price_changed',
        '_sale_price_dates_from' => 'product_promotion_changed',
        '_sale_price_dates_to' => 'product_promotion_changed',
        '_stock' => 'product_stock_changed',
        '_stock_status' => 'product_stock_status_changed',
    ];

    /** @var array<string, mixed> */
    private array $pendingProductMeta = [];

    public function __construct(private readonly AuditRepository $repository = new AuditRepository())
    {
    }

    public function register(): void
    {
        add_filter('update_post_metadata', [$this, 'postMetaUpdating'], 10, 5);
        add_action('updated_post_meta', [$this, 'postMetaUpdated'], 10, 4);
        add_action('added_post_meta', [$this, 'postMetaAdded'], 10, 4);
        add_action('save_post_shop_coupon', [$this, 'couponSaved'], 10, 3);
        add_action('woocommerce_order_status_changed', [$this, 'orderStatusChanged'], 10, 4);
        add_action('woocommerce_refund_created', [$this, 'refundCreated'], 10, 2);
        add_action('user_register', [$this, 'userRegistered'], 10, 2);
        add_action('profile_update', [$this, 'profileUpdated'], 10, 3);
        add_action('set_user_role', [$this, 'userRoleChanged'], 10, 3);
        add_action('updated_option', [$this, 'optionUpdated'], 10, 3);
        add_action('admin_menu', [$this, 'registerAdminPage']);
    }

    public function postMetaUpdating(
        mixed $check,
        int $objectId,
        string $metaKey,
        mixed $metaValue,
        mixed $previousValue
    ): mixed
    {
        unset($metaValue, $previousValue);
        if (isset(self::PRODUCT_META[$metaKey]) && get_post_type($objectId) === 'product') {
            $this->pendingProductMeta[$objectId . ':' . $metaKey] = get_post_meta($objectId, $metaKey, true);
        }

        return $check;
    }

    public function postMetaUpdated(int $metaId, int $objectId, string $metaKey, mixed $metaValue): void
    {
        unset($metaId);
        $pendingKey = $objectId . ':' . $metaKey;
        $oldValue = $this->pendingProductMeta[$pendingKey] ?? null;
        unset($this->pendingProductMeta[$pendingKey]);
        $this->recordProductMeta($objectId, $metaKey, $oldValue, $metaValue);
    }

    public function postMetaAdded(int $metaId, int $objectId, string $metaKey, mixed $metaValue): void
    {
        unset($metaId);
        $this->recordProductMeta($objectId, $metaKey, null, $metaValue);
    }

    private function recordProductMeta(int $objectId, string $metaKey, mixed $oldValue, mixed $newValue): void
    {
        if (! isset(self::PRODUCT_META[$metaKey]) || get_post_type($objectId) !== 'product') {
            return;
        }

        $this->repository->record(
            self::PRODUCT_META[$metaKey],
            'product',
            $objectId,
            ['field' => $metaKey, 'value' => self::auditValue($oldValue)],
            ['field' => $metaKey, 'value' => self::auditValue($newValue)]
        );
    }

    private static function auditValue(mixed $value): mixed
    {
        if ($value === null || is_scalar($value)) {
            return $value;
        }

        return '[complex]';
    }

    public function couponSaved(int $postId, WP_Post $post, bool $update): void
    {
        if (wp_is_post_revision($postId)) {
            return;
        }

        $this->repository->record(
            $update ? 'coupon_updated' : 'coupon_created',
            'coupon',
            $postId,
            [],
            ['status' => $post->post_status]
        );
    }

    public function orderStatusChanged(int $orderId, string $from, string $to): void
    {
        $this->repository->record(
            'order_status_changed',
            'order',
            $orderId,
            ['status' => $from],
            ['status' => $to]
        );
    }

    public function refundCreated(int $refundId, array $args): void
    {
        $this->repository->record(
            'refund_created',
            'refund',
            $refundId,
            [],
            ['order_id' => (int) ($args['order_id'] ?? 0)]
        );
    }

    /** @param array<string, mixed> $userdata */
    public function userRegistered(int $userId, array $userdata = []): void
    {
        $roles = isset($userdata['role']) ? [(string) $userdata['role']] : [];
        $this->repository->record('user_created', 'user', $userId, [], ['roles' => $roles]);
    }

    /** @param array<string, mixed> $userdata */
    public function profileUpdated(int $userId, WP_User $oldUser, array $userdata = []): void
    {
        unset($userdata);
        $current = get_userdata($userId);
        $this->repository->record(
            'customer_record_updated',
            'user',
            $userId,
            ['roles' => $oldUser->roles],
            ['roles' => $current instanceof WP_User ? $current->roles : []]
        );
    }

    /** @param list<string> $oldRoles */
    public function userRoleChanged(int $userId, string $role, array $oldRoles): void
    {
        $this->repository->record(
            'user_role_changed',
            'user',
            $userId,
            ['roles' => $oldRoles],
            ['roles' => [$role]]
        );
    }

    public function optionUpdated(string $option, mixed $oldValue, mixed $value): void
    {
        if (preg_match('/^woocommerce_.*(?:payment|gateway|shipping|settings)/', $option) !== 1) {
            return;
        }

        unset($oldValue, $value);
        $this->repository->record(
            str_contains($option, 'shipping') ? 'shipping_configuration_changed' : 'payment_configuration_changed',
            'option',
            $option,
            ['changed' => true],
            ['changed' => true]
        );
    }

    public function registerAdminPage(): void
    {
        add_submenu_page(
            'woocommerce',
            __('Audit log', 'jouvence-para-core'),
            __('Audit log', 'jouvence-para-core'),
            'manage_woocommerce',
            'jouvence-para-audit-log',
            [$this, 'renderAdminPage']
        );
    }

    public function renderAdminPage(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You are not allowed to view the audit log.', 'jouvence-para-core'));
        }

        $rows = $this->repository->latest(100);
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Jouvence Para audit log', 'jouvence-para-core'); ?></h1>
            <p><?php esc_html_e('Recent sensitive commerce and administrative changes. Audit entries are append-oriented and intentionally exclude secrets and raw payment data.', 'jouvence-para-core'); ?></p>
            <table class="widefat striped">
                <thead><tr><th><?php esc_html_e('Time (UTC)', 'jouvence-para-core'); ?></th><th><?php esc_html_e('Action', 'jouvence-para-core'); ?></th><th><?php esc_html_e('Object', 'jouvence-para-core'); ?></th><th><?php esc_html_e('Actor', 'jouvence-para-core'); ?></th><th><?php esc_html_e('Environment', 'jouvence-para-core'); ?></th><th><?php esc_html_e('Details', 'jouvence-para-core'); ?></th></tr></thead>
                <tbody>
                    <?php if ($rows === []) : ?>
                        <tr><td colspan="6"><?php esc_html_e('No audit events recorded yet.', 'jouvence-para-core'); ?></td></tr>
                    <?php endif; ?>
                    <?php foreach ($rows as $row) : ?>
                        <tr>
                            <td><?php echo esc_html((string) ($row['occurred_at'] ?? '')); ?></td>
                            <td><?php echo esc_html((string) ($row['action'] ?? '')); ?></td>
                            <td><?php echo esc_html((string) ($row['object_type'] ?? '') . ' #' . (string) ($row['object_id'] ?? '')); ?></td>
                            <td><?php echo esc_html((string) ($row['actor_id'] ?? '0')); ?></td>
                            <td><?php echo esc_html((string) ($row['environment'] ?? '')); ?></td>
                            <td><details><summary><?php esc_html_e('View', 'jouvence-para-core'); ?></summary><pre><?php echo esc_html((string) ($row['before_json'] ?? '{}')); ?> → <?php echo esc_html((string) ($row['after_json'] ?? '{}')); ?></pre></details></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
