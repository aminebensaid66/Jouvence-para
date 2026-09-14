<?php

declare(strict_types=1);

namespace JouvencePara\Core\Catalog;

use JouvencePara\Core\Contracts\Module;

final class CatalogCsvModule implements Module
{
    private const MAX_BYTES = 5_000_000;

    public function register(): void
    {
        add_action('admin_menu', [$this, 'menu']);
        add_action('admin_post_jp_catalog_csv_import', [$this, 'import']);
        add_action('admin_post_jp_catalog_csv_export', [$this, 'export']);
    }

    public function menu(): void
    {
        add_submenu_page(
            'edit.php?post_type=product',
            __('Import/export catalogue', 'jouvence-para-core'),
            __('Import/export catalogue', 'jouvence-para-core'),
            'edit_products',
            'jp-catalog-csv',
            [$this, 'page']
        );
    }

    public function page(): void
    {
        if (! current_user_can('edit_products')) {
            $this->fail(__('Accès refusé.', 'jouvence-para-core'), 403);
        }
        $reportKey = 'jp_catalog_csv_report_' . get_current_user_id();
        $report = get_transient($reportKey);
        delete_transient($reportKey);
        ?>
        <div class="wrap"><h1><?php esc_html_e('Import/export catalogue', 'jouvence-para-core'); ?></h1>
        <?php if (is_array($report)) : ?>
            <h2><?php esc_html_e('Résultat du dernier import', 'jouvence-para-core'); ?></h2>
            <table class="widefat striped">
                <thead><tr><th><?php esc_html_e('Ligne', 'jouvence-para-core'); ?></th><th><?php esc_html_e('SKU', 'jouvence-para-core'); ?></th><th><?php esc_html_e('Statut', 'jouvence-para-core'); ?></th><th><?php esc_html_e('Erreurs', 'jouvence-para-core'); ?></th></tr></thead>
                <tbody><?php foreach ($report as $entry) : ?>
                    <tr><td><?php echo esc_html((string) ($entry['row'] ?? '')); ?></td><td><?php echo esc_html((string) ($entry['sku'] ?? '')); ?></td><td><?php echo esc_html((string) ($entry['status'] ?? '')); ?></td><td><?php echo esc_html(implode(', ', (array) ($entry['errors'] ?? []))); ?></td></tr>
                <?php endforeach; ?></tbody>
            </table>
        <?php endif; ?>
        <p><?php esc_html_e('Les marques et catégories doivent déjà exister. Les valeurs inconnues sont rejetées.', 'jouvence-para-core'); ?></p>
        <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="jp_catalog_csv_import"><?php wp_nonce_field('jp_catalog_csv_import'); ?>
            <label for="jp_catalog_csv"><?php esc_html_e('Fichier CSV UTF-8', 'jouvence-para-core'); ?></label>
            <input id="jp_catalog_csv" type="file" name="jp_catalog_csv" accept=".csv,text/csv" required>
            <?php submit_button(__('Importer', 'jouvence-para-core')); ?>
        </form>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="jp_catalog_csv_export"><?php wp_nonce_field('jp_catalog_csv_export'); ?>
            <?php submit_button(__('Exporter', 'jouvence-para-core'), 'secondary'); ?>
        </form></div>
        <?php
    }

    public function import(): void
    {
        check_admin_referer('jp_catalog_csv_import');
        if (! current_user_can('edit_products')) {
            $this->fail(__('Accès refusé.', 'jouvence-para-core'), 403);
        }
        $file = $_FILES['jp_catalog_csv'] ?? null;
        if (! is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->fail(__('Téléversement CSV invalide.', 'jouvence-para-core'), 400);
        }
        if ((int) ($file['size'] ?? 0) <= 0 || (int) $file['size'] > self::MAX_BYTES) {
            $this->fail(__('Le fichier CSV dépasse la taille autorisée.', 'jouvence-para-core'), 400);
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || ! is_uploaded_file($tmp)) {
            $this->fail(__('Fichier CSV non fiable.', 'jouvence-para-core'), 400);
        }
        $rows = $this->readCsv($tmp);
        $report = (new CatalogCsvImporter(new WooCatalogCsvGateway()))->import($rows);
        set_transient('jp_catalog_csv_report_' . get_current_user_id(), $report, 15 * MINUTE_IN_SECONDS);
        wp_safe_redirect(admin_url('edit.php?post_type=product&page=jp-catalog-csv&imported=1'));
        exit;
    }

    public function export(): void
    {
        check_admin_referer('jp_catalog_csv_export');
        if (! current_user_can('edit_products')) {
            $this->fail(__('Accès refusé.', 'jouvence-para-core'), 403);
        }
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="jouvence-para-catalog.csv"');
        $output = fopen('php://output', 'wb');
        if ($output === false) {
            $this->fail(__('Impossible de créer l’export.', 'jouvence-para-core'), 500);
        }
        fputcsv($output, CatalogCsvSchema::headers());
        $gateway = new WooCatalogCsvGateway();
        for ($page = 1; $page <= 10000; $page++) {
            $rows = $gateway->exportPage($page, 100);
            if ($rows === []) {
                break;
            }
            foreach ($rows as $row) {
                fputcsv($output, array_map(
                    static fn (string $header): string => self::safeCsvCell((string) ($row[$header] ?? '')),
                    CatalogCsvSchema::headers()
                ));
            }
            if (count($rows) < 100) {
                break;
            }
        }
        fclose($output);
        exit;
    }

    /** @return list<array<string, mixed>> */
    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            $this->fail(__('Impossible de lire le CSV.', 'jouvence-para-core'), 400);
        }
        $headers = fgetcsv($handle);
        if (! is_array($headers)) {
            fclose($handle);
            $this->fail(__('En-têtes CSV absents.', 'jouvence-para-core'), 400);
        }
        $headers = CatalogCsvSchema::normalizeHeaders(array_map('strval', $headers));
        if ($headers !== CatalogCsvSchema::headers()) {
            fclose($handle);
            $this->fail(__('Les en-têtes CSV ne correspondent pas au contrat catalogue.', 'jouvence-para-core'), 400);
        }
        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (count($rows) >= 5000) {
                fclose($handle);
                $this->fail(__('Le CSV dépasse la limite de 5 000 lignes.', 'jouvence-para-core'), 400);
            }
            if (count($values) !== count($headers)) {
                $rows[] = ['sku' => '', 'name' => '', 'regular_price' => '', 'stock_quantity' => '', 'stock_status' => ''];
                continue;
            }
            $rows[] = array_combine($headers, $values) ?: [];
        }
        fclose($handle);
        return $rows;
    }

    public static function safeCsvCell(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) === 1 ? "'" . $value : $value;
    }

    private function fail(string $message, int $status): never
    {
        wp_die(esc_html($message), '', ['response' => $status]);
    }
}
