<?php

declare(strict_types=1);

namespace JouvencePara\Core\Support;

use JouvencePara\Core\Contracts\Module;

final class DeliveryAdviceModule implements Module
{
    private const PAGE_OPTION = 'jp_delivery_policy_page_id';

    public function register(): void
    {
        add_filter('jouvence_para_delivery_advice', [$this, 'advice']);
        add_action('admin_init', [$this, 'ensurePolicyPage']);
        add_shortcode('jouvence_para_delivery_policy', [$this, 'policyShortcode']);
    }

    /** @param array<string, mixed> $advice @return array<string, mixed> */
    public function advice(array $advice = []): array
    {
        return DeliveryAdvicePolicy::publicRules() + [
            'policy_url' => $this->policyUrl(),
        ] + $advice;
    }

    public function ensurePolicyPage(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }
        $pageId = (int) get_option(self::PAGE_OPTION, 0);
        if ($pageId > 0 && get_post_status($pageId) !== false) {
            return;
        }
        $existing = get_page_by_path('livraison');
        if ($existing instanceof \WP_Post) {
            update_option(self::PAGE_OPTION, $existing->ID, false);
            return;
        }
        $result = wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => 'Livraison et retrait',
            'post_name' => 'livraison',
            'post_content' => '[jouvence_para_delivery_policy]',
            'comment_status' => 'closed',
        ], true);
        if (is_wp_error($result)) {
            do_action('jouvence_para_delivery_policy_page_error', $result);
            return;
        }
        update_option(self::PAGE_OPTION, (int) $result, false);
    }

    public function policyShortcode(): string
    {
        return '<section class="jp-delivery-policy">'
            . '<h2>' . esc_html__('Livraison', 'jouvence-para-core') . '</h2>'
            . '<p>' . esc_html__('Livraison nationale : 7,000 TND. Livraison offerte à partir de 200,000 TND après remises.', 'jouvence-para-core') . '</p>'
            . '<p>' . esc_html__('Transporteur par défaut : First Delivery. Traitement transporteur manuel au lancement. Estimation : deux jours ouvrés hors week-ends et jours fériés.', 'jouvence-para-core') . '</p>'
            . '<p>' . esc_html__('Retrait en boutique gratuit.', 'jouvence-para-core') . '</p>'
            . '</section>';
    }

    private function policyUrl(): string
    {
        $pageId = (int) get_option(self::PAGE_OPTION, 0);
        return $pageId > 0 ? (string) get_permalink($pageId) : '';
    }
}
