<?php

get_header();
$merchandisingBlocks = apply_filters('jouvence_para_homepage_blocks', []);
?>
<main id="main-content" class="site-main jp-container">
    <div class="jp-home-intro">
        <p class="jp-eyebrow"><?php esc_html_e('Online parapharmacy in Tunisia', 'jouvence-para'); ?></p>
        <h1><?php bloginfo('name'); ?></h1>
        <p><?php esc_html_e('Clear product information, reliable availability and human support for everyday parapharmacy needs.', 'jouvence-para'); ?></p>
    </div>

    <?php if (is_array($merchandisingBlocks) && $merchandisingBlocks !== []) : ?>
        <section class="jp-merchandising" aria-labelledby="jp-merchandising-title">
            <h2 id="jp-merchandising-title" class="screen-reader-text"><?php esc_html_e('Featured selections', 'jouvence-para'); ?></h2>
            <?php foreach ($merchandisingBlocks as $block) : ?>
                <article class="jp-merchandising-card jp-merchandising-card--<?php echo esc_attr((string) ($block['type'] ?? 'banner')); ?>">
                    <?php if (($block['image_url'] ?? '') !== '') : ?>
                        <img class="jp-merchandising-card__image" src="<?php echo esc_url((string) $block['image_url']); ?>" alt="<?php echo esc_attr((string) ($block['image_alt'] ?? '')); ?>">
                    <?php endif; ?>
                    <div class="jp-merchandising-card__content">
                        <?php if (($block['eyebrow'] ?? '') !== '') : ?>
                            <p class="jp-eyebrow"><?php echo esc_html((string) $block['eyebrow']); ?></p>
                        <?php endif; ?>
                        <h3><?php echo esc_html((string) ($block['title'] ?? '')); ?></h3>
                        <?php if (($block['body'] ?? '') !== '') : ?>
                            <p><?php echo esc_html((string) $block['body']); ?></p>
                        <?php endif; ?>
                        <?php if (($block['link_url'] ?? '') !== '') : ?>
                            <a class="jp-button" href="<?php echo esc_url((string) $block['link_url']); ?>">
                                <?php echo esc_html((string) (($block['cta_label'] ?? '') !== '' ? $block['cta_label'] : __('Discover', 'jouvence-para'))); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
<?php
get_footer();
