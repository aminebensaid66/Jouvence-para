# Guest checkout — JP-CHECKOUT-001

WooCommerce remains the source of truth for the cart, checkout, customer,
shipping, payment and order. Guest checkout stays enabled; account creation is
never required to place an order.

## Tunisia field contract

The checkout defaults to Tunisia (`TN`) and the native country/state controls
are retained. Governorates use the stable IDs already supplied by the geography
module (`TN-TUNIS`, for example), while the locality/delegation remains validated
text until the approved deeper geography catalog exists. The country base filter
also keeps native shipping and tax country calculations aligned with the Tunisia
store.

Required fields are first name (80 Unicode characters), last name (80), address
line 1 (180), city/locality, governorate, country, email and phone. Postal code
remains optional. Order notes are optional and capped at 500 characters. The
classic form and Store API use the same rules; native field values remain in the
request/session so valid input is not discarded when another field fails.

Tunisia phone input accepts eight national digits with spaces, punctuation, `+216`
or `00216`; it is stored at the order boundary as E.164-like `+216XXXXXXXX`.
Control characters, foreign prefixes, extensions and wrong lengths fail closed.
The ambiguous bare `216` prefix is removed only when the compact input has eleven
digits, so a valid eight-digit national number beginning with `216` is preserved.

## Native validation boundaries

Classic checkout normalizes posted data before WooCommerce validation and adds
field-specific errors through `woocommerce_after_checkout_validation`. Store API
PATCH updates remain partial and recoverable. Final Store API POST requests are
checked on `woocommerce_store_api_checkout_update_order_from_request`, before
WooCommerce persists the order or invokes payment. A failed request returns a
client-actionable 400 with field keys and does not create an order. The final
order hook normalizes the phone again as a defense at the persistence boundary.

No card data, password, token or unnecessary personal data is stored by this
module. Payment, shipping fees, terms and order totals remain native or belong to
their dedicated tickets.

## Verification

Automated tests cover all accepted phone forms and the ambiguous `216` case,
Unicode length/control boundaries, every required field, stable governorates,
classic validation, incomplete Store API PATCH behavior, final POST rejection and
guest input preservation. On 2026-09-29 an isolated WordPress 7.1 / WooCommerce
11.1.2 site displayed the native guest checkout, French field labels, country and
governorate controls, COD and order summary with no browser errors. A synthetic
cart was used; no production data or email was sent. Final submitted values and
payment success remain part of the staging release matrix.
