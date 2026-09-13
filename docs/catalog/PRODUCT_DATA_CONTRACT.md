# Jouvence Para Product Data Contract — JP-CAT-001

## Scope

WooCommerce products are the canonical product entity for Jouvence Para. This contract adds validation and publication-readiness rules in `jouvence-para-core`; it does not introduce a parallel product table.

The launch catalog is expected to contain approximately 1,000 products. Product administration must therefore use indexed/native WooCommerce identifiers where available and bounded lookups for compatibility data.

## Canonical fields

| Business field | Canonical storage / API | Rule in JP-CAT-001 |
|---|---|---|
| Product name | `WC_Product::get_name()` | Required before publication |
| SKU | WooCommerce SKU property | Required before publication and globally unique |
| EAN / barcode | WooCommerce global unique ID on WooCommerce >= 9.1; compatibility meta `_jp_ean_barcode` on WooCommerce 9.0 | Optional; globally unique when supplied |
| French short description | WooCommerce short description | Short or full description required before publication |
| French full description | WooCommerce description | Short or full description required before publication |
| Regular price | WooCommerce regular price | Required, numeric and non-negative |
| Promotional price | WooCommerce sale price | Optional, numeric and non-negative |
| Stock quantity | WooCommerce stock quantity | Managed quantity required before publication; cannot be negative |
| Stock status | WooCommerce stock status | `instock` or `outofstock`; launch backorders are disabled |
| Publication status | WooCommerce/WordPress product status | `publish` and scheduled `future` states are blocked until publication readiness passes |
| Featured image | WooCommerce product image ID | Required before publication |
| Gallery | WooCommerce gallery image IDs | Supported; not required for publication |
| Package weight | WooCommerce weight | Optional, numeric and non-negative |
| Package dimensions | WooCommerce length/width/height | Optional, numeric and non-negative |
| Product category | WooCommerce `product_cat` assignment | At least one required before publication |
| Brand | Jouvence Para controlled `jp_brand` taxonomy | At least one required before publication; taxonomy registration belongs to JP-CAT-002 |

Consumer-facing net size/volume remains a controlled product attribute owned by JP-CAT-002. JP-CAT-001 deliberately does not create free-text size/volume attributes that would compete with that taxonomy/attribute contract.

## EAN compatibility

The repository declares WooCommerce 9.0 as its minimum version. WooCommerce added the native `global_unique_id` product property in 9.1. JP-CAT-001 therefore uses a compatibility adapter:

1. On WooCommerce 9.1 or later, read/write the native global unique ID.
2. On WooCommerce 9.0, store the normalized identifier as product meta `_jp_ean_barcode`.
3. When a site is upgraded, an existing compatibility value is migrated lazily to the native property on the next product save.
4. Uniqueness checks query both native and compatibility storage so mixed-version data cannot silently duplicate an identifier.

The compatibility-meta query is bounded to one matching product and excludes the product currently being updated.

## TND money precision

When the active WooCommerce currency is TND, Jouvence Para uses three decimal places. `TndMoney` normalizes values with string arithmetic rather than binary floating-point arithmetic. Non-TND stores retain the WooCommerce-configured decimal precision.

JP-CAT-001 does not add a business rule requiring a promotional price to be lower than the regular price because that rule was not approved for this issue.

## Publication readiness

A parent product may enter `publish` or scheduled `future` state only when all of the following are true:

- product name is present;
- unique SKU is present;
- regular price is present and valid;
- stock management is enabled;
- stock quantity is present and non-negative;
- stock status is valid;
- at least a short or full French description is present;
- a controlled brand is assigned;
- at least one category is assigned;
- a featured image is assigned.

If an administrator attempts publication with missing readiness data, the product is kept as a draft and WooCommerce admin displays actionable validation errors. Hard-invalid submitted values such as duplicate identifiers or negative money/stock are not persisted.

Programmatic product saves are validated through the WooCommerce product-object save hook. Hard-invalid data raises `WC_Data_Exception`; incomplete publication attempts are downgraded to draft.

Variation objects receive hard identifier, money, stock and measurement validation, but parent publication-readiness fields are not imposed on variations in this ticket.

## Administration and authorization

WooCommerce's classic product editor remains the administration boundary. Its product meta-box save handler verifies the `woocommerce_meta_nonce` and the user's `edit_post` capability before firing the product-processing hook. JP-CAT-001 verifies the same nonce and capability before processing its compatibility EAN field.

On WooCommerce versions that expose a native global-unique-ID field, Jouvence Para reuses it. On WooCommerce 9.0 only, the core plugin adds a translated EAN/barcode field to the inventory product-data panel.

## Acceptance criteria traceability

JP-CAT-001 is complete when all of the following are true:

- WooCommerce products remain the canonical product entity; no parallel product table is introduced.
- name, SKU, optional EAN/barcode, French descriptions, regular/promotional prices, stock quantity/status, publication status, featured/gallery images and package measurements are represented through the WooCommerce product model or the documented WooCommerce 9.0 compatibility metadata;
- duplicate non-empty SKU and EAN/barcode values are rejected;
- negative prices, stock quantities and package measurements are rejected;
- TND uses three-decimal monetary precision without binary-float normalization in Jouvence Para validation;
- backorders are forced off at the product-object boundary;
- publication/scheduling is prevented when required launch data is missing or invalid;
- administrative compatibility-field writes require WooCommerce's save nonce and the product edit capability;
- domain rules live in `jouvence-para-core`, with no theme changes;
- unit tests cover validation, uniqueness and money normalization, and integration tests cover WooCommerce hook registration, authorization, canonical mapping and failure behavior.

## Explicit non-goals

JP-CAT-001 does **not** implement:

- brand/category taxonomy registration or seeded terms (JP-CAT-002);
- controlled product attributes such as skin type, hair type, concern, SPF, size/volume or audience (JP-CAT-002);
- CSV import/export (JP-CAT-003 / issue #21);
- catalog search or indexing;
- storefront product/category design;
- competitor-authored content ingestion.

Product descriptions and media must continue to come from Jouvence Para or authorized manufacturer/supplier sources. The validator confirms description presence; it does not attempt automated language or medical-claim classification.
