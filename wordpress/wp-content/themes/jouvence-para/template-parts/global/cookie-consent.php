<div class="jp-cookie-banner" data-jp-cookie-banner hidden role="region" aria-labelledby="jp-cookie-banner-title">
    <strong id="jp-cookie-banner-title"><?php esc_html_e('Your privacy choices', 'jouvence-para'); ?></strong>
    <p><?php esc_html_e('Essential cookies keep the store, cart and checkout working. Analytics and marketing cookies are optional.', 'jouvence-para'); ?></p>
    <div class="jp-cookie-banner__actions">
        <button class="jp-cookie-button jp-cookie-button--primary" type="button" data-jp-consent-accept><?php esc_html_e('Accept all', 'jouvence-para'); ?></button>
        <button class="jp-cookie-button" type="button" data-jp-consent-reject><?php esc_html_e('Reject optional', 'jouvence-para'); ?></button>
        <button class="jp-cookie-button" type="button" data-jp-consent-customize><?php esc_html_e('Customize', 'jouvence-para'); ?></button>
    </div>
</div>

<div class="jp-cookie-dialog" data-jp-cookie-dialog hidden role="dialog" aria-modal="true" aria-labelledby="jp-cookie-dialog-title">
    <div class="jp-cookie-dialog__panel">
        <h2 id="jp-cookie-dialog-title"><?php esc_html_e('Cookie preferences', 'jouvence-para'); ?></h2>
        <p><?php esc_html_e('You can change optional cookie choices at any time. Essential storage cannot be disabled because it is required for core store functions.', 'jouvence-para'); ?></p>
        <label class="jp-cookie-dialog__option">
            <input type="checkbox" checked disabled>
            <span><strong><?php esc_html_e('Essential', 'jouvence-para'); ?></strong><br><?php esc_html_e('Required for security, cart, checkout and account functions.', 'jouvence-para'); ?></span>
        </label>
        <label class="jp-cookie-dialog__option">
            <input type="checkbox" name="jp-consent-analytics">
            <span><strong><?php esc_html_e('Analytics', 'jouvence-para'); ?></strong><br><?php esc_html_e('Helps us understand store usage and improve the experience.', 'jouvence-para'); ?></span>
        </label>
        <label class="jp-cookie-dialog__option">
            <input type="checkbox" name="jp-consent-marketing">
            <span><strong><?php esc_html_e('Marketing', 'jouvence-para'); ?></strong><br><?php esc_html_e('Allows approved marketing measurement or personalization tools.', 'jouvence-para'); ?></span>
        </label>
        <div class="jp-cookie-dialog__actions">
            <button class="jp-cookie-button" type="button" data-jp-consent-close><?php esc_html_e('Cancel', 'jouvence-para'); ?></button>
            <button class="jp-cookie-button jp-cookie-button--primary" type="button" data-jp-consent-save><?php esc_html_e('Save preferences', 'jouvence-para'); ?></button>
        </div>
    </div>
</div>
