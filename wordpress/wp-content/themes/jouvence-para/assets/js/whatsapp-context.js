(() => {
    'use strict';

    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target.closest('[data-jp-whatsapp-click]') : null;
        if (!target) {
            return;
        }
        const allowsAnalytics = window.JouvenceParaConsent?.allows?.('analytics') === true;
        if (!allowsAnalytics) {
            return;
        }
        window.dispatchEvent(new CustomEvent('jouvencepara:analytics', {
            detail: {
                event: 'whatsapp_click',
                context: 'product',
                product_id: Number(target.dataset.jpProductId || 0),
            },
        }));
    });
})();
