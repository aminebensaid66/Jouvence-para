<?php

declare(strict_types=1);

namespace JouvencePara\Core\Customers;

use JouvencePara\Core\Contracts\Module;
use Throwable;
use WC_Customer;
use WP_Error;
use WP_Post;

final class AccountModule implements Module
{
    private const VERSION_OPTION = 'jp_account_settings_version';
    /** @var array<int, string> */
    private array $validatedChoices = [];

    public function register(): void
    {
        add_action('admin_init', [$this, 'configureWooCommerce']);
        add_action('woocommerce_edit_account_form', [$this, 'renderPreferences']);
        add_action('woocommerce_save_account_details_errors', [$this, 'validatePreferences'], 10, 2);
        add_action('woocommerce_save_account_details', [$this, 'savePreferences']);
    }

    public function configureWooCommerce(): void
    {
        if (! current_user_can('manage_options') || get_option(self::VERSION_OPTION, '') === '1') {
            return;
        }
        if (wc_get_page_id('myaccount') < 1 && function_exists('wc_create_page')) {
            wc_create_page('mon-compte', 'woocommerce_myaccount_page_id', 'Mon compte', '[woocommerce_my_account]');
        }
        $pageId = wc_get_page_id('myaccount');
        $page = $pageId > 0 ? get_post($pageId) : null;
        if (! $page instanceof WP_Post || $page->post_type !== 'page' || $page->post_status !== 'publish'
            || ! has_shortcode($page->post_content, 'woocommerce_my_account')) {
            do_action('jouvence_para_account_configuration_error', 'account_page');
            return;
        }
        foreach (['woocommerce_enable_myaccount_registration' => 'yes', 'woocommerce_enable_guest_checkout' => 'yes'] as $key => $value) {
            if (! update_option($key, $value, false) && get_option($key, null) !== $value) {
                do_action('jouvence_para_account_configuration_error', 'account_settings');
                return;
            }
        }
        update_option(self::VERSION_OPTION, '1', false);
    }

    public function renderPreferences(): void
    {
        $id = get_current_user_id();
        if ($id < 1) {
            return;
        }
        $customer = new WC_Customer($id);
        $enabled = CommunicationPreferences::emailEnabled($customer->get_meta(CommunicationPreferences::META_KEY));
        echo '<fieldset><legend>' . esc_html__('Préférences de communication', 'jouvence-para-core') . '</legend>';
        echo '<p>' . esc_html($enabled ? __('Votre choix actuel : offres par e-mail autorisées.', 'jouvence-para-core') : __('Votre choix actuel : pas d’offres par e-mail.', 'jouvence-para-core')) . '</p>';
        woocommerce_form_field('jp_email_marketing_choice', [
            'type' => 'select',
            'label' => __('Offres de Jouvence Para par e-mail', 'jouvence-para-core'),
            'options' => [
                'keep' => __('Conserver mon choix actuel', 'jouvence-para-core'),
                'enable' => __('J’accepte de recevoir des offres par e-mail', 'jouvence-para-core'),
                'disable' => __('Ne pas recevoir d’offres par e-mail', 'jouvence-para-core'),
            ],
            'description' => __('Vous pouvez modifier ce choix à tout moment. Les messages liés à vos commandes et à la sécurité restent séparés.', 'jouvence-para-core'),
        ], 'keep');
        echo '<input type="hidden" name="jp_preferences_present" value="1">';
        wp_nonce_field('jp_account_preferences_' . $id, 'jp_account_preferences_nonce');
        echo '</fieldset>';
    }

    public function validatePreferences(WP_Error $errors, object $user): void
    {
        $id = (int) ($user->ID ?? 0);
        unset($this->validatedChoices[$id]);
        if (! array_key_exists('jp_preferences_present', $_POST) && ! array_key_exists('jp_email_marketing_choice', $_POST)
            && ! array_key_exists('jp_account_preferences_nonce', $_POST)) {
            return;
        }
        $nonce = $_POST['jp_account_preferences_nonce'] ?? null;
        $nativeNonce = $_POST['save-account-details-nonce'] ?? $_POST['_wpnonce'] ?? null;
        $choice = $_POST['jp_email_marketing_choice'] ?? null;
        if ($id < 1 || $id !== get_current_user_id() || ($_POST['action'] ?? null) !== 'save_account_details'
            || ($_POST['jp_preferences_present'] ?? null) !== '1' || ! is_string($choice)
            || ! in_array($choice, ['keep', 'enable', 'disable'], true) || ! is_string($nonce) || ! is_string($nativeNonce)
            || ! wp_verify_nonce(wp_unslash($nonce), 'jp_account_preferences_' . $id)
            || ! wp_verify_nonce(wp_unslash($nativeNonce), 'save_account_details')) {
            $errors->add('jp_preferences_invalid', __('Vos préférences n’ont pas pu être vérifiées. Rechargez la page et réessayez.', 'jouvence-para-core'));
            return;
        }
        // Authorize before native password saves can rotate the current login session/nonces.
        $this->validatedChoices[$id] = $choice;
    }

    public function savePreferences(int $id): void
    {
        $choice = $this->validatedChoices[$id] ?? 'keep';
        unset($this->validatedChoices[$id]);
        if ($id < 1 || $id !== get_current_user_id() || $choice === 'keep' || wc_notice_count('error') > 0) {
            return;
        }
        try {
            $customer = new WC_Customer($id);
            $record = $customer->get_meta(CommunicationPreferences::META_KEY);
            $enabled = $choice === 'enable';
            if (CommunicationPreferences::valid($record) && $record['email_marketing'] === $enabled) {
                return;
            }
            $updated = ['version' => 1, 'email_marketing' => $enabled, 'updated_at' => gmdate('Y-m-d\TH:i:s\Z')];
            $customer->update_meta_data(CommunicationPreferences::META_KEY, $updated);
            $customer->save_meta_data();
            if ((new WC_Customer($id))->get_meta(CommunicationPreferences::META_KEY) === $updated) {
                return;
            }
        } catch (Throwable) {
            // Never expose or log customer fields or exception/provider messages.
        }
        wc_add_notice(__('Vos préférences n’ont pas pu être enregistrées. Réessayez.', 'jouvence-para-core'), 'error');
    }
}
