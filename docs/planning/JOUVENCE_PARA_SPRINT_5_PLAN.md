# Jouvence Para — Sprint 5 Plan

## Sprint goal

> Complete the production ordering path: checkout, Tunisia shipping, COD, order side effects, stock lifecycle, shipment administration, order confirmation, security and alerting.

## Entry criteria

- Cart, promotions and order state model are stable.
- Shipping matrix and COD rules are approved.
- Inventory reservation/decrement/restoration policy is approved.
- Payment abstraction exists.

## Assigned tickets

| Ticket ID       | Title                                                       | Type   | Priority   | Workstream               | Dependencies                  | Blocked By / Decisions   |
|:----------------|:------------------------------------------------------------|:-------|:-----------|:-------------------------|:------------------------------|:-------------------------|
| JP-STOCK-002    | Implement reservation, decrement and restoration lifecycle  | Story  | P0         | Backend                  | JP-DEC-003, JP-ORDER-001      |                          |
| JP-STOCK-003    | Implement inventory audit trail                             | Story  | P0         | Backend                  | JP-AUDIT-001, JP-STOCK-001    |                          |
| JP-CHECKOUT-001 | Build guest checkout fields and server-side validation      | Story  | P0         | Frontend / Backend       | JP-GEO-001, JP-CART-002       |                          |
| JP-CHECKOUT-002 | Implement shipping cost, terms and final order summary      | Story  | P0         | Frontend / Backend       | JP-GEO-002, JP-CHECKOUT-001   |                          |
| JP-CHECKOUT-003 | Implement idempotent order submission and checkout recovery | Story  | P0         | Backend / Frontend       | JP-CHECKOUT-002, JP-ORDER-001 |                          |
| JP-SHIP-001     | Implement shipment domain model and admin data              | Story  | P0         | Backend / Operations     | JP-DEC-004, JP-ORDER-001      |                          |
| JP-PAY-001      | Implement COD payment method and rules                      | Story  | P0         | Backend / Frontend       | JP-DEC-005, JP-CHECKOUT-002   |                          |
| JP-ORDER-002    | Implement order transition side effects                     | Story  | P0         | Backend                  | JP-ORDER-001, JP-DEC-003      |                          |
| JP-ORDER-003    | Implement order notes, history and operational admin view   | Story  | P0         | Backend / Frontend Admin | JP-ORDER-001                  |                          |
| JP-ORDER-004    | Build order confirmation page and transactional email       | Story  | P0         | Frontend / Backend       | JP-CHECKOUT-003, JP-EMAIL-001 |                          |
| JP-SEO-002      | Implement faceted crawl policy and removed-product behavior | Story  | P0         | Backend / SEO            | JP-DISC-003                   |                          |
| JP-SEC-001      | Configure TLS, production headers and secure cookies        | Story  | P0         | DevOps / Security        | JP-DEC-009                    |                          |
| JP-OBS-002      | Configure operational alerts                                | Story  | P0         | DevOps                   | JP-OBS-001                    |                          |

## Continuous engineering control carried through this sprint

| Ticket ID   | Title                               | Type   | Priority   | Workstream                    | Dependencies   | Blocked By / Decisions   |
|:------------|:------------------------------------|:-------|:-----------|:------------------------------|:---------------|:-------------------------|
| JP-SEC-003  | Apply custom-code security controls | Story  | P0         | Backend / Frontend / Security | JP-PLAT-002    |                          |

## Suggested parallel lanes

### Checkout / Frontend
- **JP-CHECKOUT-001** — Build guest checkout fields and server-side validation
- **JP-CHECKOUT-002** — Implement shipping cost, terms and final order summary
- **JP-CHECKOUT-003** — Implement idempotent order submission and checkout recovery
- **JP-PAY-001** — Implement COD payment method and rules
- **JP-ORDER-004** — Build order confirmation page and transactional email

### Backend / Orders
- **JP-STOCK-002** — Implement reservation, decrement and restoration lifecycle
- **JP-STOCK-003** — Implement inventory audit trail
- **JP-SHIP-001** — Implement shipment domain model and admin data
- **JP-ORDER-002** — Implement order transition side effects
- **JP-ORDER-003** — Implement order notes, history and operational admin view

### SEO / Security
- **JP-SEO-002** — Implement faceted crawl policy and removed-product behavior
- **JP-SEC-001** — Configure TLS, production headers and secure cookies

### DevOps
- **JP-OBS-002** — Configure operational alerts

## Exit criteria

- Guest checkout works with Tunisia-specific data and validation.
- Shipping cost and terms are visible before confirmation.
- Duplicate-order protection and error recovery work.
- COD eligibility is enforced.
- Order transitions trigger documented side effects.
- Stock lifecycle follows approved policy.
- Shipment data is structured and manageable.
- Order confirmation page/email work.
- Security headers/cookies and operational alerts are active.
- Custom-code security controls remain enforced for all changes in the sprint.

## Dependency / blocker handling

A ticket must not be implemented by inventing an unresolved business rule. If its `Blocked By / Decisions` field is unresolved, the team may implement only the non-dependent foundation and must keep the behavior disabled or incomplete until the decision is approved.

## Ticket details

### JP-STOCK-002 — Implement reservation, decrement and restoration lifecycle
**Type:** Story
**Priority:** P0
**Workstream:** Backend
**Requirements:** REQ-STOCK-004, REQ-STOCK-005
**Dependencies:** JP-DEC-003, JP-ORDER-001
**Blocked by / decisions:** None

Apply approved inventory side effects to order lifecycle without returning stock prematurely.

**Acceptance criteria**

- Reservation/decrement moment exactly matches approved policy.
- Reservation expiry is implemented if required.
- Cancellation restores stock only in approved cases.
- Refused/returned stock does not become saleable before approved physical state.
- Concurrent order tests do not oversell beyond configured policy.

**Tests**

- Concurrent last-unit orders.
- Cancel before/after commit.
- Refused shipment.
- Return reception.

### JP-STOCK-003 — Implement inventory audit trail
**Type:** Story
**Priority:** P0
**Workstream:** Backend
**Requirements:** REQ-STOCK-006
**Dependencies:** JP-AUDIT-001, JP-STOCK-001
**Blocked by / decisions:** None

Record manual inventory changes for accountability.

**Acceptance criteria**

- Manual changes record actor/product/old/new/timestamp.
- Reason can be stored where provided.
- Audit records are visible only to authorized users.

### JP-CHECKOUT-001 — Build guest checkout fields and server-side validation
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend
**Requirements:** REQ-CHECKOUT-001, REQ-CHECKOUT-002, REQ-CHECKOUT-003, REQ-CHECKOUT-004
**Dependencies:** JP-GEO-001, JP-CART-002
**Blocked by / decisions:** None

Implement short Tunisia-oriented guest checkout with normalization and recoverable validation.

**Acceptance criteria**

- Guest checkout is enabled.
- Required fields/lengths match specification/carrier adjustments.
- Server-side validation exists for every required field.
- Tunisian phone input accepts legitimate formatting and normalizes internally.
- Invalid submissions preserve valid entered data where possible.

**Tests**

- Valid TN number variants.
- Invalid required fields.
- Long input boundaries.
- Guest order without account.

### JP-CHECKOUT-002 — Implement shipping cost, terms and final order summary
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend
**Requirements:** REQ-CHECKOUT-005, REQ-CHECKOUT-006, REQ-CHECKOUT-007
**Dependencies:** JP-GEO-002, JP-CHECKOUT-001
**Blocked by / decisions:** None

Ensure the customer sees complete cost/payment/delivery information before confirming.

**Acceptance criteria**

- Shipping cost is visible before final submission.
- Terms checkbox is required and never prechecked.
- Order summary shows products/quantities/discounts/shipping/total/payment/delivery.
- Changing shipping input updates totals deterministically.

### JP-CHECKOUT-003 — Implement idempotent order submission and checkout recovery
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Frontend
**Requirements:** REQ-CHECKOUT-008, REQ-CHECKOUT-009
**Dependencies:** JP-CHECKOUT-002, JP-ORDER-001
**Blocked by / decisions:** None

Prevent duplicate orders and keep checkout recoverable under slow/error conditions.

**Acceptance criteria**

- Double-click cannot create duplicate orders.
- Safe retry/page refresh does not create duplicate orders.
- Cart is preserved on failed checkout.
- Recoverable form data remains available.
- Checkout failure emits internal diagnostic event without sensitive-data leakage.

**Tests**

- Double-click simulation.
- Network retry.
- Refresh after slow response.
- Server validation failure.
- Third-party shipping/payment timeout.

### JP-SHIP-001 — Implement shipment domain model and admin data
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Operations
**Requirements:** REQ-SHIP-001, REQ-SHIP-002
**Dependencies:** JP-DEC-004, JP-ORDER-001
**Blocked by / decisions:** None

Store shipment and tracking data as structured operational data.

**Acceptance criteria**

- Shipment supports carrier/tracking/status/timestamps/estimate/cost.
- Shipment data is linked to the order.
- Authorized staff can manage shipment fields.
- Tracking data is not stored only in free-text notes.

### JP-PAY-001 — Implement COD payment method and rules
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Frontend
**Requirements:** REQ-PAY-001
**Dependencies:** JP-DEC-005, JP-CHECKOUT-002
**Blocked by / decisions:** None

Configure COD as a production payment method under approved business restrictions.

**Acceptance criteria**

- COD appears only where eligible.
- Any max-order/geographic restrictions are enforced server-side.
- Order records payment method/status separately from order status.
- Customer confirmation clearly identifies COD.

### JP-ORDER-002 — Implement order transition side effects
**Type:** Story
**Priority:** P0
**Workstream:** Backend
**Requirements:** REQ-ORDER-002
**Dependencies:** JP-ORDER-001, JP-DEC-003
**Blocked by / decisions:** None

Attach deterministic stock, communication, shipment and review side effects to transitions.

**Acceptance criteria**

- Each transition has documented side effects.
- Side effects are idempotent where retries are possible.
- Stock behavior follows approved inventory policy.
- No transition silently triggers unrelated effects.

### JP-ORDER-003 — Implement order notes, history and operational admin view
**Type:** Story
**Priority:** P0
**Workstream:** Backend / Frontend Admin
**Requirements:** REQ-ORDER-003, REQ-ORDER-004, REQ-ADMIN-003
**Dependencies:** JP-ORDER-001
**Blocked by / decisions:** None

Give authorized staff a complete operational view without exposing internal notes to customers.

**Acceptance criteria**

- Internal and customer-visible notes are distinct.
- Status history is visible.
- Payment/shipment/stock-relevant events are available.
- Authorized users can search/filter/process orders.
- Preparation/print artifacts required by operations are supported.

### JP-ORDER-004 — Build order confirmation page and transactional email
**Type:** Story
**Priority:** P0
**Workstream:** Frontend / Backend
**Requirements:** REQ-CONFIRM-001, REQ-CONFIRM-002
**Dependencies:** JP-CHECKOUT-003, JP-EMAIL-001
**Blocked by / decisions:** None

Confirm order creation to customer and operations team.

**Acceptance criteria**

- Confirmation page shows order number, summary, payment, delivery and next steps.
- Customer receives transactional confirmation email.
- Operations receives appropriate notification.
- Refresh does not create another order.

### JP-SEO-002 — Implement faceted crawl policy and removed-product behavior
**Type:** Story
**Priority:** P0
**Workstream:** Backend / SEO
**Requirements:** REQ-SEO-004, REQ-SEO-005
**Dependencies:** JP-DISC-003
**Blocked by / decisions:** None

Avoid index explosion and preserve useful authority when products disappear.

**Acceptance criteria**

- Filter combinations follow documented index/noindex/canonical policy.
- Pagination is crawlable as intended.
- Temporarily unavailable products are handled separately from permanently removed items.
- Relevant replacements use redirects where appropriate.

### JP-SEC-001 — Configure TLS, production headers and secure cookies
**Type:** Story
**Priority:** P0
**Workstream:** DevOps / Security
**Requirements:** REQ-SEC-001, REQ-SEC-005
**Dependencies:** JP-DEC-009
**Blocked by / decisions:** None

Configure transport/browser security without breaking checkout/payment integrations.

**Acceptance criteria**

- HTTPS is enforced.
- HSTS is enabled when production is ready.
- CSP/referrer/content-type/frame protections are configured appropriately.
- Session/auth cookies use appropriate Secure/HttpOnly/SameSite settings.

### JP-OBS-002 — Configure operational alerts
**Type:** Story
**Priority:** P0
**Workstream:** DevOps
**Requirements:** Architecture / operational requirement
**Dependencies:** JP-OBS-001
**Blocked by / decisions:** None

Alert the team on production conditions requiring action.

**Acceptance criteria**

- Site-down alert exists.
- 5xx/error spike alert exists.
- Checkout/integration failure alert exists where measurable.
- Disk/DB/certificate/backup/queue alerts exist as applicable.
- Alert recipients/escalation path are documented.
