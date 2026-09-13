# Jouvence Para Architecture

## 1. Goals

The architecture optimizes for correct commerce behavior, understandable module ownership, small reviewable changes, and the ability to replace external providers without rewriting the store.

## 2. Target repository layout

```text
jouvence-para/
├── AGENTS.md
├── README.md
├── CHANGELOG.md
├── composer.json
├── package.json
├── docker-compose.yml
├── .env.example
├── .github/
│   └── workflows/
├── docker/
├── docs/
│   ├── architecture/
│   ├── adr/
│   ├── business-rules/
│   ├── catalog/
│   ├── operations/
│   ├── runbooks/
│   ├── security/
│   ├── testing/
│   └── workflow/
├── patches/
│   ├── incoming/
│   └── rejected/
├── scripts/
├── tests/
│   ├── unit/
│   ├── integration/
│   └── e2e/
└── wordpress/
    └── wp-content/
        ├── themes/
        │   └── jouvence-para/
        └── plugins/
            └── jouvence-para-core/
```

This is the target layout, not authorization to move the existing theme before `JP-DEC-001` and `JP-PLAT-001` are complete.

## 3. Runtime boundaries

```text
Browser
  -> CDN/WAF/TLS (when selected)
    -> WordPress + WooCommerce
       -> Jouvence Para Theme (presentation)
       -> Jouvence Para Core (business behavior)
          -> Catalog
          -> Search
          -> Inventory
          -> Cart/Promotions
          -> Checkout
          -> Orders
          -> Shipping
          -> Payments
          -> Customer/Privacy
          -> Notifications
          -> Analytics/Audit
          -> Integration adapters
       -> MySQL/MariaDB
       -> Cache/search/providers when selected
```

## 4. Core-plugin module layout

```text
jouvence-para-core/
├── jouvence-para-core.php
├── src/
│   ├── Bootstrap/
│   ├── Catalog/
│   ├── Search/
│   ├── Inventory/
│   ├── Promotions/
│   ├── Checkout/
│   ├── Orders/
│   ├── Shipping/
│   ├── Payments/
│   ├── Customers/
│   ├── Notifications/
│   ├── Privacy/
│   ├── Analytics/
│   ├── Audit/
│   ├── Admin/
│   ├── Infrastructure/
│   └── Support/
├── migrations/
├── templates/
├── assets/
├── languages/
└── tests/
```

Rules:

- A module exposes a small public API and keeps provider details internal.
- `Infrastructure` contains reusable technical adapters, not commerce decisions.
- `Support` contains small shared primitives only; it must not become a miscellaneous dumping ground.
- Circular dependencies between modules are prohibited.
- WordPress hook registration is centralized in module bootstrap/provider classes.

## 5. Theme layout

```text
jouvence-para/
├── style.css
├── functions.php
├── theme.json
├── src/
│   ├── Setup/
│   ├── Assets/
│   └── View/
├── templates/
├── template-parts/
│   ├── global/
│   ├── catalog/
│   ├── product/
│   ├── cart/
│   └── account/
├── woocommerce/
├── assets/
│   ├── css/
│   ├── js/
│   ├── images/
│   └── fonts/
├── languages/
└── tests/
```

The theme may render data and enhance interactions. It must not decide stock restoration, shipping price, promotion eligibility, payment state, or permitted order transitions.

## 6. Sources of truth

| Domain | Initial source of truth |
|---|---|
| Product, variation, price | WooCommerce |
| Base inventory | WooCommerce until an approved POS integration replaces it |
| Cart and coupon | WooCommerce |
| Customer and address | WooCommerce/WordPress |
| Order and order items | WooCommerce HPOS-compatible APIs |
| Custom controlled vocabulary | Registered taxonomies/attributes defined by core plugin |
| Shipping configuration | Core-plugin configuration over WooCommerce shipping APIs |
| Provider event history | Purpose-built core-plugin tables where required |
| Audit history | Append-oriented audit storage defined by core plugin |

Custom code must remain compatible with WooCommerce High-Performance Order Storage. It must not assume orders are WordPress posts.

## 7. Integration pattern

Every provider integration follows:

```text
Domain service -> provider interface -> provider adapter -> remote API
                                      -> fake adapter for tests
```

Each integration must define timeouts, retries, idempotency, log redaction, webhook verification, and manual fallback.

## 8. Change isolation

One ticket should normally own one module. If a ticket crosses theme and plugin:

- define the plugin contract first;
- keep rendering in the theme;
- include both changes in one atomic patch only when neither side is useful alone;
- include an integration test or documented contract fixture.

Changes to stock, checkout, orders, shipping, payments, permissions, or privacy require a second reviewer.

## 9. Decisions before implementation

The following remain blocking decisions and must be captured in ADRs/business-rule documents:

- final identity and repository rename;
- inventory source and reservation lifecycle;
- shipping carrier/matrix;
- COD rules;
- return/refusal rules;
- language implementation;
- search technology;
- hosting/cache/media architecture;
- online payment provider.
