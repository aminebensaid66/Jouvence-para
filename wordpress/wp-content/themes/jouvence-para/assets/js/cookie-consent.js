(() => {
    'use strict';

    const COOKIE_NAME = 'jp_consent_v1';
    const VERSION = 1;
    const MAX_AGE = 60 * 60 * 24 * 180;
    const banner = document.querySelector('[data-jp-cookie-banner]');
    const dialog = document.querySelector('[data-jp-cookie-dialog]');
    const analytics = dialog?.querySelector('[name="jp-consent-analytics"]');
    const marketing = dialog?.querySelector('[name="jp-consent-marketing"]');
    let returnFocus = null;

    const encode = (preferences) => {
        const json = JSON.stringify({
            v: VERSION,
            analytics: Boolean(preferences.analytics),
            marketing: Boolean(preferences.marketing),
        });
        return btoa(json).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
    };

    const decode = (value) => {
        try {
            const normalized = value.replace(/-/g, '+').replace(/_/g, '/');
            const padding = normalized.length % 4 === 0 ? '' : '='.repeat(4 - (normalized.length % 4));
            const parsed = JSON.parse(atob(normalized + padding));
            if (parsed.v !== VERSION || typeof parsed.analytics !== 'boolean' || typeof parsed.marketing !== 'boolean') {
                return null;
            }
            return parsed;
        } catch (error) {
            return null;
        }
    };

    const read = () => {
        const prefix = `${COOKIE_NAME}=`;
        const item = document.cookie.split(';').map((part) => part.trim()).find((part) => part.startsWith(prefix));
        return item ? decode(item.slice(prefix.length)) : null;
    };

    const activateDeferredScripts = (preferences) => {
        document.querySelectorAll('script[type="text/plain"][data-jp-consent]').forEach((placeholder) => {
            const category = placeholder.dataset.jpConsent;
            if (!preferences?.[category] || placeholder.dataset.jpActivated === '1') {
                return;
            }

            const script = document.createElement('script');
            [...placeholder.attributes].forEach((attribute) => {
                if (!['type', 'data-jp-consent', 'data-jp-activated', 'data-src'].includes(attribute.name)) {
                    script.setAttribute(attribute.name, attribute.value);
                }
            });
            if (placeholder.dataset.src) {
                script.src = placeholder.dataset.src;
            }
            script.textContent = placeholder.textContent;
            placeholder.dataset.jpActivated = '1';
            placeholder.after(script);
        });
    };

    const save = (preferences) => {
        const previous = read();
        const requiresReload = Boolean(
            (previous?.analytics && !preferences.analytics)
            || (previous?.marketing && !preferences.marketing)
        );
        const secure = window.location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = `${COOKIE_NAME}=${encode(preferences)}; Path=/; Max-Age=${MAX_AGE}; SameSite=Lax${secure}`;
        window.dispatchEvent(new CustomEvent('jouvencepara:consentchange', { detail: preferences }));
        if (requiresReload) {
            window.location.reload();
            return false;
        }
        activateDeferredScripts(preferences);
        return true;
    };

    const closeDialog = () => {
        if (!dialog) {
            return;
        }
        dialog.hidden = true;
        document.body.classList.remove('jp-consent-open');
        if (returnFocus instanceof HTMLElement) {
            returnFocus.focus();
        }
    };

    const openDialog = (trigger) => {
        if (!dialog || !analytics || !marketing) {
            return;
        }
        const preferences = read() || { analytics: false, marketing: false };
        analytics.checked = preferences.analytics;
        marketing.checked = preferences.marketing;
        returnFocus = trigger instanceof HTMLElement ? trigger : null;
        dialog.hidden = false;
        document.body.classList.add('jp-consent-open');
        analytics.focus();
    };

    const finish = (preferences) => {
        if (!save(preferences)) {
            return;
        }
        if (banner) {
            banner.hidden = true;
        }
        closeDialog();
    };

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        if (target.closest('[data-jp-consent-accept]')) {
            finish({ analytics: true, marketing: true });
        } else if (target.closest('[data-jp-consent-reject]')) {
            finish({ analytics: false, marketing: false });
        } else if (target.closest('[data-jp-consent-customize]')) {
            openDialog(target.closest('[data-jp-consent-customize]'));
        } else if (target.closest('[data-jp-consent-close]')) {
            closeDialog();
        } else if (target.closest('[data-jp-consent-save]')) {
            finish({ analytics: Boolean(analytics?.checked), marketing: Boolean(marketing?.checked) });
        }
    });

    document.addEventListener('keydown', (event) => {
        if (!dialog || dialog.hidden) {
            return;
        }
        if (event.key === 'Escape') {
            closeDialog();
            return;
        }
        if (event.key !== 'Tab') {
            return;
        }

        const focusable = [...dialog.querySelectorAll('button:not([disabled]), input:not([disabled]), a[href]')];
        if (focusable.length === 0) {
            return;
        }
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    const preferences = read();
    window.JouvenceParaConsent = {
        allows: (category) => category === 'essential' || Boolean(read()?.[category]),
        preferences: () => read(),
        open: () => openDialog(document.activeElement),
    };

    if (preferences) {
        activateDeferredScripts(preferences);
    } else if (banner) {
        banner.hidden = false;
    }
})();
