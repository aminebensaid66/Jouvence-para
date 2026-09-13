# Jouvence Para — Post-launch P1 Plan

## Sprint goal

> Activate commercial-growth features on top of stable production operations without changing the core architecture.

## Entry criteria

- Production ordering and operations are stable.
- Relevant provider/business rules for each feature are approved.
- Post-launch metrics are available to validate priority.

## Assigned tickets

| Ticket ID    | Title                                                | Type   | Priority   | Workstream                       | Dependencies                           | Blocked By / Decisions                    |
|:-------------|:-----------------------------------------------------|:-------|:-----------|:---------------------------------|:---------------------------------------|:------------------------------------------|
| JP-STOCK-005 | Implement back-in-stock subscriptions                | Story  | P1         | Backend / Frontend               | JP-STOCK-001, JP-EMAIL-001             |                                           |
| JP-PROMO-003 | Implement packs/routines                             | Story  | P1         | Backend / Frontend               | JP-STOCK-002, JP-PROMO-001             |                                           |
| JP-PAY-003   | Integrate online payment provider                    | Story  | P1         | Backend / Frontend / Integration | JP-DEC-005, JP-PAY-002, JP-WEBHOOK-001 | Provider selection/contract required      |
| JP-ORDER-005 | Implement transactional SMS/WhatsApp order messaging | Story  | P1         | Backend / Integration            | JP-ORDER-004                           | Provider, consent/legal/template approval |
| JP-WISH-001  | Implement account wishlist                           | Story  | P1         | Frontend / Backend               | JP-ACC-001, JP-PDP-001                 |                                           |
| JP-REV-001   | Implement moderated verified-purchase reviews        | Story  | P1         | Backend / Frontend               | JP-ACC-001, JP-ORDER-002               |                                           |
| JP-WA-002    | Implement safe cart sharing to WhatsApp              | Story  | P1         | Frontend / Backend               | JP-WA-001, JP-CART-001                 |                                           |

## Suggested parallel lanes

### Customer Growth
- **JP-STOCK-005** — Implement back-in-stock subscriptions
- **JP-PROMO-003** — Implement packs/routines
- **JP-WISH-001** — Implement account wishlist
- **JP-REV-001** — Implement moderated verified-purchase reviews

### Communication
- **JP-ORDER-005** — Implement transactional SMS/WhatsApp order messaging
- **JP-WA-002** — Implement safe cart sharing to WhatsApp

### Payments
- **JP-PAY-003** — Integrate online payment provider

## Exit criteria

- Selected P1 features are independently releasable behind feature flags where appropriate.
- New communications/integrations follow consent, security and observability rules.
- Each feature has production metrics and rollback/disable behavior.

## Dependency / blocker handling

A ticket must not be implemented by inventing an unresolved business rule. If its `Blocked By / Decisions` field is unresolved, the team may implement only the non-dependent foundation and must keep the behavior disabled or incomplete until the decision is approved.

## Ticket details

### JP-STOCK-005 — Implement back-in-stock subscriptions
**Type:** Story
**Priority:** P1
**Workstream:** Backend / Frontend
**Requirements:** REQ-STOCKALERT-001, REQ-STOCKALERT-002
**Dependencies:** JP-STOCK-001, JP-EMAIL-001
**Blocked by / decisions:** None

Allow customers to subscribe to restock notifications with consent and duplicate/rate controls.

**Acceptance criteria**

- Subscription stores product/variation/contact/consent/status.
- Notification only sends when item becomes purchasable.
- Repeated restock notifications are rate-limited.
- Customer can avoid duplicate active subscriptions.

### JP-PROMO-003 — Implement packs/routines
**Type:** Story
**Priority:** P1
**Workstream:** Backend / Frontend
**Requirements:** REQ-PACK-001, REQ-PACK-002
**Dependencies:** JP-STOCK-002, JP-PROMO-001
**Blocked by / decisions:** None

Support composed packs whose availability reflects component stock unless explicitly independently stocked.

**Acceptance criteria**

- Pack contents and quantities are visible.
- Availability uses documented component-stock rule.
- Savings are displayed only from valid prices.
- Adding a pack cannot oversell a component.

### JP-PAY-003 — Integrate online payment provider
**Type:** Story
**Priority:** P1
**Workstream:** Backend / Frontend / Integration
**Requirements:** REQ-PAY-003, REQ-PAY-005, REQ-WEBHOOK-001, REQ-WEBHOOK-002, REQ-WEBHOOK-003
**Dependencies:** JP-DEC-005, JP-PAY-002, JP-WEBHOOK-001
**Blocked by / decisions:** Provider selection/contract required

Integrate approved provider using sandbox first, signed/idempotent webhooks and reconciliation-ready transaction IDs.

**Acceptance criteria**

- Sandbox and production credentials are isolated.
- Successful/failed/cancelled payment paths are handled.
- Webhook signatures are verified where supported.
- Duplicate webhook events do not duplicate side effects.
- Provider transaction IDs are persisted safely.
- Failed webhook processing is observable/retryable.

### JP-ORDER-005 — Implement transactional SMS/WhatsApp order messaging
**Type:** Story
**Priority:** P1
**Workstream:** Backend / Integration
**Requirements:** REQ-CONFIRM-003
**Dependencies:** JP-ORDER-004
**Blocked by / decisions:** Provider, consent/legal/template approval

Add transactional messaging only after provider and compliance requirements are approved.

**Acceptance criteria**

- Templates are approved.
- Message retries/delivery failures are observable.
- Transactional and marketing messaging remain separate.

### JP-WISH-001 — Implement account wishlist
**Type:** Story
**Priority:** P1
**Workstream:** Frontend / Backend
**Requirements:** REQ-WISH-001
**Dependencies:** JP-ACC-001, JP-PDP-001
**Blocked by / decisions:** None

Add/remove/view wishlist and move available products to cart.

**Acceptance criteria**

- Wishlist persists to authenticated account.
- Unavailable items remain clearly marked.
- Anonymous merge behavior is documented if anonymous wishlist is added.

### JP-REV-001 — Implement moderated verified-purchase reviews
**Type:** Story
**Priority:** P1
**Workstream:** Backend / Frontend
**Requirements:** REQ-REVIEW-001, REQ-REVIEW-002, REQ-REVIEW-003, REQ-REVIEW-004
**Dependencies:** JP-ACC-001, JP-ORDER-002
**Blocked by / decisions:** None

Create review collection/moderation with trustworthy verified-purchase labeling.

**Acceptance criteria**

- Moderation workflow exists.
- Verified label requires eligible delivered/completed order association.
- Spam/abuse controls exist.
- Aggregate rating schema matches visible real reviews only.

### JP-WA-002 — Implement safe cart sharing to WhatsApp
**Type:** Story
**Priority:** P1
**Workstream:** Frontend / Backend
**Requirements:** REQ-WA-003
**Dependencies:** JP-WA-001, JP-CART-001
**Blocked by / decisions:** None

Allow cart context without leaking customer/private information through public URLs.

**Acceptance criteria**

- Shared context contains only approved product/cart information.
- No address, email, phone, session token or private order data is exposed.
- Expired/changed products are handled gracefully.
