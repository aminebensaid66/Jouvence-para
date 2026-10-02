const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../../wordpress/wp-content/plugins/jouvence-para-core/assets/js/analytics-contract.js'), 'utf8');

function runtime(config = {}, { consent = false, stored = new Map(), storageBlocked = false, cartQuantity = 0, cartStoreReady = true } = {}) {
    const documentListeners = new Map();
    const windowListeners = new Map();
    const dispatched = [];
    let currentQuantity = cartQuantity;
    let ready = cartStoreReady;
    let cartSubscriber;
    let mutationCallback;

    class Element {}
    class HTMLInputElement extends Element {}
    class HTMLSelectElement extends Element {}
    class MutationObserver {
        constructor(callback) { mutationCallback = callback; }
        observe() {}
    }
    class CustomEvent {
        constructor(type, options = {}) { this.type = type; this.detail = options.detail; }
    }
    const storage = {
        getItem(key) { return stored.has(key) ? stored.get(key) : null; },
        setItem(key, value) { stored.set(key, String(value)); },
        removeItem(key) { stored.delete(key); },
    };
    const window = {
        JouvenceParaAnalyticsConfig: config,
        JouvenceParaConsent: { allows: (category) => category === 'essential' || (category === 'analytics' && consent) },
        addEventListener(type, callback) {
            const callbacks = windowListeners.get(type) || [];
            callbacks.push(callback);
            windowListeners.set(type, callbacks);
        },
        dispatchEvent(event) {
            dispatched.push(event);
            for (const callback of windowListeners.get(event.type) || []) callback(event);
        },
        get localStorage() {
            if (storageBlocked) throw new Error('storage disabled');
            return storage;
        },
        get sessionStorage() {
            if (storageBlocked) throw new Error('storage disabled');
            return storage;
        },
    };
    const document = {
        body: {},
        addEventListener(type, callback) { documentListeners.set(type, callback); },
    };
    if (cartQuantity !== null) {
        window.wp = {
            data: {
                select: () => ready ? ({ getCartData: () => ({ items: currentQuantity ? [{ quantity: currentQuantity }] : [] }) }) : undefined,
                subscribe: (callback) => { cartSubscriber = callback; },
            },
        };
    }
    vm.runInNewContext(source, {
        window, document, Element, HTMLInputElement, HTMLSelectElement, MutationObserver, CustomEvent,
        Date, Map, Set, JSON, Number, Math, Object, String, Array,
    });
    return {
        dispatched,
        clickSearchResult(productId) {
            const card = { dataset: { jpProductId: String(productId) } };
            const target = new Element();
            target.dataset = {};
            target.matches = () => false;
            target.closest = (selector) => {
                if (selector === 'a[href], button, input[type="submit"], [data-jp-analytics-event]') return target;
                if (selector === '.jp-product-card') return card;
                return null;
            };
            documentListeners.get('click')({ target });
        },
        applyCoupon(result) {
            const form = new Element();
            form.closest = () => form;
            documentListeners.get('submit')({ target: form });
            const notice = new Element();
            notice.matches = (selector) => result === 'success'
                ? selector.includes('.woocommerce-message') || selector.includes('.is-success')
                : selector.includes('.woocommerce-error') || selector.includes('.is-error');
            notice.querySelector = () => null;
            mutationCallback([{ addedNodes: [notice] }]);
        },
        clickFacetApply() {
            const target = new Element();
            target.dataset = {};
            target.matches = () => false;
            target.closest = (selector) => selector === 'a[href], button, input[type="submit"], [data-jp-analytics-event]'
                ? target
                : (selector === '.jp-facets' ? target : null);
            documentListeners.get('click')({ target });
        },
        changeConsent(value) {
            consent = value;
            window.dispatchEvent(new CustomEvent('jouvencepara:consentchange', { detail: { analytics: value, marketing: false } }));
        },
        setCartQuantity(value) {
            currentQuantity = value;
            cartSubscriber?.();
        },
        registerCartStore(value) {
            ready = true;
            currentQuantity = value;
            cartSubscriber?.();
        },
        changeFacet(name, value) {
            const target = new HTMLSelectElement();
            target.name = name;
            target.value = value;
            target.closest = (selector) => selector === '.jp-facets';
            documentListeners.get('change')({ target });
        },
        emit: window.JouvenceParaAnalytics.emit,
    };
}

test('consent denial suppresses page and purchase events; grant emits safe contract payload once', () => {
    const state = runtime({
        search: { query: 'name@example.invalid +216 29 302 202', query_length: 32, result_count: 0 },
        purchase: { event: 'search', schema_version: 99, purchase_id: 'a'.repeat(64), value: 20, email: 'private@example.invalid' },
        checkout: true,
    });
    assert.equal(state.dispatched.length, 0);
    state.changeConsent(true);
    const events = state.dispatched.map(({ detail }) => detail);
    assert.equal(events.filter(({ event }) => event === 'purchase').length, 1);
    assert.equal(events.filter(({ event }) => event === 'search_no_results').length, 1);
    assert.equal(events.filter(({ event }) => event === 'checkout_start').length, 1);
    const purchase = events.find(({ event }) => event === 'purchase');
    assert.equal(purchase.event, 'purchase');
    assert.equal(purchase.schema_version, 1);
    assert.equal('email' in purchase, false);
    const search = events.find(({ event }) => event === 'search_no_results');
    assert.equal('query' in search, false);
    assert.equal(search.query_length, 32);
    state.changeConsent(true);
    assert.equal(state.dispatched.filter(({ detail }) => detail.event === 'purchase').length, 1);
});

test('purchase refresh uses persistent dedupe storage across page runtimes', () => {
    const stored = new Map();
    const config = { purchase: { purchase_id: 'b'.repeat(64), value: 5, currency: 'TND' } };
    const first = runtime(config, { consent: true, stored, cartQuantity: null });
    const second = runtime(config, { consent: true, stored, cartQuantity: null });
    assert.equal(first.dispatched.filter(({ detail }) => detail.event === 'purchase').length, 1);
    assert.equal(second.dispatched.filter(({ detail }) => detail.event === 'purchase').length, 0);
});

test('purchase is suppressed when browser storage cannot guarantee refresh dedupe', () => {
    const state = runtime({ purchase: { purchase_id: 'c'.repeat(64), value: 5 } }, { consent: true, storageBlocked: true, cartQuantity: null });
    assert.equal(state.dispatched.some(({ detail }) => detail.event === 'purchase'), false);
});

test('Store API cart state emits successful additions and removals only with analytics consent', () => {
    const state = runtime({}, { consent: false, cartQuantity: 0 });
    state.setCartQuantity(1);
    assert.equal(state.dispatched.length, 0);
    state.changeConsent(true);
    state.setCartQuantity(2);
    state.setCartQuantity(1);
    assert.deepEqual(state.dispatched.map(({ detail }) => detail.event).filter(Boolean), ['add_to_cart', 'remove_from_cart']);
});

test('late Store API registration establishes a hydrated baseline before counting cart changes', () => {
    const state = runtime({}, { consent: true, cartQuantity: 2, cartStoreReady: false });
    state.registerCartStore(2);
    assert.equal(state.dispatched.some(({ detail }) => detail.event === 'add_to_cart'), false);
    state.setCartQuantity(3);
    assert.equal(state.dispatched.filter(({ detail }) => detail.event === 'add_to_cart').length, 1);
});

test('public emit reserves contract identity and recursively drops direct personal data', () => {
    const state = runtime({}, { consent: true, cartQuantity: null });
    assert.equal(state.emit('whatsapp_click', {
        event: 'purchase', schema_version: 99, context: 'product', query: 'private@example.invalid', email: 'private@example.invalid',
        items: [{ product_id: 3, phone: '+21629302202' }],
    }), true);
    const payload = state.dispatched[0].detail;
    assert.equal(payload.event, 'whatsapp_click');
    assert.equal(payload.schema_version, 1);
    assert.equal('email' in payload, false);
    assert.equal('query' in payload, false);
    assert.deepEqual(JSON.parse(JSON.stringify(payload.items)), [{ product_id: 3 }]);
});

test('search result clicks report only the stable product identifier', () => {
    const state = runtime({ search: { query_length: 8, result_count: 1 } }, { consent: true, cartQuantity: null });
    state.clickSearchResult(456);
    const event = state.dispatched.find(({ detail }) => detail.event === 'search_result_click')?.detail;
    assert.equal(event.product_id, 456);
    assert.equal('query' in event, false);
});

test('facet changes emit an aggregate event without filter values', () => {
    const state = runtime({}, { consent: true, cartQuantity: null });
    state.clickFacetApply();
    assert.equal(state.dispatched.length, 0);
    state.changeFacet('jp_filter[brand][]', 'sensitive-free-brand');
    const event = state.dispatched[0].detail;
    assert.equal(event.event, 'filter_use');
    assert.deepEqual(JSON.parse(JSON.stringify(event)), { context: 'facet', schema_version: 1, event: 'filter_use' });
});

test('coupon outcomes are observed without forwarding coupon values or notice text', () => {
    for (const result of ['success', 'error']) {
        const state = runtime({}, { consent: true, cartQuantity: null });
        state.applyCoupon(result);
        assert.deepEqual(state.dispatched.map(({ detail }) => detail.event), [result === 'success' ? 'coupon_applied' : 'coupon_rejected']);
        assert.equal('query' in state.dispatched[0].detail, false);
    }
});
