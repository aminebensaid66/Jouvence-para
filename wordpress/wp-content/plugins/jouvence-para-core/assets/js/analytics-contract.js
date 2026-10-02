(() => {
    'use strict';

    const config = window.JouvenceParaAnalyticsConfig || {};
    const seen = new Map();
    const deliveredFlashEvents = new Set();
    const allowedEvents = new Set(['search', 'search_no_results', 'search_result_click', 'category_view', 'filter_use', 'product_view', 'whatsapp_click', 'add_to_cart', 'remove_from_cart', 'cart_view', 'checkout_start', 'checkout_error', 'shipping_selected', 'payment_selected', 'purchase', 'coupon_applied', 'coupon_rejected', 'newsletter_signup', 'back_in_stock_request', 'account_registration', 'reorder']);
    let couponAttemptExpiresAt = 0;

    const sanitizePayload = (value, depth = 0, field = '') => {
        if (depth > 3 || value === null || typeof value === 'function') {
            return undefined;
        }
        if (typeof value === 'string') {
            if (value.length > 160 || /[\u0000-\u001f\u007f]/u.test(value)) return undefined;
            if (field === 'sku' && /^[A-Za-z0-9._/-]{1,80}$/u.test(value)) return value;
            if (field === 'currency' && /^[A-Z]{3}$/u.test(value)) return value;
            if (['brand', 'category', 'context', 'method'].includes(field) && /^[a-z0-9_-]{1,80}$/iu.test(value)) return value;
            if (field === 'purchase_id' && /^[a-f0-9]{64}$/u.test(value)) return value;
            return undefined;
        }
        if (typeof value === 'number' || typeof value === 'boolean') {
            return value;
        }
        if (Array.isArray(value)) {
            return value.slice(0, 100).map((item) => sanitizePayload(item, depth + 1, '')).filter((item) => item !== undefined);
        }
        if (typeof value === 'object') {
            return Object.entries(value).reduce((safe, [key, item]) => {
                if (!/^[a-z][a-z0-9_]{0,40}$/u.test(key) || key === 'event' || key === 'schema_version' || /(?:email|phone|name|address|customer|user|password|token|secret|billing|shipping)/iu.test(key)) {
                    return safe;
                }
                const clean = sanitizePayload(item, depth + 1, key);
                if (clean !== undefined) safe[key] = clean;
                return safe;
            }, {});
        }
        return undefined;
    };

    const emit = (event, payload = {}) => {
        if (!allowedEvents.has(event) || window.JouvenceParaConsent?.allows?.('analytics') !== true) {
            return false;
        }
        const safePayload = sanitizePayload(payload) || {};
        const key = `${event}:${JSON.stringify(safePayload)}`;
        const now = Date.now();
        if (seen.get(key) && now - seen.get(key) < 500) {
            return false;
        }
        seen.set(key, now);
        window.dispatchEvent(new CustomEvent('jouvencepara:analytics', {
            detail: { ...safePayload, schema_version: 1, event },
        }));
        return true;
    };

    const purchase = config.purchase;
    const sendPurchase = () => {
        if (!purchase?.purchase_id || window.JouvenceParaConsent?.allows?.('analytics') !== true) {
            return;
        }
        const key = `jp_analytics_purchase_v1_${purchase.purchase_id}`;
        for (const access of [() => window.localStorage, () => window.sessionStorage]) {
            try {
                const storage = access();
                if (storage.getItem(key) === '1') return;
                storage.setItem(key, '1');
                if (!emit('purchase', purchase)) storage.removeItem(key);
                return;
            } catch (error) {
                // Continue with session storage; suppress if neither store works.
            }
        }
    };
    sendPurchase();
    window.addEventListener('jouvencepara:consentchange', sendPurchase);

    const emitPageContext = () => {
        Object.entries(config.events || {}).forEach(([event, count]) => {
            for (let index = 0; index < Math.min(100, Number(count) || 0); index += 1) {
                const marker = `${event}:${index + 1}`;
                if (!deliveredFlashEvents.has(marker) && emit(event, { event_sequence: index + 1 })) {
                    deliveredFlashEvents.add(marker);
                }
            }
        });
        if (config.product?.product_id) emit('product_view', config.product);
        if (config.search) {
            const resultCount = Number(config.search.result_count) || 0;
            emit(resultCount > 0 ? 'search' : 'search_no_results', {
                query_length: Number(config.search.query_length) || 0, result_count: resultCount,
            });
        }
        if (config.category) emit('category_view', config.category);
        if (config.cart) emit('cart_view');
        if (config.checkout) emit('checkout_start');
    };
    emitPageContext();
    window.addEventListener('jouvencepara:consentchange', emitPageContext);

    const jquery = window.jQuery;
    if (jquery) {
        jquery(document.body).on('added_to_cart', () => emit('add_to_cart'));
        jquery(document.body).on('removed_from_cart', () => emit('remove_from_cart'));
        jquery(document.body).on('updated_wc_div', () => emit('cart_view'));
        jquery(document.body).on('checkout_error', () => emit('checkout_error'));
        jquery(document.body).on('applied_coupon', () => {
            couponAttemptExpiresAt = 0;
            emit('coupon_applied');
        });
    }

    const couponForm = (target) => target instanceof Element && Boolean(target.closest('.woocommerce-form-coupon, form.checkout_coupon'));
    const beginCouponAttempt = () => { couponAttemptExpiresAt = Date.now() + 15000; };
    document.addEventListener('submit', (event) => {
        if (couponForm(event.target)) beginCouponAttempt();
    });

    if (typeof MutationObserver !== 'undefined' && document.body) {
        const couponNoticeObserver = new MutationObserver((records) => {
            if (Date.now() > couponAttemptExpiresAt) return;
            for (const record of records) {
                for (const node of record.addedNodes || []) {
                    const notice = node instanceof Element
                        ? (node.matches('.woocommerce-message, .woocommerce-error, .wc-block-components-notice-banner.is-success, .wc-block-components-notice-banner.is-error')
                            ? node
                            : node.querySelector('.woocommerce-message, .woocommerce-error, .wc-block-components-notice-banner.is-success, .wc-block-components-notice-banner.is-error'))
                        : null;
                    if (!notice) continue;
                    const successful = notice.matches('.woocommerce-message, .wc-block-components-notice-banner.is-success');
                    emit(successful ? 'coupon_applied' : 'coupon_rejected');
                    couponAttemptExpiresAt = 0;
                    return;
                }
            }
        });
        couponNoticeObserver.observe(document.body, { childList: true, subtree: true });
    }

    // Observe actual Store API cart changes, including block add/remove operations.
    if (window.wp?.data?.subscribe) {
        let itemCount = null;
        const observeCart = () => {
            const items = window.wp.data.select('wc/store/cart')?.getCartData()?.items;
            if (!Array.isArray(items)) return;
            const nextCount = items.reduce((count, item) => count + Number(item.quantity || 0), 0);
            if (itemCount === null) {
                itemCount = nextCount;
                return;
            }
            if (nextCount > itemCount) emit('add_to_cart');
            if (nextCount < itemCount) emit('remove_from_cart');
            itemCount = nextCount;
        };
        observeCart();
        window.wp.data.subscribe(observeCart);
    }

    document.addEventListener('click', (event) => {
        const target = event.target instanceof Element ? event.target.closest('a[href], button, input[type="submit"], [data-jp-analytics-event]') : null;
        if (!target) {
            return;
        }
        const explicit = target.dataset.jpAnalyticsEvent;
        if (explicit) {
            const context = target.dataset.jpAnalyticsContext || '';
            emit(explicit, /^[a-z0-9_-]{1,40}$/iu.test(context) ? { context } : {});
            return;
        }
        if (target.matches('button[name="apply_coupon"], input[name="apply_coupon"]')
            || target.closest('.wc-block-components-totals-coupon__button')) {
            beginCouponAttempt();
            return;
        }
        if (config.search) {
            const card = target.closest('.jp-product-card');
            const productId = Number(card?.dataset.jpProductId || 0);
            if (productId > 0) emit('search_result_click', { product_id: productId });
        }
        if (target.closest('[data-jp-newsletter-signup]')) {
            emit('newsletter_signup');
        } else if (target.closest('[data-jp-back-in-stock-request]')) {
            emit('back_in_stock_request');
        } else if (target.closest('[data-jp-reorder], .woocommerce-button.order-again')) {
            emit('reorder');
        } else if (target.closest('[data-jp-account-registration]')) {
            emit('account_registration');
        }
    });

    document.addEventListener('change', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLInputElement || target instanceof HTMLSelectElement)) {
            return;
        }
        if (target.closest('.jp-facets')) {
            emit('filter_use', { context: 'facet' });
            return;
        }
        const name = target.name || '';
        const method = /^[a-z0-9_:-]{1,80}$/iu.test(target.value) ? target.value : '';
        if (/^shipping_method(?:\[\d+\])?$/u.test(name) && method) {
            emit('shipping_selected', { method });
        } else if (name === 'payment_method' && method) {
            emit('payment_selected', { method });
        }
    });

    window.JouvenceParaAnalytics = { emit };
})();
