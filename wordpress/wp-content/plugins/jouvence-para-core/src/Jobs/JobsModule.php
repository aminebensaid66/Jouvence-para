<?php

declare(strict_types=1);

namespace JouvencePara\Core\Jobs;

use JouvencePara\Core\Contracts\Module;

final class JobsModule implements Module
{
    private const ADMIN_PAGE = 'jp-background-jobs';
    private const RETRY_ACTION = 'jp_retry_failed_job';

    public function __construct(private readonly JobQueue $queue = new JobQueue())
    {
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'registerAdminPage']);
        add_action('admin_post_' . self::RETRY_ACTION, [$this, 'retryFailedAction']);
    }

    public function queue(): JobQueue
    {
        return $this->queue;
    }

    public function registerAdminPage(): void
    {
        add_management_page(
            __('Background jobs', 'jouvence-para-core'),
            __('Background jobs', 'jouvence-para-core'),
            'manage_woocommerce',
            self::ADMIN_PAGE,
            [$this, 'renderAdminPage']
        );
    }

    public function renderAdminPage(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You are not allowed to manage background jobs.', 'jouvence-para-core'));
        }

        $failedJobs = $this->queue->failedJobs();
        $retryStatus = isset($_GET['retried']) && is_scalar($_GET['retried'])
            ? sanitize_text_field((string) wp_unslash($_GET['retried']))
            : '';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Background jobs', 'jouvence-para-core'); ?></h1>
            <p><?php esc_html_e('Failed jobs are listed here. Review their Scheduled Actions log before retrying.', 'jouvence-para-core'); ?></p>
            <?php if ($retryStatus !== '') : ?>
                <div class="notice notice-<?php echo $retryStatus === '1' ? 'success' : 'error'; ?> is-dismissible">
                    <p><?php echo $retryStatus === '1'
                        ? esc_html__('The failed job was queued for another attempt.', 'jouvence-para-core')
                        : esc_html__('The failed job could not be queued. Refresh the page and review its status.', 'jouvence-para-core'); ?></p>
                </div>
            <?php endif; ?>
            <?php if ($failedJobs === []) : ?>
                <p><?php esc_html_e('There are no failed Jouvence Para jobs.', 'jouvence-para-core'); ?></p>
            <?php else : ?>
                <table class="widefat striped">
                    <thead><tr>
                        <th scope="col"><?php esc_html_e('Action ID', 'jouvence-para-core'); ?></th>
                        <th scope="col"><?php esc_html_e('Job', 'jouvence-para-core'); ?></th>
                        <th scope="col"><?php esc_html_e('Failed attempt', 'jouvence-para-core'); ?></th>
                        <th scope="col"><?php esc_html_e('Recovery', 'jouvence-para-core'); ?></th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($failedJobs as $job) : ?>
                        <tr>
                            <td><?php echo esc_html((string) $job['id']); ?></td>
                            <td><?php echo esc_html($job['job']); ?></td>
                            <td><?php echo esc_html((string) $job['attempt']); ?></td>
                            <td>
                                <?php if ($job['recoverable']) : ?>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                        <input type="hidden" name="action" value="<?php echo esc_attr(self::RETRY_ACTION); ?>">
                                        <input type="hidden" name="action_id" value="<?php echo esc_attr((string) $job['id']); ?>">
                                        <?php wp_nonce_field(self::RETRY_ACTION . '_' . $job['id']); ?>
                                        <button type="submit" class="button"><?php esc_html_e('Retry from first attempt', 'jouvence-para-core'); ?></button>
                                    </form>
                                <?php else : ?>
                                    <?php esc_html_e('Handler unavailable or action data is invalid', 'jouvence-para-core'); ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    public function retryFailedAction(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('You are not allowed to manage background jobs.', 'jouvence-para-core'));
        }

        $rawActionId = isset($_POST['action_id']) ? wp_unslash($_POST['action_id']) : 0;
        $actionId = is_scalar($rawActionId) ? absint($rawActionId) : 0;
        check_admin_referer(self::RETRY_ACTION . '_' . $actionId);

        $retried = $this->queue->retryFailed($actionId);
        $url = add_query_arg(
            ['page' => self::ADMIN_PAGE, 'retried' => $retried ? '1' : '0'],
            admin_url('tools.php')
        );
        wp_safe_redirect($url);
        exit;
    }
}
