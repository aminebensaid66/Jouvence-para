# Jouvence Para — Sprint 4 Plan

## Sprint goal

> Build the core commerce foundation: cart, promotions, payment abstraction, account basics, order state model, analytics/SEO foundation, webhooks and background jobs.

## Entry criteria

- Product discovery and product-detail primitives are stable.
- Promotion stacking decision is resolved.
- Order/inventory policy decisions are documented enough to define the state machine.
- Cookie/analytics consent behavior is available.

## Assigned tickets

| Ticket ID      | Title                                                         | Type   | Priority   | Workstream                     | Dependencies                          | Blocked By / Decisions   |
|:---------------|:--------------------------------------------------------------|:-------|:-----------|:-------------------------------|:--------------------------------------|:-------------------------|
| JP-PDP-003     | Implement related/complementary products and product schema   | Story  | P0         | Backend / Frontend / SEO       | JP-PDP-001                            |                          |
| JP-CART-001    | Implement cart operations and persistent cart                 | Story  | P0         | Frontend / Backend             | JP-PDP-001                            |                          |
| JP-CART-002    | Revalidate cart price and stock at critical points            | Story  | P0         | Backend / Frontend             | JP-CART-001, JP-STOCK-001             |                          |
| JP-CART-003    | Implement coupons, free-shipping progress and limited upsells | Story  | P0         | Frontend / Backend             | JP-DEC-004, JP-PROMO-001, JP-CART-001 |                          |
| JP-PROMO-001   | Implement promotion types, scheduling and eligibility         | Story  | P0         | Backend / Commerce             | JP-CAT-001                            |                          |
| JP-PROMO-002   | Implement stacking policy and valid reference-price display   | Story  | P0         | Backend / Frontend             | JP-DEC-008, JP-PROMO-001              |                          |
| JP-GEO-001     | Implement Tunisia geography data model                        | Story  | P0         | Backend / Frontend             | JP-DEC-004                            |                          |
| JP-GEO-002     | Implement shipping matrix configuration                       | Story  | P0         | Backend                        | JP-DEC-004, JP-GEO-001                |                          |
| JP-PAY-002     | Implement payment abstraction and safe payment data model     | Story  | P0         | Backend                        | JP-PLAT-002                           |                          |
| JP-ORDER-001   | Implement order state machine and controlled transitions      | Story  | P0         | Backend / Operations           | JP-DEC-003, JP-DEC-006, JP-PLAT-002   |                          |
| JP-ACC-001     | Implement authentication and account profile                  | Story  | P0         | Frontend / Backend             | JP-SEC-002                            |                          |
| JP-WEBHOOK-001 | Build reusable signed/idempotent webhook processing framework | Story  | P0         | Backend / Integration          | JP-PLAT-002                           |                          |
| JP-JOBS-001    | Implement background-job conventions and failure visibility   | Story  | P0         | Backend / DevOps               | JP-PLAT-002, JP-OBS-001               |                          |
| JP-SEO-001     | Implement SEO URL, metadata and structured-data foundation    | Story  | P0         | Frontend / Backend / SEO       | JP-CAT-002, JP-PDP-001                |                          |
| JP-AN-001      | Implement analytics event contract                            | Story  | P0         | Frontend / Backend / Analytics | JP-COOKIE-001                         |                          |
| JP-EMAIL-002   | Build branded transactional email templates                   | Story  | P0         | Frontend / Backend             | JP-EMAIL-001, JP-DEC-001              |                          |

## Continuous engineering control carried through this sprint

| Ticket ID   | Title                               | Type   | Priority   | Workstream                    | Dependencies   | Blocked By / Decisions   |
|:------------|:------------------------------------|:-------|:-----------|:------------------------------|:---------------|:-------------------------|
| JP-SEC-003  | Apply custom-code security controls | Story  | P0         | Backend / Frontend / Security | JP-PLAT-002    |                          |

## Suggested parallel lanes

### Commerce Backend
- **JP-CART-001** — Implement cart operations and persistent cart
- **JP-CART-002** — Revalidate cart price and stock at critical points
- **JP-CART-003** — Implement coupons, free-shipping progress and limited upsells
- **JP-PROMO-001** — Implement promotion types, scheduling and eligibility
- **JP-PROMO-002** — Implement stacking policy and valid reference-price display
- **JP-PAY-002** — Implement payment abstraction and safe payment data model
- **JP-ORDER-001** — Implement order state machine and controlled transitions

### Accounts / Frontend
- **JP-CART-001** — Implement cart operations and persistent cart
- **JP-CART-003** — Implement coupons, free-shipping progress and limited upsells
- **JP-ACC-001** — Implement authentication and account profile

### Platform / Integrations
- **JP-WEBHOOK-001** — Build reusable signed/idempotent webhook processing framework
- **JP-JOBS-001** — Implement background-job conventions and failure visibility

### SEO / Analytics / Email
- **JP-PDP-003** — Implement related/complementary products and product schema
- **JP-SEO-001** — Implement SEO URL, metadata and structured-data foundation
- **JP-AN-001** — Implement analytics event contract
- **JP-EMAIL-002** — Build branded transactional email templates

## Exit criteria

- Cart and stale-price/stock validation work.
- Promotions/coupons use deterministic eligibility and stacking rules.
- Order state machine exists.
- Payment state is modeled independently from order state.
- Customer authentication/profile works.
- SEO and analytics foundations are active.
- Webhook and async-job frameworks are ready for integrations.
- Branded transactional email templates exist.
- Custom-code security controls remain enforced for all changes in the sprint.

## Dependency / blocker handling

A ticket must not be implemented by inventing an unresolved business rule. If its `Blocked By / Decisions` field is unresolved, the team may implement only the non-dependent foundation and must keep the behavior disabled or incomplete until the decision is approved.

## Ticket details

### JP-PDP-003 — Implement related/complementary products and product schema
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Frontend / SEO
**Requirements:** REQ-PRODUCT-005, REQ-PRODUCT-007
**Dependencies:** JP-PDP-001
**Blocked by / decisions:** None

Separate substitution/related recommendations from complementary routine products and emit valid structured data.

**Acceptance criteria**

- Related and complementary recommendations are distinct.
- Manual curation is honored where present.
- Product/Offer structured data matches visible current data.
- AggregateRating is absent unless valid reviews exist.

### JP-CART-001 — Implement cart operations and persistent cart
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend
**Requirements:** REQ-CART-001, REQ-CART-002
**Dependencies:** JP-PDP-001
**Blocked by / decisions:** None

Build reliable cart state for anonymous and authenticated customers.

**Acceptance criteria**

- Add/update/remove operations are reliable.
- Line totals/subtotal/discount/shipping estimate/total render correctly when calculable.
- Authenticated carts persist across sessions.
- Anonymous persistence and merge behavior follow documented decision.
- Cart remains usable after login/logout transitions.

### JP-CART-002 — Revalidate cart price and stock at critical points
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Frontend
**Requirements:** REQ-CART-003
**Dependencies:** JP-CART-001, JP-STOCK-001
**Blocked by / decisions:** None

Prevent stale cart state from producing invalid orders.

**Acceptance criteria**

- Price/stock revalidate when cart loads.
- Price/stock revalidate before checkout.
- Final order creation revalidates again.
- Customer receives clear resolution when quantity/price changed.

### JP-CART-003 — Implement coupons, free-shipping progress and limited upsells
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend
**Requirements:** REQ-CART-004, REQ-CART-005, REQ-CART-006
**Dependencies:** JP-DEC-004, JP-PROMO-001, JP-CART-001
**Blocked by / decisions:** None

Expose promotion effects transparently without cluttering the cart.

**Acceptance criteria**

- Coupon success/rejection is visible with safe reason.
- Free-shipping progress appears only when rule exists.
- Threshold calculation uses approved basis.
- Upsells are limited and relevant.
- Totals remain correct after coupon/quantity changes.

### JP-PROMO-001 — Implement promotion types, scheduling and eligibility
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Commerce
**Requirements:** REQ-PROMO-001, REQ-PROMO-002, REQ-PROMO-003
**Dependencies:** JP-CAT-001
**Blocked by / decisions:** None

Provide deterministic promotion rules using WooCommerce/native/custom mechanisms as documented.

**Acceptance criteria**

- Required launch promotion types are supported.
- Start/end times are timezone-aware.
- Product/category/min-cart/usage-limit/exclusion rules work.
- Expired promotions stop applying without manual cleanup.
- Eligibility is server-side authoritative.

### JP-PROMO-002 — Implement stacking policy and valid reference-price display
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Frontend
**Requirements:** REQ-PROMO-004, REQ-PROMO-005
**Dependencies:** JP-DEC-008, JP-PROMO-001
**Blocked by / decisions:** None

Prevent accidental stacking and misleading sale displays.

**Acceptance criteria**

- Configured stacking matrix is enforced.
- UI totals explain resulting discounts.
- Strikethrough/reference price appears only when valid under approved rules.
- Tests cover every approved/disallowed combination.

### JP-GEO-001 — Implement Tunisia geography data model
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Frontend
**Requirements:** REQ-GEO-001, REQ-GEO-002
**Dependencies:** JP-DEC-004
**Blocked by / decisions:** None

Represent governorate/delegation/locality using stable IDs suitable for shipping rules.

**Acceptance criteria**

- Governorates have stable internal IDs.
- Required delegation/locality depth matches carrier/business rules.
- Labels are user-friendly while rules reference stable IDs.
- Data can support future Arabic labels.

### JP-GEO-002 — Implement shipping matrix configuration
**Type:** Story
**Priority:** P0
**Workstream:** Backend
**Requirements:** REQ-GEO-003
**Dependencies:** JP-DEC-004, JP-GEO-001
**Blocked by / decisions:** None

Configure approved regional rates, thresholds, estimates and exceptions.

**Acceptance criteria**

- Every supported region resolves to deterministic shipping rules.
- Unsupported areas produce a clear checkout outcome.
- Free-shipping and delivery estimates use approved rules.
- Rate changes are configurable without theme code.

### JP-PAY-002 — Implement payment abstraction and safe payment data model
**Type:** Story
**Priority:** P0
**Workstream:** Backend
**Requirements:** REQ-PAY-002, REQ-PAY-004
**Dependencies:** JP-PLAT-002
**Blocked by / decisions:** None

Keep payment state independent from order workflow and prevent raw card-data storage.

**Acceptance criteria**

- Payment record/model supports method/status/provider/transaction/paid/refund state.
- Order status can differ from payment status.
- No raw card data is persisted by Jouvence Para.
- Sensitive provider data is redacted from logs.

### JP-ORDER-001 — Implement order state machine and controlled transitions
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Operations
**Requirements:** REQ-ORDER-001
**Dependencies:** JP-DEC-003, JP-DEC-006, JP-PLAT-002
**Blocked by / decisions:** None

Map documented business statuses to WooCommerce/custom statuses and constrain transitions.

**Acceptance criteria**

- Required statuses are mapped/documented.
- Allowed transition matrix exists.
- Invalid transitions are blocked for ordinary staff.
- Any administrator override is explicit and audited.

### JP-ACC-001 — Implement authentication and account profile
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend
**Requirements:** REQ-ACCOUNT-001, REQ-ACCOUNT-002
**Dependencies:** JP-SEC-002
**Blocked by / decisions:** None

Provide registration/login/logout/reset and customer-managed profile/address data.

**Acceptance criteria**

- Registration/login/logout/reset work.
- Saved addresses can be managed.
- Password update works securely.
- Communication preferences are persisted where applicable.

### JP-WEBHOOK-001 — Build reusable signed/idempotent webhook processing framework
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Integration
**Requirements:** REQ-WEBHOOK-001, REQ-WEBHOOK-002, REQ-WEBHOOK-003
**Dependencies:** JP-PLAT-002
**Blocked by / decisions:** None

Provide shared webhook controls for payment/carrier integrations.

**Acceptance criteria**

- Signature verification extension point exists.
- Provider event IDs/idempotency prevent duplicate side effects.
- Processing result/failure metadata is persisted safely.
- Retryable failures are distinguishable from permanent failures.

### JP-JOBS-001 — Implement background-job conventions and failure visibility
**Type:** Story
**Priority:** P0
**Workstream:** Backend / DevOps
**Requirements:** Architecture / operational requirement
**Dependencies:** JP-PLAT-002, JP-OBS-001
**Blocked by / decisions:** None

Standardize async work for indexing, email retries, sync and alerts.

**Acceptance criteria**

- Jobs have retry policy.
- Duplicate-sensitive jobs have idempotency protection.
- Failures are logged/observable.
- Administrators have a method to inspect or recover failed critical jobs.

### JP-SEO-001 — Implement SEO URL, metadata and structured-data foundation
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend / SEO
**Requirements:** REQ-SEO-001, REQ-SEO-002, REQ-SEO-003
**Dependencies:** JP-CAT-002, JP-PDP-001
**Blocked by / decisions:** None

Implement stable URLs, page metadata and valid schema from current visible data.

**Acceptance criteria**

- Primary page types have stable canonical URLs.
- Indexable pages support unique title/meta/canonical.
- Organization/Breadcrumb/Product/Offer/Article schema is valid where applicable.
- Schema does not claim invisible/nonexistent rating/price data.

### JP-AN-001 — Implement analytics event contract
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend / Analytics
**Requirements:** Architecture / operational requirement
**Dependencies:** JP-COOKIE-001
**Blocked by / decisions:** None

Implement the stable event list and product/checkout payload conventions from the spec.

**Acceptance criteria**

- Required search/catalog/product/cart/checkout/purchase events are emitted.
- Event names and fields are documented.
- No unnecessary PII is sent.
- Events respect consent where legally required.
- Purchase is not double-counted on refresh.

**Tests**

- Event payload validation.
- Consent-denied behavior.
- Purchase refresh deduplication.

### JP-EMAIL-002 — Build branded transactional email templates
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend
**Requirements:** REQ-EMAIL-003
**Dependencies:** JP-EMAIL-001, JP-DEC-001
**Blocked by / decisions:** None

Create responsive transactional templates used by order/account workflows.

**Acceptance criteria**

- Templates are branded with real Jouvence Para identity.
- Common email clients are spot-tested.
- Plain-text/fallback behavior is acceptable.
- Sensitive/internal notes never appear.
