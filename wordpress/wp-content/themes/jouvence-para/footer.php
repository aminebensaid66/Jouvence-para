<footer class="site-footer">
    <div class="jp-container">
        <p>&copy; <?php echo esc_html(wp_date('Y')); ?> <?php bloginfo('name'); ?></p>
        <?php $jpWhatsAppUrl = (string) apply_filters('jouvence_para_whatsapp_url', '', null); ?>
        <?php if ($jpWhatsAppUrl !== '') : ?><p><a class="jp-whatsapp-link" href="<?php echo esc_url($jpWhatsAppUrl); ?>" rel="noopener noreferrer"><?php esc_html_e('WhatsApp : +216 29 302 202', 'jouvence-para'); ?></a></p><?php endif; ?>
        <button class="jp-cookie-button jp-cookie-preferences" type="button" data-jp-consent-customize><?php esc_html_e('Cookie preferences', 'jouvence-para'); ?></button>
    </div>
</footer>
<?php get_template_part('template-parts/global/cookie-consent'); ?>
<?php wp_footer(); ?>
</body>
</html>
