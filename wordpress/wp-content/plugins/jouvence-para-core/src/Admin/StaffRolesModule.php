<?php

declare(strict_types=1);

namespace JouvencePara\Core\Admin;

use JouvencePara\Core\Contracts\Module;

final class StaffRolesModule implements Module
{
    private const VERSION_OPTION = 'jp_staff_roles_version';
    private const VERSION = '1';

    public function register(): void
    {
        add_action('admin_init', [$this, 'installRoles']);
        add_filter('user_has_cap', [$this, 'stripForbiddenCapabilities'], 20, 4);
        add_action('admin_init', [$this, 'guardRefundActions'], 1);
    }

    public function installRoles(): void
    {
        if (! current_user_can('administrator') && ! current_user_can('manage_options')) {
            return;
        }
        if (get_option(self::VERSION_OPTION, '') === self::VERSION) {
            return;
        }

        foreach (RolePolicy::definitions() as $slug => $definition) {
            $role = get_role($slug);
            if ($role === null) {
                add_role($slug, __($definition['name'], 'jouvence-para-core'), $definition['capabilities']);
                $role = get_role($slug);
            }
            if ($role === null) {
                do_action('jouvence_para_staff_role_error', $slug);
                return;
            }

            foreach ($definition['capabilities'] as $capability => $grant) {
                $grant ? $role->add_cap($capability) : $role->remove_cap($capability);
            }
            foreach (RolePolicy::forbiddenAdministrativeCapabilities() as $capability) {
                $role->remove_cap($capability);
            }
        }

        update_option(self::VERSION_OPTION, self::VERSION, false);
    }

    /** @param array<string, bool> $allcaps @param array<string, bool> $caps @param array<int|string, mixed> $args @param object $user */
    public function stripForbiddenCapabilities(array $allcaps, array $caps, array $args, object $user): array
    {
        unset($caps, $args);
        $roles = isset($user->roles) && is_array($user->roles) ? $user->roles : [];
        if (! array_filter($roles, [RolePolicy::class, 'isJouvenceStaffRole'])) {
            return $allcaps;
        }

        foreach (RolePolicy::forbiddenAdministrativeCapabilities() as $capability) {
            unset($allcaps[$capability]);
        }
        return $allcaps;
    }

    public function guardRefundActions(): void
    {
        if (! wp_doing_ajax()) {
            return;
        }
        $action = isset($_REQUEST['action']) ? sanitize_key((string) wp_unslash($_REQUEST['action'])) : '';
        if (! in_array($action, ['woocommerce_refund_line_items', 'woocommerce_delete_refund'], true)) {
            return;
        }
        $user = wp_get_current_user();
        if (! array_filter((array) $user->roles, [RolePolicy::class, 'isJouvenceStaffRole'])) {
            return;
        }
        if (! current_user_can('jp_process_refunds')) {
            wp_die(
                esc_html__('Les remboursements sont réservés à un administrateur autorisé.', 'jouvence-para-core'),
                '',
                ['response' => 403]
            );
        }
    }
}
