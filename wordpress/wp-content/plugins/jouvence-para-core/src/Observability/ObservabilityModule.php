<?php

declare(strict_types=1);

namespace JouvencePara\Core\Observability;

use JouvencePara\Core\Contracts\Module;
use WP_REST_Response;

final class ObservabilityModule implements Module
{
    private float $startedAt;

    public function __construct(
        private readonly DailyMetrics $metrics = new DailyMetrics(),
        private readonly Logger $logger = new Logger()
    ) {
        $this->startedAt = isset($_SERVER['REQUEST_TIME_FLOAT'])
            ? (float) $_SERVER['REQUEST_TIME_FLOAT']
            : microtime(true);
    }

    public function register(): void
    {
        add_action('rest_api_init', [$this, 'registerHealthRoute']);
        add_action('admin_menu', [$this, 'registerAdminPage']);
        add_filter('site_status_tests', [$this, 'registerSiteHealthTest']);
        register_shutdown_function([$this, 'recordRequest']);
    }

    public function registerHealthRoute(): void
    {
        register_rest_route(
            'jouvence-para/v1',
            '/health',
            [
                'methods' => 'GET',
                'callback' => [$this, 'healthResponse'],
                'permission_callback' => '__return_true',
            ]
        );
    }

    public function healthResponse(): WP_REST_Response
    {
        return new WP_REST_Response(
            [
                'status' => 'ok',
                'service' => 'jouvence-para',
                'release' => JOUVENCE_PARA_CORE_VERSION,
                'timestamp' => gmdate('c'),
            ],
            200,
            ['Cache-Control' => 'no-store']
        );
    }

    public function recordRequest(): void
    {
        $status = http_response_code();
        if ($status === false || $status < 100) {
            $status = 200;
        }

        $durationMs = (microtime(true) - $this->startedAt) * 1000;
        $this->metrics->record($status, $durationMs);

        $error = error_get_last();
        if (! is_array($error) || ! in_array($error['type'] ?? 0, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            return;
        }

        $this->logger->error(
            'php_fatal',
            [
                'type' => (int) ($error['type'] ?? 0),
                'message' => Redactor::text((string) ($error['message'] ?? '')),
                'file' => basename((string) ($error['file'] ?? '')),
                'line' => (int) ($error['line'] ?? 0),
                'route' => Redactor::text((string) ($_SERVER['REQUEST_URI'] ?? '')),
            ]
        );
    }

    public function registerAdminPage(): void
    {
        add_management_page(
            __('Jouvence Para diagnostics', 'jouvence-para-core'),
            __('Jouvence Para diagnostics', 'jouvence-para-core'),
            'manage_options',
            'jouvence-para-diagnostics',
            [$this, 'renderAdminPage']
        );
    }

    public function renderAdminPage(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to view diagnostics.', 'jouvence-para-core'));
        }

        global $wpdb;
        $metrics = $this->metrics->current();
        $dbHealthy = method_exists($wpdb, 'check_connection') ? (bool) $wpdb->check_connection(false) : true;
        $diskFree = @disk_free_space(ABSPATH);
        $diskTotal = @disk_total_space(ABSPATH);
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Jouvence Para diagnostics', 'jouvence-para-core'); ?></h1>
            <table class="widefat striped" role="presentation">
                <tbody>
                    <tr><th><?php esc_html_e('Requests today', 'jouvence-para-core'); ?></th><td><?php echo esc_html((string) $metrics['requests']); ?></td></tr>
                    <tr><th><?php esc_html_e('5xx rate today', 'jouvence-para-core'); ?></th><td><?php echo esc_html(number_format_i18n(MetricsSummary::serverErrorRate($metrics), 2) . '%'); ?></td></tr>
                    <tr><th><?php esc_html_e('Average request time', 'jouvence-para-core'); ?></th><td><?php echo esc_html(number_format_i18n(MetricsSummary::averageMs($metrics), 1) . ' ms'); ?></td></tr>
                    <tr><th><?php esc_html_e('Maximum request time', 'jouvence-para-core'); ?></th><td><?php echo esc_html(number_format_i18n($metrics['max_ms'], 1) . ' ms'); ?></td></tr>
                    <tr><th><?php esc_html_e('Database', 'jouvence-para-core'); ?></th><td><?php echo esc_html($dbHealthy ? __('Connected', 'jouvence-para-core') : __('Unavailable', 'jouvence-para-core')); ?></td></tr>
                    <tr><th><?php esc_html_e('External object cache', 'jouvence-para-core'); ?></th><td><?php echo esc_html(wp_using_ext_object_cache() ? __('Active', 'jouvence-para-core') : __('Not active', 'jouvence-para-core')); ?></td></tr>
                    <tr><th><?php esc_html_e('PHP peak memory', 'jouvence-para-core'); ?></th><td><?php echo esc_html(size_format(memory_get_peak_usage(true))); ?></td></tr>
                    <tr><th><?php esc_html_e('Disk free', 'jouvence-para-core'); ?></th><td><?php echo esc_html(is_float($diskFree) && is_float($diskTotal) ? size_format($diskFree) . ' / ' . size_format($diskTotal) : __('Unavailable', 'jouvence-para-core')); ?></td></tr>
                </tbody>
            </table>
        </div>
        <?php
    }

    /** @param array<string, mixed> $tests */
    public function registerSiteHealthTest(array $tests): array
    {
        $tests['direct']['jouvence_para_observability'] = [
            'label' => __('Jouvence Para observability', 'jouvence-para-core'),
            'test' => [$this, 'siteHealthResult'],
        ];

        return $tests;
    }

    /** @return array<string, mixed> */
    public function siteHealthResult(): array
    {
        $metrics = $this->metrics->current();
        $errorRate = MetricsSummary::serverErrorRate($metrics);
        $status = $errorRate >= 5.0 && $metrics['requests'] >= 20 ? 'critical' : 'good';

        return [
            'label' => $status === 'good'
                ? __('Jouvence Para request health is within baseline', 'jouvence-para-core')
                : __('Jouvence Para is reporting an elevated 5xx rate', 'jouvence-para-core'),
            'status' => $status,
            'badge' => ['label' => 'Jouvence Para', 'color' => 'blue'],
            'description' => sprintf(
                '<p>%s</p>',
                esc_html(sprintf(__('Today\'s 5xx rate is %.2f%% across %d recorded requests.', 'jouvence-para-core'), $errorRate, $metrics['requests']))
            ),
            'actions' => '',
            'test' => 'jouvence_para_observability',
        ];
    }
}
