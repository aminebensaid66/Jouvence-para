# Jouvence Para — Production Product & Technical Requirements Specification

**Document type:** Production product requirements + technical specification
**Project:** Jouvence Para
**Market:** Online parapharmacy — Tunisia
**Platform direction:** WordPress + WooCommerce, custom theme, custom business plugin
**Status:** Development baseline
**Version:** 2.0
**Date:** 13 September 2026

---

# 1. Purpose

This document is the primary implementation specification for Jouvence Para.

It defines:

- product scope;
- customer-facing features;
- back-office features;
- product and catalog data models;
- search and filtering behavior;
- inventory rules;
- cart and checkout behavior;
- shipping and payment architecture;
- order lifecycle;
- promotions;
- customer accounts;
- reviews, wishlist, loyalty and notifications;
- integrations;
- analytics;
- SEO;
- accessibility;
- security;
- privacy;
- infrastructure;
- deployment;
- observability;
- backup/disaster recovery;
- testing strategy;
- production acceptance criteria;
- unresolved business decisions that must be completed before the affected feature is implemented.

This is not an MVP specification. The system must be designed as a full production e-commerce platform from the beginning.

Requirements are prioritized as:

- **P0** — mandatory for production launch;
- **P1** — production feature, can be enabled after launch without redesigning the architecture;
- **P2** — advanced feature that the architecture must not block.

Where a business decision is not yet known, this document uses **TBD**. Developers must not invent a value for any TBD affecting money, stock, customer rights, legal obligations, payments, shipping or health-related content.

---

# 2. Product Vision

Jouvence Para must provide a fast, trustworthy and easy-to-understand online parapharmacy experience for customers in Tunisia.

Core positioning:

> Jouvence Para helps customers find authentic parapharmacy products suited to their needs through clear product information, reliable stock, simple discovery, human assistance and transparent delivery.

Primary brand principles:

1. **Trust**
   - authentic products;
   - identifiable suppliers;
   - real contact information;
   - transparent stock;
   - clear delivery and return policies.

2. **Clarity**
   - simple navigation;
   - controlled taxonomy;
   - consistent product naming;
   - complete pricing;
   - understandable product descriptions.

3. **Advice**
   - non-medical guidance;
   - routines;
   - contextual WhatsApp assistance;
   - educational content.

4. **Local relevance**
   - Tunisia-specific delivery;
   - TND pricing;
   - Tunisian phone/address validation;
   - French first, Arabic-ready architecture.

5. **Operational reliability**
   - correct stock;
   - deterministic order states;
   - duplicate-order protection;
   - auditable administrative actions.

---

# 3. Scope

## 3.1 Customer-facing scope

The production platform must support:

- homepage;
- responsive navigation;
- categories;
- brands;
- needs;
- promotions;
- product discovery;
- search autocomplete;
- search results;
- faceted filters;
- product pages;
- wishlist;
- back-in-stock subscriptions;
- cart;
- coupons;
- shipping estimation;
- checkout;
- cash on delivery;
- online payment integration architecture;
- customer accounts;
- order history;
- reorder;
- order tracking;
- return/complaint requests;
- verified reviews;
- contextual WhatsApp;
- content/guides;
- newsletter subscription;
- French interface;
- Arabic-ready content model and RTL compatibility;
- accessibility;
- SEO;
- analytics.

## 3.2 Staff-facing scope

The platform must support:

- product management;
- product import/export;
- categories, brands, needs and attributes;
- media;
- price and promotion management;
- inventory;
- order processing;
- shipment/tracking;
- returns and complaints;
- coupons;
- customer reviews;
- homepage merchandising;
- user roles and permissions;
- audit logs;
- configuration;
- reporting;
- analytics visibility;
- system monitoring.

---

# 4. Primary Users

## 4.1 Customer — needs-based shopper

Knows a need such as acne, pigmentation, dryness, sensitive skin, hair loss or sun protection, but may not know a product.

Needs:

- browse by need;
- meaningful filters;
- clear explanations;
- comparison alternatives;
- advice access;
- trustworthy availability.

## 4.2 Customer — exact-product shopper

Knows the product, brand, SKU or reference.

Needs:

- fast search;
- price;
- stock;
- fast checkout;
- reorder.

## 4.3 Parent

Needs:

- age/audience information;
- safety information;
- baby/mother categories;
- usage and warnings;
- reliable stock.

## 4.4 WhatsApp-assisted customer

Needs:

- contextual product or cart message;
- human response;
- preserved cart while asking for advice.

## 4.5 Catalog manager

Needs:

- controlled product data;
- bulk imports;
- pricing;
- images;
- categories/attributes;
- quality validation.

## 4.6 Order operator

Needs:

- order queue;
- confirmation workflow;
- shipment/tracking;
- internal notes;
- status transitions;
- return handling.

## 4.7 Administrator

Needs:

- system configuration;
- employee roles;
- integrations;
- logs;
- plugin/theme management;
- security and backups.

---

# 5. Technical Architecture

## 5.1 Required logical architecture

```text
Browser / Mobile Web
        |
        v
CDN / WAF / TLS
        |
        v
Web Server / Reverse Proxy
        |
        v
WordPress
  |-- Jouvence Para Theme
  |-- Jouvence Para Core Plugin
  |-- WooCommerce
  |-- Approved integration plugins
        |
        +--> MySQL / MariaDB
        +--> Redis / Object Cache
        +--> Search service if selected
        +--> Email service
        +--> WhatsApp/SMS provider
        +--> Payment gateway
        +--> Shipping/carrier integration
        +--> Object/media storage or CDN
        +--> Monitoring / error tracking
```

## 5.2 Responsibility boundaries

### Theme

The custom theme must be responsible for:

- templates;
- visual system;
- reusable UI components;
- responsive layouts;
- frontend behavior;
- WooCommerce presentation overrides;
- accessibility presentation;
- CSS/JS assets.

The theme must not own business-critical rules.

### Jouvence Para Core Plugin

Create a dedicated custom plugin, recommended slug:

```text
jouvence-para-core
```

It must own business logic such as:

- custom product taxonomies;
- custom product metadata;
- checkout validation;
- shipping rules;
- order workflow extensions;
- stock-related business hooks;
- WhatsApp contextual links;
- analytics events;
- back-office enhancements;
- integration adapters;
- API endpoints;
- custom scheduled jobs;
- audit logging.

Business-critical logic must not be placed only in `functions.php`.

### WooCommerce

WooCommerce remains the source of truth for:

- products;
- variations;
- prices;
- cart;
- coupons;
- customers;
- orders;
- taxes if applicable;
- base inventory;
- payment method framework;
- shipping method framework.

## 5.3 Environment separation

Mandatory environments:

- local development;
- staging;
- production.

Rules:

- each environment has separate credentials;
- production data must not be copied to local development without sanitization;
- staging must not send real marketing messages;
- staging payment integrations must use sandbox credentials;
- staging must be blocked from search engine indexing.

---

# 6. Repository Structure

Recommended structure:

```text
jouvence-para/
├── docker/
├── docs/
│   ├── architecture/
│   ├── adr/
│   ├── api/
│   ├── business-rules/
│   └── operations/
├── wordpress/
│   └── wp-content/
│       ├── themes/
│       │   └── jouvence-para/
│       └── plugins/
│           └── jouvence-para-core/
├── tests/
│   ├── unit/
│   ├── integration/
│   └── e2e/
├── scripts/
├── .env.example
├── docker-compose.yml
├── README.md
└── CHANGELOG.md
```

No production secret may exist in Git.

---

# 7. Architecture Decision Records

Important architectural decisions must be recorded under `docs/adr/`.

Minimum ADRs:

- ADR-001 — WordPress and WooCommerce;
- ADR-002 — custom theme / core-plugin separation;
- ADR-003 — product taxonomy model;
- ADR-004 — search technology;
- ADR-005 — inventory strategy;
- ADR-006 — shipping architecture;
- ADR-007 — payment abstraction;
- ADR-008 — multilingual strategy;
- ADR-009 — cache strategy;
- ADR-010 — media/CDN strategy.

Each ADR must contain:

- context;
- decision;
- alternatives considered;
- consequences;
- date;
- status.

---

# 8. Catalog Domain Model

## 8.1 Main domain entities

The platform conceptually contains:

```text
Product
ProductVariation
Brand
Category
Need
Ingredient
ProductAttribute
Supplier
Inventory
Promotion
Coupon
Customer
Address
Cart
CartItem
Order
OrderItem
Payment
Shipment
ReturnRequest
Complaint
Review
Wishlist
BackInStockSubscription
LoyaltyAccount
LoyaltyTransaction
ContentArticle
```

WooCommerce may provide the physical storage for several entities. The business semantics defined here remain authoritative.

---

# 9. Product Data Model

## 9.1 Required product fields

### Identity

- internal product ID;
- SKU;
- EAN/GTIN when available;
- normalized product name;
- slug;
- brand;
- range/line;
- product type;
- variation parent if applicable.

### Commercial

- regular price in TND;
- sale price;
- sale start;
- sale end;
- tax status;
- product status;
- purchasable status.

### Inventory

- stock quantity;
- stock management enabled/disabled;
- low-stock threshold;
- stock status;
- backorder policy;
- supplier reference.

### Classification

- category;
- subcategory;
- needs;
- audience;
- age group;
- skin type;
- hair type;
- scalp type where applicable;
- body/usage zone;
- active ingredients;
- texture;
- formulation attributes.

### Content

- short description;
- long description;
- primary benefit;
- key benefits;
- indications;
- usage instructions;
- usage frequency;
- ingredient/INCI text;
- active ingredients;
- precautions;
- manufacturer warnings;
- size/volume/weight;
- images;
- optional video.

### Merchandising

- featured;
- new;
- bestseller;
- promotion badge;
- related products;
- complementary products;
- upsell products;
- manual ranking.

### SEO

- meta title;
- meta description;
- canonical behavior;
- image alt text;
- structured data eligibility.

### Internal traceability

- supplier;
- supplier product ID;
- import source;
- last content update;
- last stock update;
- last price update;
- internal notes;
- lot/expiry fields only if the business operationally manages them.

## 9.2 Product naming convention

Required convention:

```text
Brand + Range + Product + Format
```

Example:

```text
CeraVe Foaming Cleanser 473 ml
```

Canonical names must be normalized before import.

## 9.3 Controlled values

The following must not be arbitrary free text if used for navigation/filtering:

- brands;
- needs;
- categories;
- skin types;
- hair types;
- active ingredients;
- age groups;
- textures;
- audience;
- usage zones.

Controlled values must have:

- stable internal slug;
- French label;
- optional Arabic label;
- optional synonyms.

---

# 10. Product Taxonomy

## 10.1 Main commercial navigation

Target categories:

1. Face;
2. Hair;
3. Body & Hygiene;
4. Sun Care;
5. Baby & Mother;
6. Food Supplements;
7. Equipment & Well-being, if sold;
8. Brands;
9. Promotions;
10. Advice.

Final category availability depends on the real catalog.

## 10.2 Needs taxonomy

Examples:

- acne & imperfections;
- dark spots & radiance;
- dry/sensitive skin;
- anti-aging;
- hair loss;
- dandruff/scalp;
- sun protection;
- fatigue & immunity;
- pregnancy/mother/baby.

A product may belong to:

- one primary commercial category;
- additional commercial categories if justified;
- zero or more needs;
- one brand;
- multiple controlled attributes.

Do not duplicate a product page solely to place it in multiple taxonomies.

---

# 11. Search

## REQ-SEARCH-001 — Search scope — P0

Search must support:

- product name;
- brand;
- SKU;
- EAN/GTIN;
- category;
- need;
- active ingredient;
- important synonyms.

## REQ-SEARCH-002 — Normalization — P0

Search must be:

- case-insensitive;
- accent-tolerant;
- tolerant of common spacing/hyphen variations;
- tolerant of simple typing errors.

## REQ-SEARCH-003 — Autocomplete — P0

Autocomplete must display a bounded number of results containing, where available:

- product image;
- name;
- brand;
- price;
- stock state.

Autocomplete may also return:

- brands;
- categories;
- needs.

## REQ-SEARCH-004 — Ranking — P0

Ranking must favor:

1. exact product-name matches;
2. exact SKU/EAN matches;
3. brand + name matches;
4. active ingredient / need matches;
5. category matches;
6. descriptive-content matches.

The final search engine must expose configurable relevance weights.

## REQ-SEARCH-005 — Zero-result behavior — P0

A zero-result page must:

- preserve the query;
- suggest correction where possible;
- display relevant categories or popular products;
- provide a WhatsApp/contact path;
- emit a `search_no_results` analytics event.

## REQ-SEARCH-006 — Search analytics — P0

Track:

- query;
- result count;
- clicked result;
- zero-result occurrence.

No sensitive customer information may be added to analytics payloads.

## REQ-SEARCH-007 — Search engine decision — P0 / TBD

A production decision must be recorded before implementation:

- enhanced database-backed search;
- OpenSearch/Elasticsearch;
- Algolia;
- Meilisearch;
- another approved engine.

Basic WordPress search is not assumed to satisfy the final production requirement.

---

# 12. Category Pages and Filtering

## REQ-FILTER-001 — Responsive product grid — P0

Product-list pages must be mobile-first and responsive.

## REQ-FILTER-002 — Supported filters — P0

Global filters may include:

- brand;
- price;
- need;
- skin/hair type;
- active ingredient;
- availability;
- promotion.

Filter availability must be category-aware.

## REQ-FILTER-003 — Filter logic — P0

Default logical model:

- values within one filter group: **OR**;
- separate filter groups: **AND**.

Example:

```text
Brand = A OR B
AND
Need = Acne
AND
Availability = In stock
```

## REQ-FILTER-004 — Active filter state — P0

The interface must show:

- active filters;
- result count;
- remove-single-filter action;
- clear-all action.

## REQ-FILTER-005 — Sorting — P0

Provide:

- relevance;
- best sellers;
- newest;
- price ascending;
- price descending.

## REQ-FILTER-006 — URL behavior — P0

Filtered views must have deterministic URLs.

SEO rules must prevent indexation of low-value filter combinations.

---

# 13. Product Cards

## REQ-CARD-001 — Required fields — P0

Product cards must show:

- product image;
- brand;
- name;
- format/size when meaningful;
- regular/current price;
- discount information if active;
- stock state;
- action to open product;
- quick add where applicable.

## REQ-CARD-002 — Out-of-stock — P0

Out-of-stock products must:

- be clearly marked;
- not expose a normal purchase action;
- offer back-in-stock subscription if enabled.

---

# 14. Product Detail Page

## REQ-PRODUCT-001 — Core information — P0

Every product page must contain, when the data exists:

- normalized name;
- brand;
- format;
- gallery;
- price;
- previous/reference price when legally valid;
- discount amount/percentage;
- current stock state;
- quantity;
- add-to-cart;
- main benefit;
- indications;
- target profile;
- usage;
- ingredients/INCI;
- precautions;
- texture;
- skin/hair type;
- zone;
- age;
- SKU;
- EAN/GTIN.

## REQ-PRODUCT-002 — Gallery — P0

Images must:

- be optimized;
- support responsive sizes;
- support zoom/lightbox where accessible;
- have meaningful alt text;
- avoid loading unnecessary full-size images initially.

## REQ-PRODUCT-003 — Delivery visibility — P0

The page must expose either:

- delivery estimate/cost based on available context; or
- a clear link to shipping information.

## REQ-PRODUCT-004 — WhatsApp advice — P0

A contextual WhatsApp action must generate a prefilled message containing:

- product name;
- product URL.

It must not automatically send any message.

## REQ-PRODUCT-005 — Related/complementary items — P0

Related products and complementary products must be separate concepts.

Recommendations must not be random when manually curated data is available.

## REQ-PRODUCT-006 — Medical claims — P0

Product content must not contain invented medical claims or unverified healing promises.

Content must remain consistent with:

- manufacturer information;
- approved internal content policy;
- applicable law.

## REQ-PRODUCT-007 — Structured data — P0

Product/Offer/AggregateRating structured data may be emitted only when corresponding visible data exists.

---

# 15. Wishlist

## REQ-WISH-001 — Wishlist — P1

Authenticated customers must be able to:

- add/remove items;
- access their list;
- add available products to cart.

Anonymous wishlist behavior is optional, but if implemented must define merge behavior on login.

---

# 16. Back-in-Stock Subscriptions

## REQ-STOCKALERT-001 — Subscription — P1

A customer may request an alert for an unavailable product.

The system must store:

- product;
- variation when applicable;
- contact destination;
- consent/timestamp;
- status.

## REQ-STOCKALERT-002 — Notification — P1

Notifications must be sent only when:

- the product becomes purchasable;
- consent requirements are satisfied.

Repeated notifications must be rate-limited.

---

# 17. Inventory

## REQ-STOCK-001 — Source of truth — P0

WooCommerce inventory is the initial source of truth unless an external inventory/POS integration is formally selected.

## REQ-STOCK-002 — Overselling — P0

Overselling must be disabled by default.

Any backorder capability must be explicitly enabled per product/business rule.

## REQ-STOCK-003 — Low stock — P0

Each product/variation may have:

- global default threshold;
- per-product override.

Low-stock alerts must be available to authorized staff.

## REQ-STOCK-004 — Reservation/decrement policy — P0 / TBD

Before checkout implementation, define exactly:

- when stock is reserved;
- when stock is decremented;
- when reservation expires;
- when stock is restored.

No developer may assume these rules.

## REQ-STOCK-005 — Cancellation — P0

Stock restoration must depend on order status and physical reality.

Example issue that must be formalized:

- a parcel refused by the customer must not necessarily re-enter available stock until physically returned and inspected.

## REQ-STOCK-006 — Audit — P0

Manual inventory changes must record:

- staff user;
- product;
- old value;
- new value;
- reason if provided;
- timestamp.

## REQ-STOCK-007 — Physical-store synchronization — P0 / TBD

If online stock is shared with a physical store, an inventory synchronization architecture is mandatory before launch.

---

# 18. Cart

## REQ-CART-001 — Cart operations — P0

Users must be able to:

- add products;
- update quantity;
- remove products;
- view line totals;
- view subtotal;
- view discounts;
- view estimated shipping;
- view final total when calculable.

## REQ-CART-002 — Persistent cart — P0

Authenticated customers must retain cart data across sessions.

Anonymous-cart persistence duration: **TBD**.

Login/cart-merge behavior must be explicitly tested.

## REQ-CART-003 — Stock revalidation — P0

Stock and price must be revalidated:

- when cart is loaded;
- before checkout;
- during final order creation.

## REQ-CART-004 — Free-shipping progress — P0 if free threshold exists

If a free-shipping threshold exists:

- show current progress;
- calculate it according to a documented basis;
- never misrepresent eligibility.

Threshold amount and calculation base: **TBD**.

## REQ-CART-005 — Upsells — P0

Limit cart recommendations to a small number of relevant products.

## REQ-CART-006 — Coupons — P0

Coupon application must show:

- accepted/rejected state;
- discount amount;
- reason on rejection where safe.

---

# 19. Checkout

## REQ-CHECKOUT-001 — Guest checkout — P0

Guest checkout is enabled by default.

Account creation may be proposed but must not be mandatory unless a future business rule explicitly changes this.

## REQ-CHECKOUT-002 — Required fields — P0

Minimum data model:

```yaml
first_name:
  required: true
  max_length: 80

last_name:
  required: true
  max_length: 80

phone:
  required: true
  country: TN
  normalized_format: E.164

email:
  required: true

address_line_1:
  required: true
  max_length: 180

governorate:
  required: true
  type: controlled_value

delegation_or_locality:
  required: true
  type: controlled_value_or_validated_text

postal_code:
  required: false

order_notes:
  required: false
  max_length: 500
```

Final field rules may be adjusted to carrier requirements.

## REQ-CHECKOUT-003 — Validation — P0

Validation must exist server-side even if frontend validation is present.

Invalid data must return field-specific errors without discarding valid customer input.

## REQ-CHECKOUT-004 — Tunisian phone — P0

Phone numbers must be normalized to a consistent internal representation.

Validation must not reject legitimate Tunisian formats simply because the user includes spaces or a country prefix.

## REQ-CHECKOUT-005 — Shipping cost — P0

Customer must see shipping cost before final order confirmation.

## REQ-CHECKOUT-006 — Terms — P0

Terms acceptance:

- required;
- not prechecked;
- linked to the applicable terms.

## REQ-CHECKOUT-007 — Order summary — P0

Before confirmation show:

- products;
- quantities;
- discounts;
- shipping;
- total;
- payment method;
- delivery information.

## REQ-CHECKOUT-008 — Duplicate protection — P0

Order creation must be idempotent against:

- double click;
- retry after slow response;
- page refresh;
- duplicate frontend submission.

## REQ-CHECKOUT-009 — Error recovery — P0

Checkout failure must:

- preserve cart;
- preserve non-sensitive form information when possible;
- clearly explain next action;
- emit an internal error event.

---

# 20. Tunisian Geography and Shipping Zones

## REQ-GEO-001 — Structured geography — P0

The system must use a controlled Tunisia geography model for shipping where practical:

```text
Governorate
  -> Delegation
     -> Locality
```

Exact depth depends on carrier rules.

## REQ-GEO-002 — Stable identifiers — P0

Shipping rules must reference stable internal location IDs, not only user-entered labels.

## REQ-GEO-003 — Shipping matrix — P0 / TBD

Business must provide:

- supported governorates/regions;
- shipping rate per region/rule;
- free-shipping threshold;
- exceptions;
- estimated delivery windows;
- remote-area rules if any.

These values must not be invented by development.

---

# 21. Shipping

## REQ-SHIP-001 — Shipping model — P0

The system must model:

```text
ShippingZone
ShippingMethod
ShippingRate
Shipment
TrackingData
```

## REQ-SHIP-002 — Shipment data — P0

Shipment record must support:

- carrier;
- tracking number;
- tracking URL;
- shipment status;
- shipped timestamp;
- delivered timestamp;
- estimated delivery;
- cost.

## REQ-SHIP-003 — Tracking — P0

Where the carrier supports tracking:

- expose tracking to staff;
- expose safe tracking information to customer;
- do not require free-text notes as the only storage.

## REQ-SHIP-004 — Carrier integration failure — P0

If a carrier API becomes unavailable:

- order data must not be lost;
- failure must be logged;
- staff must have a recovery/manual workflow.

---

# 22. Payments

## REQ-PAY-001 — Cash on delivery — P0

COD must be available at launch unless the business explicitly changes this decision.

COD rules requiring business confirmation:

- maximum order value, if any;
- geographic restrictions, if any;
- confirmation requirements;
- refused-parcel handling.

## REQ-PAY-002 — Payment abstraction — P0

Payment method must be modeled independently of order state.

Required payment data:

- method;
- payment status;
- provider;
- provider transaction ID;
- paid time;
- refund status.

## REQ-PAY-003 — Online payment — P1

The architecture must support a validated Tunisian online payment provider without redesigning the order model.

Provider: **TBD**.

## REQ-PAY-004 — Card data — P0

Jouvence Para infrastructure must not store raw payment-card data.

Use provider-hosted or provider-compliant payment components.

## REQ-PAY-005 — Webhook safety — P1

Payment webhooks must support:

- signature verification;
- duplicate-event handling;
- idempotency;
- retries;
- event logging;
- safe failure handling.

---

# 23. Order Lifecycle

## 23.1 Required order statuses

Business-facing lifecycle should support at least:

```text
NEW
PENDING_CONFIRMATION
CONFIRMED
PREPARING
READY_FOR_SHIPMENT
SHIPPED
DELIVERED
CANCELLED
REFUSED
RETURN_REQUESTED
RETURNED
REFUNDED
```

Mapping to WooCommerce native/custom statuses must be documented.

## REQ-ORDER-001 — Controlled transitions — P0

Orders must follow permitted transitions.

Staff must not arbitrarily jump between incompatible statuses unless an administrator override is explicitly supported and audited.

## REQ-ORDER-002 — Side effects — P0

Each transition must have documented side effects.

Examples:

- confirmation notification;
- inventory reservation/commit;
- shipment requirement;
- review eligibility;
- stock restoration;
- refund processing.

Exact inventory side effects remain dependent on REQ-STOCK-004.

## REQ-ORDER-003 — Internal notes — P0

Staff may add internal notes not visible to customers.

Customer-visible notes must be clearly distinct.

## REQ-ORDER-004 — Order history — P0

Authorized staff must be able to inspect:

- status history;
- payment data;
- shipment data;
- notes;
- stock-relevant events;
- customer communications where integrated;
- audit trail.

---

# 24. Order Confirmation

## REQ-CONFIRM-001 — Confirmation page — P0

After successful order creation show:

- order number;
- summary;
- payment method;
- delivery summary;
- next steps.

## REQ-CONFIRM-002 — Email — P0

Customer receives transactional order confirmation email.

Admin/order team receives appropriate operational notification.

## REQ-CONFIRM-003 — Messaging — P1

SMS/WhatsApp transactional confirmation may be enabled after:

- provider integration;
- consent/legal review;
- template approval;
- retry and delivery monitoring.

---

# 25. Customer Accounts

## REQ-ACCOUNT-001 — Authentication — P0

Support:

- registration;
- login;
- logout;
- password reset.

## REQ-ACCOUNT-002 — Account data — P0

Customer can manage:

- personal details;
- saved addresses;
- password;
- communication preferences where applicable.

## REQ-ACCOUNT-003 — Orders — P0

Customer can:

- view own orders;
- view order details;
- view status;
- view tracking;
- reorder eligible previous orders.

## REQ-ACCOUNT-004 — Authorization — P0

A customer must never be able to read or modify another customer's order or personal information.

## REQ-ACCOUNT-005 — Data rights — P0

Support applicable workflows for:

- data access;
- correction;
- export;
- deletion/anonymization as legally appropriate.

---

# 26. Reviews

## REQ-REVIEW-001 — Moderation — P1

Reviews must be moderated according to an internal policy.

## REQ-REVIEW-002 — Verified purchase — P1

A review may be labeled verified only when a completed/delivered eligible order can be associated with the reviewer.

## REQ-REVIEW-003 — Abuse prevention — P1

Provide spam and abuse controls.

## REQ-REVIEW-004 — Aggregate rating — P1

Structured AggregateRating must only reflect real, visible reviews.

---

# 27. Promotions and Coupons

## REQ-PROMO-001 — Promotion types — P0/P1

Architecture must support:

- percentage discount;
- fixed discount;
- sale price;
- free shipping;
- coupon;
- bundle/pack discount;
- gift-with-purchase.

Some may be enabled after launch.

## REQ-PROMO-002 — Scheduling — P0

Promotions must support start/end date and timezone-aware evaluation.

## REQ-PROMO-003 — Eligibility — P0

Support rules such as:

- product;
- category;
- minimum cart;
- customer eligibility where legally/business appropriate;
- global usage limit;
- customer usage limit;
- exclusions.

## REQ-PROMO-004 — Stacking — P0 / TBD

Business must define compatibility between:

- sale prices;
- coupons;
- bundles;
- gifts;
- free shipping.

The system must prevent unintended stacking.

## REQ-PROMO-005 — Reference price — P0

Previous/strikethrough price may be displayed only when valid under applicable pricing rules.

---

# 28. Packs and Routines

## REQ-PACK-001 — Component-based stock — P1

Where a pack is composed of existing products, availability should be calculated from component availability unless the pack has independent physical stock.

## REQ-PACK-002 — Pack content — P1

Product page must clearly identify:

- included products;
- quantities;
- regular combined price if shown;
- pack price;
- savings if valid.

---

# 29. Loyalty

## REQ-LOYALTY-001 — Ledger model — P2

If loyalty is implemented, use a ledger rather than a single mutable balance.

A loyalty transaction should record:

- customer;
- points delta;
- reason;
- related order;
- timestamp;
- expiry when applicable.

## REQ-LOYALTY-002 — Rules — P2 / TBD

Business must define:

- earning rate;
- redemption rate;
- expiration;
- excluded items;
- behavior on refund/return;
- minimum redemption.

---

# 30. Returns

## REQ-RETURN-001 — Structured request — P0/P1

Return requests must contain:

- order;
- item;
- quantity;
- reason;
- request status;
- timestamp;
- optional evidence/photos.

## REQ-RETURN-002 — Return statuses

Recommended:

```text
REQUESTED
UNDER_REVIEW
APPROVED
REJECTED
RECEIVED
REFUNDED
CLOSED
```

## REQ-RETURN-003 — Hygiene/safety — P0 / TBD

Return eligibility for hygiene/safety-sensitive products must be legally validated and published.

No rule may be invented by developers.

---

# 31. Complaints / SAV

## REQ-SAV-001 — Complaint form — P0

Support:

- order number;
- customer contact;
- issue category;
- description;
- photo attachments where needed.

## REQ-SAV-002 — Attachment security — P0

Uploads must have:

- type validation;
- size limit;
- safe filename handling;
- storage outside executable paths or equivalent protection;
- malware/security consideration.

---

# 32. WhatsApp

## REQ-WA-001 — Global contact — P0

A visible but non-intrusive WhatsApp entry point must be available.

## REQ-WA-002 — Contextual product message — P0

Prefill product name + URL.

## REQ-WA-003 — Cart context — P1

If cart-sharing is implemented, do not expose private/sensitive data in public URLs.

## REQ-WA-004 — Response expectations — P0

Business must provide:

- real WhatsApp number;
- operating hours;
- expected response wording.

Values: **TBD**.

---

# 33. Newsletter and Marketing Consent

## REQ-MKT-001 — Consent — P0

Marketing subscription must require explicit consent.

No marketing consent checkbox may be preselected.

## REQ-MKT-002 — Transactional separation — P0

Transactional messages must be technically and conceptually separate from marketing messages.

## REQ-MKT-003 — Unsubscribe — P0

Marketing emails must support unsubscribe and preference handling as required.

---

# 34. Back Office

## REQ-ADMIN-001 — Products — P0

Authorized users must manage:

- products;
- variations;
- taxonomies;
- product attributes;
- content;
- media;
- price;
- promotion;
- stock.

## REQ-ADMIN-002 — Bulk import/export — P0

CSV import/export must:

- use documented headers;
- validate controlled values;
- report row errors;
- not silently create duplicate brands/attributes;
- support dry-run/validation where feasible;
- preserve stable IDs/SKUs.

## REQ-ADMIN-003 — Orders — P0

Authorized users must:

- filter/search orders;
- change valid statuses;
- add notes;
- access shipping/tracking;
- access return data;
- print preparation documents as required.

## REQ-ADMIN-004 — Homepage merchandising — P0

Authorized content/marketing users should manage:

- banners;
- featured categories;
- featured brands;
- merchandising blocks;

without source-code changes.

---

# 35. Staff Roles and Permissions

Recommended logical roles:

- Administrator;
- Catalog Manager;
- Order Manager;
- Customer Support;
- Marketing Manager.

Permission matrix must be finalized before staff onboarding.

Minimum principle:

> No user receives WordPress Administrator privileges merely to perform day-to-day operational work.

Sensitive operations include:

- plugin installation;
- user/role changes;
- refunds;
- payment configuration;
- shipping configuration;
- global price changes;
- security settings.

---

# 36. Audit Logging

## REQ-AUDIT-001 — Logged actions — P0

Log at minimum:

- price changes;
- stock changes;
- promotion changes;
- order status changes;
- refund actions;
- customer-record administrative changes;
- coupon changes;
- role/user changes;
- shipping configuration changes;
- payment configuration changes.

## REQ-AUDIT-002 — Audit fields — P0

Record:

- actor;
- action;
- object;
- previous value when feasible;
- new value when feasible;
- timestamp;
- environment.

Audit logs must not contain secrets or raw payment data.

---

# 37. API and Integration Requirements

Each external integration must document:

- authentication;
- sandbox and production endpoints;
- secrets;
- timeout;
- retry strategy;
- rate limit;
- idempotency;
- webhook behavior;
- error handling;
- log redaction;
- operational fallback.

Potential integrations:

- payment gateway;
- carrier;
- SMTP/email provider;
- WhatsApp;
- SMS;
- analytics;
- search;
- error tracking;
- CDN;
- backup storage.

---

# 38. Webhooks

## REQ-WEBHOOK-001 — Verification — P0/P1

For providers that support signed webhooks:

- verify signature;
- verify timestamp where available;
- reject invalid events.

## REQ-WEBHOOK-002 — Idempotency — P0/P1

Repeated webhook events must not create duplicate side effects.

## REQ-WEBHOOK-003 — Persistence — P0/P1

Store enough event metadata to diagnose processing failures without storing unnecessary sensitive data.

---

# 39. Idempotency

Operations that require idempotency safeguards:

- order creation;
- payment confirmation;
- payment refund;
- shipment updates;
- stock synchronization;
- coupon redemption;
- transactional notification jobs where duplicate messages matter.

Idempotency design must be explicit for each integration.

---

# 40. Multilingual Architecture

## REQ-I18N-001 — French launch — P0

French is the primary launch language unless business changes this decision.

## REQ-I18N-002 — Arabic-ready — P0 architecture

Even if Arabic is enabled later, architecture must support:

- translated products;
- translated categories;
- translated needs;
- translated attributes;
- translated content;
- translated SEO metadata;
- RTL interface;
- Arabic search normalization;
- hreflang.

The chosen multilingual implementation must be documented in an ADR.

---

# 41. Content and Health-Safety Rules

## REQ-CONTENT-001 — Source consistency — P0

Product claims must be based on approved product/manufacturer information.

## REQ-CONTENT-002 — No diagnosis — P0

The platform must not present automated content as medical diagnosis.

## REQ-CONTENT-003 — Advice boundaries — P0

Non-medical routines and educational content must avoid unsupported treatment/guarantee claims.

## REQ-CONTENT-004 — Content owner — P0 / TBD

Business must identify who validates:

- descriptions;
- ingredients;
- precautions;
- health-related statements;
- guides.

---

# 42. SEO

## REQ-SEO-001 — URL structure — P0

Prefer stable structures such as:

```text
/categorie/...
/marque/...
/produit/...
/conseils/...
```

Final slugs must align with the multilingual strategy.

## REQ-SEO-002 — Metadata — P0

Every indexable primary page must support:

- title;
- meta description;
- canonical;
- social metadata where appropriate.

## REQ-SEO-003 — Structured data — P0

Support valid:

- Organization;
- BreadcrumbList;
- Product;
- Offer;
- AggregateRating when real;
- Article.

## REQ-SEO-004 — Faceted navigation — P0

Define:

- indexable filter pages;
- non-indexable parameter combinations;
- canonical behavior;
- crawl policy.

## REQ-SEO-005 — Removed products — P0

Product removal must follow a documented policy:

- replacement redirect when relevant;
- preserve page for temporarily unavailable products;
- 410/404 only when appropriate.

---

# 43. Analytics

## 43.1 Required events

Track:

- search;
- search_no_results;
- category_view;
- filter_use;
- product_view;
- whatsapp_click;
- add_to_cart;
- remove_from_cart;
- cart_view;
- checkout_start;
- checkout_error;
- shipping_selected;
- payment_selected;
- purchase;
- coupon_applied;
- coupon_rejected;
- newsletter_signup;
- back_in_stock_request;
- account_registration;
- reorder.

## 43.2 Event contract

Analytics events must use stable schemas.

Example:

```json
{
  "event": "product_view",
  "product_id": 123,
  "sku": "ABC-123",
  "brand": "Example",
  "category": "Face",
  "price": 45.900,
  "currency": "TND"
}
```

Do not transmit unnecessary personally identifying information.

## 43.3 Business KPIs

Measure:

- conversion rate;
- mobile conversion;
- product-view to cart rate;
- cart to checkout;
- checkout completion;
- average order value;
- revenue per visit;
- confirmed order rate;
- delivered order rate;
- refusal rate;
- return rate;
- repeat purchase at 30/60/90 days;
- out-of-stock rate;
- zero-result search rate;
- gross margin after promotion and shipping when data exists;
- customer-service tickets per 100 orders.

---

# 44. Accessibility

Target: WCAG 2.2 AA.

Requirements:

- full keyboard operation;
- visible focus;
- semantic headings;
- labels associated with fields;
- programmatic error messages;
- sufficient contrast;
- touch-friendly targets;
- meaningful alt text;
- no essential interaction dependent only on hover;
- respect `prefers-reduced-motion`;
- usable at 200% zoom;
- accessible dialogs;
- accessible cart/checkout updates;
- screen-reader announcement for important dynamic changes.

Accessibility tests are part of release acceptance.

---

# 45. Performance

## 45.1 Frontend targets

75th percentile Core Web Vitals targets:

- LCP <= 2.5 s;
- INP <= 200 ms;
- CLS <= 0.1.

## 45.2 Asset rules

- responsive images;
- WebP/AVIF where supported;
- lazy-load below-the-fold media;
- optimize above-the-fold image loading;
- minimize fonts;
- minimize third-party scripts;
- code splitting where applicable;
- avoid render-blocking assets where possible.

## 45.3 Backend targets

Initial targets, subject to production capacity testing:

- cached HTML TTFB target < 500 ms;
- dynamic page TTFB target < 1 s;
- search response p95 target < 500 ms;
- add-to-cart response p95 target < 800 ms;
- checkout creation p95 target < 2 s excluding slow third-party provider behavior.

Final SLIs/SLOs must be validated against hosting and traffic expectations.

## 45.4 Capacity — TBD

Business/engineering must define expected:

- catalog size;
- monthly traffic;
- concurrent visitors;
- peak orders/hour;
- image volume.

Load tests must use realistic targets.

---

# 46. Caching

## REQ-CACHE-001 — Layers — P0

Support appropriate:

- browser cache;
- CDN cache;
- page cache;
- object cache;
- media cache.

## REQ-CACHE-002 — Sensitive pages — P0

Do not full-page cache personalized transactional pages such as:

- cart;
- checkout;
- account;
- order confirmation.

## REQ-CACHE-003 — Invalidation — P0

Cache invalidation must account for:

- price update;
- stock update;
- product update;
- promotion start/end;
- relevant configuration changes.

---

# 47. Media

## REQ-MEDIA-001 — Product images — P0

Define standard image dimensions and quality.

Store original/high-quality assets while serving optimized renditions.

## REQ-MEDIA-002 — File names — P0

Use human-readable normalized file naming where practical.

## REQ-MEDIA-003 — CDN/storage — P0 / TBD

Document whether production media is stored:

- on web server;
- object storage;
- external media/CDN.

---

# 48. Security

## REQ-SEC-001 — TLS — P0

HTTPS everywhere.

Enable HSTS when production is correctly configured.

## REQ-SEC-002 — Admin security — P0

- 2FA for administrators;
- least-privilege roles;
- brute-force protection;
- strong password policy;
- session controls.

## REQ-SEC-003 — WordPress hardening — P0

- disable built-in file editing in production;
- minimize plugins;
- keep core/plugins/themes updated under controlled release process;
- restrict unused interfaces such as XML-RPC when not required;
- protect login/admin endpoints;
- monitor privileged user creation.

## REQ-SEC-004 — Application security — P0

All custom code must use:

- input validation;
- sanitization;
- output escaping;
- prepared queries;
- CSRF protection/nonces;
- authorization checks;
- file-upload validation.

## REQ-SEC-005 — Security headers — P0

Configure as appropriate:

- Content-Security-Policy;
- HSTS;
- X-Content-Type-Options;
- Referrer-Policy;
- frame protection through CSP/frame-ancestors;
- secure cookie flags.

## REQ-SEC-006 — Secrets — P0

Secrets must never be stored in Git or exposed to frontend JS.

Use environment/secrets management for:

- database credentials;
- payment keys;
- SMTP credentials;
- WhatsApp tokens;
- carrier tokens;
- monitoring keys.

## REQ-SEC-007 — Dependency scanning — P0

Continuously monitor known vulnerabilities in:

- WordPress;
- WooCommerce;
- plugins;
- themes;
- frontend dependencies;
- server/runtime dependencies.

## REQ-SEC-008 — Sensitive logging — P0

Do not log:

- passwords;
- session secrets;
- raw card data;
- access tokens;
- unnecessary personal data.

---

# 49. Privacy and Legal Compliance

Business/legal review is mandatory for:

- privacy notice;
- cookies;
- marketing consent;
- terms and conditions;
- returns;
- invoices;
- product claims;
- health-related advertising;
- online-sale restrictions;
- retention periods.

Technical implementation must support:

- consent records;
- cookie preferences;
- access/correction workflows;
- data deletion/anonymization where applicable;
- minimal data collection;
- configurable retention.

No statement in this document replaces Tunisian legal advice.

---

# 50. Cookies and Tracking

## REQ-COOKIE-001 — Non-essential tracking — P0

Non-essential analytics/marketing trackers must not run before required consent.

## REQ-COOKIE-002 — Preference management — P0

Customers must be able to review/change tracking preferences.

## REQ-COOKIE-003 — Essential functionality — P0

Consent refusal must not break:

- catalog;
- cart;
- checkout;
- account;
- order processing.

---

# 51. Email

## REQ-EMAIL-001 — Dedicated mail delivery — P0

Use a reliable transactional email provider/SMTP configuration rather than relying on unmanaged server mail.

## REQ-EMAIL-002 — Deliverability — P0

Production domain must have appropriate DNS records such as:

- SPF;
- DKIM;
- DMARC policy as appropriate.

## REQ-EMAIL-003 — Templates — P0

Transactional email templates must be branded and tested on common clients.

---

# 52. Background Jobs

Time-consuming tasks should use background/scheduled processing where appropriate:

- stock alert notifications;
- email retry;
- external synchronization;
- search indexing;
- cleanup;
- analytics exports;
- webhook retry.

Jobs must support:

- retry;
- failure logging;
- duplicate protection;
- visibility for administrators.

---

# 53. Database

## REQ-DB-001 — Encoding — P0

Use `utf8mb4` or equivalent full Unicode support.

## REQ-DB-002 — Time — P0

Store machine timestamps consistently and render using configured business timezone.

Business timezone: **Africa/Tunis** unless deployment/business requirements specify otherwise.

## REQ-DB-003 — Migrations — P0

Custom plugin schema changes must be versioned and upgrade-safe.

Example:

```text
plugin_version
schema_version
```

## REQ-DB-004 — Indexing — P0

Custom tables/queries must be indexed based on actual query patterns.

## REQ-DB-005 — Slow queries — P0

Production must support slow-query investigation.

---

# 54. Coding Standards

## PHP

- supported PHP 8.x version aligned with production hosting and WooCommerce;
- WordPress Coding Standards;
- typed classes where practical;
- no direct unsanitized SQL;
- nonce/authorization checks;
- escaping at output.

## JavaScript

- ESLint;
- consistent formatting;
- modular code;
- no unnecessary global state;
- progressive enhancement where practical.

## CSS

- design tokens;
- reusable components;
- responsive strategy;
- no arbitrary duplicated style systems.

## Source control

- all code changes via Git;
- meaningful commits;
- no production edits through WordPress file editor;
- releases tagged/versioned.

---

# 55. CI/CD

Required production pipeline concept:

```text
commit/push
  -> lint
  -> static analysis
  -> unit tests
  -> integration tests
  -> asset build
  -> security checks
  -> deploy staging
  -> E2E/smoke tests
  -> production approval
  -> pre-deploy backup/check
  -> deploy production
  -> production smoke test
```

Production deployments must be reproducible.

Rollback procedure must exist.

---

# 56. Testing Strategy

## 56.1 Unit tests

Required for custom business logic including:

- shipping calculations;
- promotion eligibility;
- validation;
- order-state rules;
- stock helpers;
- normalization utilities.

## 56.2 Integration tests

Cover:

- WooCommerce hooks;
- database;
- email adapter;
- search adapter;
- carrier adapter;
- payment adapter;
- webhook processing.

## 56.3 End-to-end tests

Minimum automated/controlled E2E flows:

1. Search -> product -> cart -> guest checkout -> order;
2. Category -> filters -> product;
3. Coupon success;
4. Coupon failure;
5. Out-of-stock product;
6. Stock change while product is in cart;
7. checkout validation error;
8. double-click order submission;
9. account registration/login;
10. password recovery;
11. reorder;
12. order cancellation;
13. shipment/tracking visibility;
14. return request;
15. back-in-stock request if enabled.

## 56.4 Browser/device matrix

At minimum validate:

- iPhone/Safari;
- Android/Chrome;
- desktop Chrome;
- desktop Firefox;
- desktop Safari where available;
- Edge.

---

# 57. Observability

## 57.1 Application logs

Collect:

- PHP/application errors;
- checkout errors;
- payment errors;
- carrier errors;
- background-job errors;
- webhook errors;
- authentication/security events;
- admin-sensitive actions.

## 57.2 Metrics

Monitor:

- availability;
- request latency;
- 5xx rate;
- PHP error rate;
- CPU;
- memory;
- disk;
- DB health;
- cache health;
- checkout failure rate;
- background-job failures.

## 57.3 Alerts

At minimum:

- site down;
- repeated 5xx;
- checkout failure spike;
- payment integration failure;
- low disk;
- database unavailable;
- failed backup;
- certificate expiry warning;
- background queue failure.

---

# 58. Error Tracking

Production error tracking should capture:

- error/exception;
- stack trace;
- release version;
- route;
- environment;
- non-sensitive context.

Personal and payment data must be redacted.

---

# 59. Backup and Disaster Recovery

## REQ-BACKUP-001 — Backups — P0

Back up:

- database;
- uploads/media;
- relevant configuration.

Backups must exist off the application server.

## REQ-BACKUP-002 — Encryption — P0

Backup storage must use appropriate access control/encryption.

## REQ-BACKUP-003 — Retention — P0 / TBD

Define retention schedule.

## REQ-BACKUP-004 — Restore testing — P0

A backup does not count as operationally valid until restoration has been tested.

## REQ-BACKUP-005 — RPO/RTO — P0 / TBD

Business/engineering must define:

- Recovery Point Objective;
- Recovery Time Objective.

---

# 60. Feature Flags

Feature flags/configuration should be available for features that may be deployed before activation, for example:

```text
FEATURE_ARABIC
FEATURE_ONLINE_PAYMENT
FEATURE_LOYALTY
FEATURE_ADVANCED_RECOMMENDATIONS
FEATURE_BACK_IN_STOCK
FEATURE_PACKS
```

Flags must not be used as a substitute for authorization/security controls.

---

# 61. Production Data Migration

Before launch:

1. remove demo products;
2. remove demo orders/customers;
3. import controlled taxonomy;
4. import real brands;
5. import real products;
6. validate SKU uniqueness;
7. validate EAN where used;
8. validate prices;
9. validate stock;
10. validate product images;
11. validate claims/precautions;
12. validate SEO metadata;
13. validate supplier/reference data;
14. execute catalog QA report.

No demo phone, address, WhatsApp, social account or placeholder legal content may remain.

---

# 62. Product Import Quality Gates

An imported product must fail validation or be flagged if:

- SKU duplicates;
- required name missing;
- brand unknown;
- category unknown;
- price invalid;
- sale price inconsistent;
- controlled attribute value unknown;
- image missing if images are mandatory;
- stock invalid;
- URL slug collision;
- required safety/content field missing according to category rules.

---

# 63. Production Acceptance Criteria

The platform is production-ready only when all applicable P0 criteria are satisfied.

Minimum release gate:

1. Product discovery works by search, category, brand and need.
2. Search behavior has been tested with realistic catalog data.
3. Filters return correct products.
4. Product prices match back office.
5. Promotion rules are deterministic.
6. Stock behavior is documented and tested.
7. Guest checkout works on supported mobile devices.
8. Shipping cost is visible before order confirmation.
9. COD is correctly configured.
10. Duplicate order submissions are prevented.
11. Customer receives confirmation.
12. Staff receives/processes order.
13. Order status transitions are documented and tested.
14. Stock changes/restorations follow defined rules.
15. Shipment/tracking workflow works.
16. Return/complaint workflow exists.
17. Customer account authorization is tested.
18. Legal pages and real business contact information are published.
19. Marketing/cookie consent behavior is validated.
20. No demo data remains.
21. Production secrets are outside Git.
22. Admin 2FA and least-privilege roles are active.
23. Backups are active.
24. Restore has been tested.
25. Monitoring and error tracking are active.
26. Critical alerts are configured.
27. Performance targets have been checked.
28. Accessibility checks have been run.
29. SEO/canonical/filter rules have been validated.
30. Structured data reflects real page content.
31. Email deliverability is tested.
32. Production smoke test passes after deployment.
33. Staff can add/edit products.
34. Staff can adjust stock safely.
35. Staff can process/cancel an order.
36. Staff can manage a complaint/return.
37. Audit logging is active for sensitive operations.
38. Operational contacts/escalation procedure exists.

---

# 64. Recommended Implementation Sequence

This is not an MVP sequence. It is a dependency-aware production development order.

## Stage A — Foundations

- Git/repository cleanup;
- Docker/local environment;
- environment config;
- core plugin skeleton;
- theme architecture;
- CI baseline;
- security baseline;
- database/migration framework;
- ADRs.

## Stage B — Catalog Model

- taxonomies;
- attributes;
- metadata;
- import format;
- product validation;
- sample real catalog.

## Stage C — Discovery

- navigation;
- category templates;
- filters;
- sorting;
- search;
- autocomplete;
- analytics events.

## Stage D — Product Experience

- full product page;
- stock visibility;
- recommendations;
- WhatsApp;
- SEO/schema;
- review architecture;
- back-in-stock architecture.

## Stage E — Commerce

- cart;
- coupons;
- promotion rules;
- shipping engine;
- checkout;
- COD;
- duplicate protection;
- account integration.

## Stage F — Operations

- order states;
- stock side effects;
- email;
- shipment/tracking;
- return workflow;
- complaint workflow;
- back-office permissions;
- audit logs.

## Stage G — Platform Hardening

- performance;
- caching;
- search productionization;
- accessibility;
- security headers;
- monitoring;
- alerting;
- backups;
- restore test;
- E2E tests;
- load tests.

## Stage H — Production Launch

- catalog migration;
- legal content;
- DNS/domain;
- email DNS;
- real integrations;
- staging UAT;
- pilot orders;
- production deployment;
- smoke tests;
- controlled launch.

## Stage I — Production Feature Activation

Architecture should already support progressive activation of:

- verified reviews;
- wishlist;
- back-in-stock;
- packs;
- online payments;
- Arabic;
- loyalty;
- advanced personalization.

---

# 65. Required Business Decisions / TBD Register

The following items are intentionally not invented by this specification.

They must be decided before implementation of their dependent feature.

## Business identity

- final legal/business entity name;
- final domain;
- physical shop location(s);
- real phone;
- real WhatsApp;
- support email;
- opening/support hours.

## Catalog

- launch catalog size;
- list of real categories;
- list of brands;
- real suppliers;
- person responsible for catalog data;
- person responsible for product/health-content validation;
- whether lots/expiry dates are operationally tracked.

## Inventory

- online-only stock or shared physical stock;
- stock reservation/decrement rule;
- low-stock default;
- backorder policy.

## Shipping

- carrier(s);
- exact geographic zones;
- exact fees;
- free-delivery threshold;
- threshold calculation basis;
- delivery estimates;
- remote-area behavior;
- tracking integration availability.

## Orders

- who confirms orders;
- confirmation hours;
- whether phone confirmation is mandatory;
- cancellation policy;
- refused-parcel policy.

## Payments

- COD restrictions if any;
- online payment provider;
- refund process;
- payment reconciliation owner.

## Returns

- final legal return policy;
- non-returnable categories;
- damaged-product workflow;
- deadlines;
- refund method.

## Languages

- French-only launch or French + Arabic launch;
- who provides Arabic translation;
- final translated taxonomy ownership.

## Promotions

- coupon stacking rules;
- sale-price stacking rules;
- free-shipping stacking;
- gift rules;
- bundle rules.

## Infrastructure

- production hosting provider;
- expected catalog size;
- expected monthly traffic;
- expected peak traffic/orders;
- media storage/CDN;
- transactional email provider;
- monitoring provider;
- backup retention;
- RPO;
- RTO.

## Compliance

- legal reviewer;
- privacy/retention rules;
- health-content reviewer;
- tax/invoicing requirements;
- required business identifiers on invoices/site.

---

# 66. Non-Goals / Prohibited Shortcuts

The following are explicitly prohibited unless a future ADR changes them:

- business-critical logic only inside theme `functions.php`;
- storing secrets in Git;
- raw card data storage;
- fictitious reviews;
- fictitious countdown urgency;
- fake stock values;
- unrestricted administrator access for ordinary staff;
- arbitrary free-text taxonomy values for filterable fields;
- silent import of malformed product data;
- manual production code editing through WordPress editor;
- production deployment without backup/rollback path;
- health claims invented by developers or AI;
- enabling tracking scripts before required consent;
- treating a successful backup job as sufficient without restore testing.

---

# 67. Definition of Done for a Feature

A feature is complete only if applicable items below are satisfied:

- requirement implemented;
- business rules documented;
- edge cases handled;
- authorization checked;
- validation implemented;
- analytics added when required;
- accessibility considered;
- translations prepared where applicable;
- automated tests added;
- manual QA passed;
- error behavior verified;
- logs do not expose sensitive data;
- performance impact checked;
- documentation updated;
- staging verified;
- acceptance criteria passed.

---

# 68. Documentation Required During Development

Maintain:

```text
docs/
  architecture/
  adr/
  api/
  business-rules/
  catalog/
  operations/
  runbooks/
  security/
  testing/
```

Required operational runbooks should cover:

- deployment;
- rollback;
- restore;
- failed checkout investigation;
- payment discrepancy;
- carrier outage;
- stuck background jobs;
- high error rate;
- compromised admin account;
- product recall/removal if required by business;
- emergency maintenance mode.

---

# 69. Final Engineering Principle

Jouvence Para must be developed as a production commerce system rather than a collection of pages.

The implementation must optimize for:

1. correctness of product data;
2. correctness of price;
3. correctness of inventory;
4. reliable order creation;
5. deterministic operational workflows;
6. security and privacy;
7. fast mobile experience;
8. maintainability;
9. observability;
10. ability to add future commercial features without rebuilding the core.

The system should prefer boring, explicit and testable business rules over hidden plugin behavior or undocumented assumptions.

---

# 70. Requirement-ID Convention

New requirements should follow:

```text
REQ-<DOMAIN>-<NUMBER>
```

Examples:

```text
REQ-SEARCH-008
REQ-PRODUCT-010
REQ-CART-007
REQ-CHECKOUT-011
REQ-STOCK-008
REQ-ORDER-005
REQ-SHIP-005
REQ-PAY-006
REQ-SEC-009
REQ-PERF-005
```

Every new feature ticket should reference one or more requirement IDs.

---

# 71. Change Management

Any material change to:

- order lifecycle;
- stock logic;
- payment behavior;
- shipping calculation;
- promotion stacking;
- customer-data handling;
- authentication;
- external integration;
- catalog taxonomy;

must update this specification and, where architectural, create or revise an ADR.

Document version must be incremented for significant requirement changes.

---

# 72. Current Project Baseline

Based on the existing project brief, the repository already includes or has included:

- HTML/CSS/JavaScript prototype work;
- custom WordPress theme;
- homepage connected to WooCommerce;
- product cards;
- add-to-cart links;
- Docker environment;
- demo catalog/image scripts.

Before relying on any of these as production-ready, they must be reviewed against this specification.

Known previous gaps included:

- Git/repository setup incomplete;
- demo data;
- incomplete search/filter/product/checkout flows;
- visual-only newsletter;
- placeholder contact/WhatsApp/social data;
- shipping/payment/tax/email/return/SAV not fully configured;
- security/backups/analytics/compliance incomplete;
- Docker/database runtime state not previously verified.

These points must be revalidated against the current repository before development planning.

---

# 73. Immediate Engineering Checklist

Before major feature implementation:

- [ ] Initialize/validate Git repository.
- [ ] Audit current theme.
- [ ] Audit current plugins.
- [ ] Create `jouvence-para-core`.
- [ ] Define development/staging/production environment strategy.
- [ ] Decide product taxonomy.
- [ ] Create canonical product import schema.
- [ ] Decide search engine.
- [ ] Decide stock lifecycle.
- [ ] Define order state machine.
- [ ] Define shipping matrix.
- [ ] Define COD rules.
- [ ] Decide multilingual implementation.
- [ ] Establish CI.
- [ ] Establish coding standards.
- [ ] Establish test framework.
- [ ] Establish secrets strategy.
- [ ] Establish backup strategy.
- [ ] Establish observability strategy.
- [ ] Replace demo identity with Jouvence Para.
- [ ] Load representative real products before tuning search/filter UX.

---

# 74. Production Launch Gate

Production launch requires an explicit GO/NO-GO review.

**GO** only if:

- all applicable P0 requirements pass;
- all money/stock/shipping/payment TBDs are resolved;
- legal/business contact information is real;
- backup restore is tested;
- checkout is tested on target devices;
- pilot orders have completed operationally;
- monitoring is active;
- staff understands order/stock/return workflows.

Any unresolved P0 issue affecting money, stock, order integrity, security, privacy or customer rights is a **NO-GO**.
