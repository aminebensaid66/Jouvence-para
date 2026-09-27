<?php
/** Preserve filter state while changing catalog sort. */
defined('ABSPATH') || exit;
?>
<form class="woocommerce-ordering" method="get">
    <label for="jp-orderby" class="screen-reader-text"><?php esc_html_e('Trier les produits', 'jouvence-para'); ?></label>
    <select id="jp-orderby" name="orderby" class="orderby" aria-label="<?php esc_attr_e('Trier les produits', 'jouvence-para'); ?>">
        <?php foreach ($catalog_orderby_options as $id => $name) : ?>
            <option value="<?php echo esc_attr($id); ?>" <?php selected($orderby, $id); ?>><?php echo esc_html($name); ?></option>
        <?php endforeach; ?>
    </select>
    <?php foreach (array_keys(\JouvencePara\Core\Discovery\FilterState::TAXONOMIES) as $key) : ?>
        <?php $param = 'jp_filter_' . $key; $values = isset($_GET[$param]) ? (array) wp_unslash($_GET[$param]) : []; ?>
        <?php foreach ($values as $value) : ?>
            <?php $value = sanitize_key((string) $value); if ($value === '') { continue; } ?>
            <input type="hidden" name="<?php echo esc_attr($param); ?>[]" value="<?php echo esc_attr($value); ?>">
        <?php endforeach; ?>
    <?php endforeach; ?>
    <button type="submit" class="jp-button jp-button--secondary"><?php esc_html_e('Trier', 'jouvence-para'); ?></button>
</form>
