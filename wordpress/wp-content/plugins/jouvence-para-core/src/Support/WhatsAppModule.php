<?php

declare(strict_types=1);

namespace JouvencePara\Core\Support;

use JouvencePara\Core\Contracts\Module;

final class WhatsAppModule implements Module
{
    private const HOURS_OPTION = 'jp_whatsapp_hours';
    private const RESPONSE_OPTION = 'jp_whatsapp_response_note';

    public function __construct(private readonly WhatsAppService $service = new WhatsAppService())
    {
    }

    public function register(): void
    {
        add_filter('jouvence_para_whatsapp_url', [$this, 'whatsAppUrl'], 10, 2);
        add_filter('jouvence_para_whatsapp_hours', [$this, 'hours']);
        add_filter('jouvence_para_whatsapp_response_note', [$this, 'responseNote']);
        add_action('admin_init', [$this, 'settings']);
        add_action('admin_menu', [$this, 'menu']);
    }

    public function whatsAppUrl(string $url, mixed $product = null): string
    {
        unset($url);
        if (is_object($product) && method_exists($product, 'get_name') && method_exists($product, 'get_id')) {
            return $this->service->productUrl((string) $product->get_name(), (string) get_permalink((int) $product->get_id()));
        }
        return $this->service->globalUrl();
    }

    public function hours(string $hours = ''): string
    {
        unset($hours);
        return (string) get_option(self::HOURS_OPTION, 'Horaires de réponse indiqués par la boutique');
    }

    public function responseNote(string $note = ''): string
    {
        unset($note);
        return (string) get_option(self::RESPONSE_OPTION, 'Nous vous répondons pendant les heures d’ouverture.');
    }

    public function settings(): void
    {
        register_setting('jp_support', self::HOURS_OPTION, [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'Horaires de réponse indiqués par la boutique',
        ]);
        register_setting('jp_support', self::RESPONSE_OPTION, [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'Nous vous répondons pendant les heures d’ouverture.',
        ]);
    }

    public function menu(): void
    {
        add_options_page(
            __('Support Jouvence Para', 'jouvence-para-core'),
            __('Support Jouvence Para', 'jouvence-para-core'),
            'manage_options',
            'jp-support-settings',
            [$this, 'page']
        );
    }

    public function page(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('Accès refusé.', 'jouvence-para-core'), '', ['response' => 403]);
        }
        ?>
        <div class="wrap"><h1><?php esc_html_e('Support Jouvence Para', 'jouvence-para-core'); ?></h1>
        <p><?php echo esc_html(sprintf(__('Numéro WhatsApp officiel : %s', 'jouvence-para-core'), WhatsAppService::DISPLAY_NUMBER)); ?></p>
        <form method="post" action="options.php"><?php settings_fields('jp_support'); ?>
        <table class="form-table"><tr><th><label for="jp_whatsapp_hours"><?php esc_html_e('Horaires', 'jouvence-para-core'); ?></label></th><td><input class="regular-text" id="jp_whatsapp_hours" name="jp_whatsapp_hours" value="<?php echo esc_attr($this->hours()); ?>"></td></tr>
        <tr><th><label for="jp_whatsapp_response_note"><?php esc_html_e('Attente de réponse', 'jouvence-para-core'); ?></label></th><td><input class="regular-text" id="jp_whatsapp_response_note" name="jp_whatsapp_response_note" value="<?php echo esc_attr($this->responseNote()); ?>"></td></tr></table>
        <?php submit_button(); ?></form></div>
        <?php
    }
}
