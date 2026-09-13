# Jouvence Para — Sprint 6 Plan

## Sprint goal

> Finish operational workflows and production hardening: tracking, returns/SAV, account order tools, privacy, accessibility, caching, performance diagnostics and critical E2E coverage.

## Entry criteria

- End-to-end checkout/order creation is functioning.
- Shipment/admin order data exists.
- Return policy is approved.
- Monitoring and staging deployment are operational.

## Assigned tickets

| Ticket ID    | Title                                                            | Type   | Priority   | Workstream                       | Dependencies                          | Blocked By / Decisions   |
|:-------------|:-----------------------------------------------------------------|:-------|:-----------|:---------------------------------|:--------------------------------------|:-------------------------|
| JP-SHIP-002  | Expose customer tracking and carrier integration fallback        | Story  | P0         | Backend / Frontend / Integration | JP-SHIP-001                           |                          |
| JP-RET-001   | Implement structured return request workflow                     | Story  | P0         | Backend / Frontend / Operations  | JP-DEC-006, JP-ORDER-001              |                          |
| JP-SAV-001   | Implement complaint/SAV form with secure attachments             | Story  | P0         | Backend / Frontend / Operations  | JP-ORDER-003                          |                          |
| JP-ACC-002   | Implement customer order history, tracking and reorder           | Story  | P0         | Frontend / Backend               | JP-ACC-001, JP-ORDER-003, JP-SHIP-001 |                          |
| JP-ACC-003   | Enforce object-level authorization and data rights workflows     | Story  | P0         | Backend / Security               | JP-ACC-001                            |                          |
| JP-MKT-001   | Implement newsletter consent and unsubscribe workflow            | Story  | P0         | Frontend / Backend               | JP-COOKIE-001                         |                          |
| JP-A11Y-001  | Implement accessibility baseline and automated checks            | Story  | P0         | Frontend / QA                    | JP-DISC-001, JP-PDP-001               |                          |
| JP-PRIV-001  | Implement privacy/data-retention operational hooks               | Story  | P0         | Backend / Product / Legal        | JP-ACC-003, JP-COOKIE-001             |                          |
| JP-DB-002    | Implement DB indexing review and slow-query diagnostics          | Story  | P0         | Backend / DevOps                 | JP-DB-001                             |                          |
| JP-CACHE-001 | Implement commerce-safe caching architecture                     | Story  | P0         | DevOps / Backend                 | JP-DEC-009                            |                          |
| JP-PERF-001  | Implement frontend performance budget and Core Web Vitals checks | Story  | P0         | Frontend / DevOps / QA           | JP-CAT-004, JP-CACHE-001              |                          |
| JP-QA-001    | Automate critical E2E commerce journeys                          | Story  | P0         | QA / Frontend / Backend          | JP-CHECKOUT-003, JP-ORDER-004         |                          |

## Continuous engineering control carried through this sprint

| Ticket ID   | Title                               | Type   | Priority   | Workstream                    | Dependencies   | Blocked By / Decisions   |
|:------------|:------------------------------------|:-------|:-----------|:------------------------------|:---------------|:-------------------------|
| JP-SEC-003  | Apply custom-code security controls | Story  | P0         | Backend / Frontend / Security | JP-PLAT-002    |                          |

## Suggested parallel lanes

### Operations / Backend
- **JP-SHIP-002** — Expose customer tracking and carrier integration fallback
- **JP-RET-001** — Implement structured return request workflow
- **JP-SAV-001** — Implement complaint/SAV form with secure attachments
- **JP-ACC-002** — Implement customer order history, tracking and reorder
- **JP-ACC-003** — Enforce object-level authorization and data rights workflows

### Frontend / QA
- **JP-MKT-001** — Implement newsletter consent and unsubscribe workflow
- **JP-A11Y-001** — Implement accessibility baseline and automated checks
- **JP-QA-001** — Automate critical E2E commerce journeys

### Platform Reliability
- **JP-DB-002** — Implement DB indexing review and slow-query diagnostics
- **JP-CACHE-001** — Implement commerce-safe caching architecture
- **JP-PERF-001** — Implement frontend performance budget and Core Web Vitals checks

### Privacy
- **JP-PRIV-001** — Implement privacy/data-retention operational hooks

## Exit criteria

- Tracking is visible to customers and carrier failure has a fallback.
- Returns and complaints are structured workflows.
- Customers can view/reorder their own orders with authorization enforced.
- Privacy/data-rights hooks are implemented.
- Newsletter consent is operational.
- Accessibility checks cover critical journeys.
- Commerce-safe caching and DB diagnostics are active.
- Critical E2E flows are automated.
- Custom-code security controls remain enforced for all changes in the sprint.

## Dependency / blocker handling

A ticket must not be implemented by inventing an unresolved business rule. If its `Blocked By / Decisions` field is unresolved, the team may implement only the non-dependent foundation and must keep the behavior disabled or incomplete until the decision is approved.

## Ticket details

### JP-SHIP-002 — Expose customer tracking and carrier integration fallback
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Frontend / Integration
**Requirements:** REQ-SHIP-003, REQ-SHIP-004
**Dependencies:** JP-SHIP-001
**Blocked by / decisions:** None

Expose safe tracking and keep orders operable during carrier API failures.

**Acceptance criteria**

- Customer can see appropriate tracking data when available.
- Carrier API failure does not lose order/shipment data.
- Failures are logged and visible operationally.
- Manual recovery workflow exists.

### JP-RET-001 — Implement structured return request workflow
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Frontend / Operations
**Requirements:** REQ-RETURN-001, REQ-RETURN-002, REQ-RETURN-003
**Dependencies:** JP-DEC-006, JP-ORDER-001
**Blocked by / decisions:** None

Provide a status-driven return workflow tied to original order items.

**Acceptance criteria**

- Return request captures order/item/quantity/reason/timestamp/evidence where applicable.
- Statuses follow documented return lifecycle.
- Eligibility uses approved legal/business rules.
- Refund/stock operations are not performed before required approval/physical events.

### JP-SAV-001 — Implement complaint/SAV form with secure attachments
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Frontend / Operations
**Requirements:** REQ-SAV-001, REQ-SAV-002
**Dependencies:** JP-ORDER-003
**Blocked by / decisions:** None

Provide complaint intake tied to an order with secure evidence upload.

**Acceptance criteria**

- Order/contact/category/description fields are collected.
- Allowed image/file types and size limits are enforced server-side.
- Filenames/storage prevent executable upload abuse.
- Complaint is visible to authorized support staff.

### JP-ACC-002 — Implement customer order history, tracking and reorder
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend
**Requirements:** REQ-ACCOUNT-003
**Dependencies:** JP-ACC-001, JP-ORDER-003, JP-SHIP-001
**Blocked by / decisions:** None

Allow customers to inspect and repeat their own eligible purchases.

**Acceptance criteria**

- Customer sees only their own orders.
- Order detail includes status and safe tracking.
- Reorder handles discontinued/out-of-stock/price-changed products safely.

### JP-ACC-003 — Enforce object-level authorization and data rights workflows
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Security
**Requirements:** REQ-ACCOUNT-004, REQ-ACCOUNT-005
**Dependencies:** JP-ACC-001
**Blocked by / decisions:** None

Prevent cross-customer access and support applicable access/export/deletion workflows.

**Acceptance criteria**

- Direct URL/API manipulation cannot access another customer's data.
- Authorization checks exist server-side.
- Applicable access/export/correction/delete/anonymize workflows are documented and testable.

### JP-MKT-001 — Implement newsletter consent and unsubscribe workflow
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend
**Requirements:** REQ-MKT-001, REQ-MKT-002, REQ-MKT-003
**Dependencies:** JP-COOKIE-001
**Blocked by / decisions:** None

Replace visual-only newsletter UI with explicit consent and proper marketing lifecycle.

**Acceptance criteria**

- Subscription requires explicit action.
- No preselected marketing checkbox.
- Transactional messages are technically separate.
- Unsubscribe/preference change works.
- Consent timestamp/source is recordable where required.

### JP-A11Y-001 — Implement accessibility baseline and automated checks
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / QA
**Requirements:** Architecture / operational requirement
**Dependencies:** JP-DISC-001, JP-PDP-001
**Blocked by / decisions:** None

Meet WCAG 2.2 AA target across critical journeys.

**Acceptance criteria**

- Keyboard operation and visible focus work.
- Form labels/errors are programmatically associated.
- Dynamic cart/checkout updates announce important changes.
- Contrast/touch targets/zoom/reduced-motion requirements pass.
- Critical flows are manually screen-reader spot-checked.

### JP-PRIV-001 — Implement privacy/data-retention operational hooks
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Product / Legal
**Requirements:** Architecture / operational requirement
**Dependencies:** JP-ACC-003, JP-COOKIE-001
**Blocked by / decisions:** None

Implement technical support for approved privacy, retention, access and deletion rules.

**Acceptance criteria**

- Data collection is limited to required fields.
- Retention rules are configurable/documented.
- Customer rights workflows use approved legal policy.
- Marketing and transactional consent records are distinguishable.

### JP-DB-002 — Implement DB indexing review and slow-query diagnostics
**Type:** Story
**Priority:** P0
**Workstream:** Backend / DevOps
**Requirements:** REQ-DB-004, REQ-DB-005
**Dependencies:** JP-DB-001
**Blocked by / decisions:** None

Make high-volume product/search/order queries diagnosable and indexed based on real usage.

**Acceptance criteria**

- Custom query patterns are reviewed for indexes.
- Slow-query investigation is enabled in production-safe form.
- No index is added without documented query need.

### JP-CACHE-001 — Implement commerce-safe caching architecture
**Type:** Story
**Priority:** P0
**Workstream:** DevOps / Backend
**Requirements:** REQ-CACHE-001, REQ-CACHE-002, REQ-CACHE-003
**Dependencies:** JP-DEC-009
**Blocked by / decisions:** None

Configure browser/CDN/page/object/media caching without caching personalized commerce state.

**Acceptance criteria**

- Cart/checkout/account/order confirmation are excluded from full-page cache.
- Product/category pages use appropriate cache layers.
- Price/stock/product/promotion updates invalidate relevant cache.
- Logged-in/customer-specific state is not leaked across sessions.

### JP-PERF-001 — Implement frontend performance budget and Core Web Vitals checks
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / DevOps / QA
**Requirements:** Architecture / operational requirement
**Dependencies:** JP-CAT-004, JP-CACHE-001
**Blocked by / decisions:** None

Optimize image/script/font loading to target the production CWV thresholds.

**Acceptance criteria**

- LCP/INP/CLS are measured on critical templates.
- Below-fold media is lazy-loaded appropriately.
- Critical above-fold media is not accidentally deprioritized.
- Third-party scripts are reviewed against performance budget.

### JP-QA-001 — Automate critical E2E commerce journeys
**Type:** Story
**Priority:** P0
**Workstream:** QA / Frontend / Backend
**Requirements:** Architecture / operational requirement
**Dependencies:** JP-CHECKOUT-003, JP-ORDER-004
**Blocked by / decisions:** None

Automate the critical journeys listed in the specification.

**Acceptance criteria**

- Search->product->cart->guest checkout->order is covered.
- Coupon success/failure is covered.
- Out-of-stock/stale-cart behavior is covered.
- Checkout validation/double-submit is covered.
- Account login/reset and reorder are covered.
- Tracking/return paths are covered when implemented.
