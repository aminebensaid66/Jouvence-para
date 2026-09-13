<footer class="site-footer">
    <div class="jp-container">
        <p>&copy; <?php echo esc_html(wp_date('Y')); ?> <?php bloginfo('name'); ?></p>
        <button class="jp-cookie-button jp-cookie-preferences" type="button" data-jp-consent-customize><?php esc_html_e('Cookie preferences', 'jouvence-para'); ?></button>
    </div>
</footer>
<?php get_template_part('template-parts/global/cookie-consent'); ?>
<?php wp_footer(); ?>
</body>
</html>
