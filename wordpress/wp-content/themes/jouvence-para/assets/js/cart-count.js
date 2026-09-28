(function () {
    const getCartItemsCount = function () {
        if (!window.wp || !window.wp.data || typeof window.wp.data.select !== 'function') {
            return null;
        }

        const cartStore = window.wp.data.select('wc/store/cart');
        if (!cartStore || typeof cartStore.getCartData !== 'function') {
            return null;
        }

        const cartData = cartStore.getCartData();
        const itemCount = Number(cartData && cartData.itemsCount);
        return Number.isSafeInteger(itemCount) && itemCount >= 0 ? itemCount : null;
    };

    const updateCartItemsCount = function () {
        const itemCount = getCartItemsCount();
        if (itemCount === null) {
            return;
        }

        document.querySelectorAll('.jp-cart-link').forEach(function (link) {
            const countElement = link.querySelector('.jp-cart-link__number');
            const announcement = link.querySelector('.jp-cart-link__announcement');
            if (!countElement || !announcement) {
                return;
            }

            const labelTemplate = itemCount === 1
                ? announcement.dataset.labelSingular
                : announcement.dataset.labelPlural;
            if (!labelTemplate) {
                return;
            }

            const announcementText = labelTemplate.replace('%d', String(itemCount));
            if (countElement.textContent === String(itemCount) && announcement.textContent === announcementText) {
                return;
            }

            countElement.textContent = String(itemCount);
            announcement.textContent = announcementText;
        });
    };

    if (!window.wp || !window.wp.data || typeof window.wp.data.subscribe !== 'function') {
        return;
    }

    updateCartItemsCount();
    window.wp.data.subscribe(updateCartItemsCount);
})();
