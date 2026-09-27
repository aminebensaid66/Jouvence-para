<?php

declare(strict_types=1);

namespace JouvencePara\Core\Security;

use JouvencePara\Core\Admin\RolePolicy;
use JouvencePara\Core\Contracts\Module;
use WP_Error;
use WP_User;

final class SecurityModule implements Module
{
    private const META_SECRET = 'jp_2fa_secret';
    private const META_PENDING = 'jp_2fa_pending_secret';
    private const META_ENABLED = 'jp_2fa_enabled';
    private const LOGIN_MAX_ATTEMPTS = 10;
    private const TOTP_MAX_ATTEMPTS = 5;
    private const THROTTLE_SECONDS = 900;

    public function register(): void
    {
        if (! defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }
        add_action('login_form', [$this, 'renderLoginField']);
        add_filter('wp_authenticate_user', [$this, 'requireTwoFactorCode'], 20, 2);
        add_filter('authenticate', [$this, 'enforceLoginThrottle'], 5, 3);
        add_action('wp_login_failed', [$this, 'recordLoginFailure'], 10, 2);
        add_action('wp_login', [$this, 'clearLoginThrottle'], 10, 2);
        add_action('show_user_profile', [$this, 'renderProfile']);
        add_action('admin_post_jp_2fa_generate', [$this, 'generateSecret']);
        add_action('admin_post_jp_2fa_enable', [$this, 'enableTwoFactor']);
        add_action('admin_post_jp_2fa_disable', [$this, 'disableTwoFactor']);
        add_action('admin_init', [$this, 'enforceEnrollment']);
        add_filter('xmlrpc_enabled', [$this, 'disableXmlRpc']);
        add_filter('file_mod_allowed', [$this, 'disallowFileEditors'], 20, 2);
        add_action('user_register', [$this, 'monitorPrivilegedUser'], 20, 2);
        add_action('set_user_role', [$this, 'monitorRoleChange'], 20, 3);
    }

    public function disableXmlRpc(bool $enabled): bool
    {
        unset($enabled);
        return false;
    }

    public function disallowFileEditors(bool $allowed, string $context): bool
    {
        if ($context === 'capability_edit_themes') {
            return false;
        }
        return $allowed;
    }

    public function renderLoginField(): void
    {
        ?>
        <p>
            <label for="jp_totp"><?php esc_html_e('Code de sécurité à 6 chiffres', 'jouvence-para-core'); ?></label>
            <input type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" name="jp_totp" id="jp_totp" class="input" value="">
        </p>
        <?php
    }

    public function enforceLoginThrottle(mixed $user, string $username, string $password): mixed
    {
        unset($password);
        if ($username === '') {
            return $user;
        }
        $attempts = (int) get_transient(SecurityPolicy::loginThrottleKey($username, $this->remoteIp()));
        if ($attempts >= self::LOGIN_MAX_ATTEMPTS) {
            return new WP_Error('jp_login_throttled', __('Trop de tentatives. Réessayez dans quelques minutes.', 'jouvence-para-core'));
        }
        return $user;
    }

    public function recordLoginFailure(string $username, ?WP_Error $error = null): void
    {
        unset($error);
        if ($username === '') {
            return;
        }
        $key = SecurityPolicy::loginThrottleKey($username, $this->remoteIp());
        $attempts = min(self::LOGIN_MAX_ATTEMPTS, ((int) get_transient($key)) + 1);
        set_transient($key, $attempts, self::THROTTLE_SECONDS);
    }

    public function clearLoginThrottle(string $userLogin, WP_User $user): void
    {
        unset($user);
        delete_transient(SecurityPolicy::loginThrottleKey($userLogin, $this->remoteIp()));
    }

    public function requireTwoFactorCode(mixed $user, string $password): mixed
    {
        unset($password);
        if (! $user instanceof WP_User || ! SecurityPolicy::requiresTwoFactor((array) $user->roles)) {
            return $user;
        }
        if (get_user_meta($user->ID, self::META_ENABLED, true) !== '1') {
            return $user;
        }

        $key = SecurityPolicy::twoFactorThrottleKey($user->ID);
        if ((int) get_transient($key) >= self::TOTP_MAX_ATTEMPTS) {
            return new WP_Error('jp_2fa_throttled', __('Trop de codes incorrects. Réessayez dans quelques minutes.', 'jouvence-para-core'));
        }
        $code = isset($_POST['jp_totp']) ? sanitize_text_field(wp_unslash((string) $_POST['jp_totp'])) : '';
        $secret = (string) get_user_meta($user->ID, self::META_SECRET, true);
        if (! Totp::verify($secret, $code)) {
            set_transient($key, min(self::TOTP_MAX_ATTEMPTS, ((int) get_transient($key)) + 1), self::THROTTLE_SECONDS);
            return new WP_Error('jp_2fa_invalid', __('Code de sécurité invalide.', 'jouvence-para-core'));
        }
        delete_transient($key);
        return $user;
    }

    public function enforceEnrollment(): void
    {
        if (! is_user_logged_in() || wp_doing_ajax()) {
            return;
        }
        $user = wp_get_current_user();
        if (! SecurityPolicy::requiresTwoFactor((array) $user->roles)) {
            return;
        }
        if (get_user_meta($user->ID, self::META_ENABLED, true) === '1') {
            return;
        }
        global $pagenow;
        if (in_array((string) $pagenow, ['profile.php', 'admin-post.php'], true)) {
            return;
        }
        wp_safe_redirect(admin_url('profile.php#jp-two-factor'));
        exit;
    }

    public function renderProfile(WP_User $user): void
    {
        if ($user->ID !== get_current_user_id() || ! SecurityPolicy::requiresTwoFactor((array) $user->roles)) {
            return;
        }
        $enabled = get_user_meta($user->ID, self::META_ENABLED, true) === '1';
        $pending = (string) get_user_meta($user->ID, self::META_PENDING, true);
        ?>
        <h2 id="jp-two-factor"><?php esc_html_e('Authentification à deux facteurs', 'jouvence-para-core'); ?></h2>
        <p><?php echo esc_html($enabled ? __('La double authentification est active.', 'jouvence-para-core') : __('La double authentification doit être activée avant d’utiliser l’administration.', 'jouvence-para-core')); ?></p>
        <?php if (! $enabled && $pending === '') : ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="jp_2fa_generate">
                <?php wp_nonce_field('jp_2fa_generate'); ?>
                <?php submit_button(__('Générer une clé 2FA', 'jouvence-para-core'), 'secondary', 'submit', false); ?>
            </form>
        <?php elseif (! $enabled) : ?>
            <p><strong><?php esc_html_e('Clé à saisir dans l’application d’authentification :', 'jouvence-para-core'); ?></strong> <code><?php echo esc_html($pending); ?></code></p>
            <p><code><?php echo esc_html(Totp::uri($pending, $user->user_login)); ?></code></p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="jp_2fa_enable">
                <?php wp_nonce_field('jp_2fa_enable'); ?>
                <label for="jp_totp_confirm"><?php esc_html_e('Code actuel', 'jouvence-para-core'); ?></label>
                <input id="jp_totp_confirm" name="jp_totp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required>
                <?php submit_button(__('Activer 2FA', 'jouvence-para-core'), 'primary', 'submit', false); ?>
            </form>
        <?php else : ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="jp_2fa_disable">
                <?php wp_nonce_field('jp_2fa_disable'); ?>
                <label for="jp_totp_disable"><?php esc_html_e('Code actuel pour désactiver', 'jouvence-para-core'); ?></label>
                <input id="jp_totp_disable" name="jp_totp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required>
                <?php submit_button(__('Désactiver 2FA', 'jouvence-para-core'), 'secondary', 'submit', false); ?>
            </form>
        <?php endif; ?>
        <?php
    }

    public function generateSecret(): void
    {
        $user = $this->authorizedCurrentUser('jp_2fa_generate');
        update_user_meta($user->ID, self::META_PENDING, Totp::generateSecret());
        $this->redirectProfile();
    }

    public function enableTwoFactor(): void
    {
        $user = $this->authorizedCurrentUser('jp_2fa_enable');
        $pending = (string) get_user_meta($user->ID, self::META_PENDING, true);
        $code = isset($_POST['jp_totp']) ? sanitize_text_field(wp_unslash((string) $_POST['jp_totp'])) : '';
        if ($pending === '' || ! Totp::verify($pending, $code)) {
            wp_die(esc_html__('Code 2FA invalide.', 'jouvence-para-core'), '', ['response' => 400]);
        }
        update_user_meta($user->ID, self::META_SECRET, $pending);
        update_user_meta($user->ID, self::META_ENABLED, '1');
        delete_user_meta($user->ID, self::META_PENDING);
        $this->redirectProfile();
    }

    public function disableTwoFactor(): void
    {
        $user = $this->authorizedCurrentUser('jp_2fa_disable');
        $secret = (string) get_user_meta($user->ID, self::META_SECRET, true);
        $code = isset($_POST['jp_totp']) ? sanitize_text_field(wp_unslash((string) $_POST['jp_totp'])) : '';
        if (! Totp::verify($secret, $code)) {
            wp_die(esc_html__('Code 2FA invalide.', 'jouvence-para-core'), '', ['response' => 400]);
        }
        delete_user_meta($user->ID, self::META_SECRET);
        delete_user_meta($user->ID, self::META_PENDING);
        delete_user_meta($user->ID, self::META_ENABLED);
        $this->redirectProfile();
    }

    /** @param array<string, mixed> $userdata */
    public function monitorPrivilegedUser(int $userId, array $userdata = []): void
    {
        $role = (string) ($userdata['role'] ?? '');
        if ($role === 'administrator' || $role === RolePolicy::STORE_MANAGER) {
            do_action('jouvence_para_privileged_user_event', 'created', $userId, $role);
        }
    }

    /** @param list<string> $oldRoles */
    public function monitorRoleChange(int $userId, string $role, array $oldRoles): void
    {
        unset($oldRoles);
        if ($role === 'administrator' || $role === RolePolicy::STORE_MANAGER) {
            do_action('jouvence_para_privileged_user_event', 'role_granted', $userId, $role);
        }
    }

    private function authorizedCurrentUser(string $nonceAction): WP_User
    {
        check_admin_referer($nonceAction);
        if (! is_user_logged_in()) {
            wp_die(esc_html__('Authentification requise.', 'jouvence-para-core'), '', ['response' => 403]);
        }
        $user = wp_get_current_user();
        if (! SecurityPolicy::requiresTwoFactor((array) $user->roles)) {
            wp_die(esc_html__('Action non autorisée.', 'jouvence-para-core'), '', ['response' => 403]);
        }
        return $user;
    }

    private function redirectProfile(): never
    {
        wp_safe_redirect(admin_url('profile.php#jp-two-factor'));
        exit;
    }

    private function remoteIp(): string
    {
        $value = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
        return preg_replace('/[^0-9a-fA-F:.]/', '', $value) ?? '';
    }
}
