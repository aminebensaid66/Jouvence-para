(function () {
    'use strict';

    const invalidEmailAutocomplete = 'section-contact contact email';
    const normalizeCheckoutEmail = function () {
        document.querySelectorAll('input[type="email"][autocomplete="' + invalidEmailAutocomplete + '"]').forEach(function (input) {
            input.setAttribute('autocomplete', 'email');
        });
    };

    normalizeCheckoutEmail();
    new MutationObserver(normalizeCheckoutEmail).observe(document.body, {
        attributes: true,
        attributeFilter: ['autocomplete'],
        childList: true,
        subtree: true,
    });
})();
