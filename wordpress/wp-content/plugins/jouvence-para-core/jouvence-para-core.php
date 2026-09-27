<?php
/**
 * Plugin Name: Jouvence Para Core
 * Description: Business rules and integration boundaries for Jouvence Para.
 * Version: 0.1.0
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * WC requires at least: 9.0
 * Text Domain: jouvence-para-core
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

define('JOUVENCE_PARA_CORE_VERSION', '0.1.0');
define('JOUVENCE_PARA_CORE_SCHEMA_VERSION', '2');
define('JOUVENCE_PARA_CORE_FILE', __FILE__);
define('JOUVENCE_PARA_CORE_PATH', plugin_dir_path(__FILE__));

spl_autoload_register(
    static function (string $class): void {
        $prefix = 'JouvencePara\\Core\\';
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $path = JOUVENCE_PARA_CORE_PATH . 'src/' . str_replace('\\', '/', $relative) . '.php';
        if (is_readable($path)) {
            require_once $path;
        }
    }
);

register_activation_hook(
    JOUVENCE_PARA_CORE_FILE,
    [JouvencePara\Core\Bootstrap\Lifecycle::class, 'activate']
);
register_deactivation_hook(
    JOUVENCE_PARA_CORE_FILE,
    [JouvencePara\Core\Bootstrap\Lifecycle::class, 'deactivate']
);

(new JouvencePara\Core\Support\Compatibility())->register();

add_action(
    'plugins_loaded',
    static function (): void {
        if (! class_exists('WooCommerce')) {
            add_action(
                'admin_notices',
                static function (): void {
                    echo '<div class="notice notice-error"><p>';
                    echo esc_html__('Jouvence Para Core requires WooCommerce.', 'jouvence-para-core');
                    echo '</p></div>';
                }
            );
            return;
        }

        (new JouvencePara\Core\Bootstrap\Plugin([
            new JouvencePara\Core\Infrastructure\Database\DatabaseModule(),
            new JouvencePara\Core\Catalog\CatalogModule(),
            new JouvencePara\Core\Catalog\CatalogTaxonomyModule(),
            new JouvencePara\Core\Catalog\CatalogCsvModule(),
            new JouvencePara\Core\Catalog\ProductAdminModule(),
            new JouvencePara\Core\Admin\MerchandisingModule(),
            new JouvencePara\Core\Admin\StaffRolesModule(),
            new JouvencePara\Core\Audit\AuditModule(),
            new JouvencePara\Core\Media\MediaModule(),
            new JouvencePara\Core\Inventory\InventoryModule(),
            new JouvencePara\Core\Observability\ObservabilityModule(),
            new JouvencePara\Core\Privacy\ConsentModule(),
            new JouvencePara\Core\Security\SecurityModule(),
        ]))->boot();
    }
);
