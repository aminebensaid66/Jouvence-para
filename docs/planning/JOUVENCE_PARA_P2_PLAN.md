# Jouvence Para — P2 Plan

## Sprint goal

> Implement advanced loyalty only after the business rules and post-launch commercial data justify it.

## Entry criteria

- Loyalty earning, redemption, expiration and refund rules are approved.
- Core order/refund flows are stable.
- Business has a measurable loyalty objective.

## Assigned tickets

| Ticket ID    | Title                    | Type   | Priority   | Workstream         | Dependencies   | Blocked By / Decisions                      |
|:-------------|:-------------------------|:-------|:-----------|:-------------------|:---------------|:--------------------------------------------|
| JP-PROMO-004 | Implement loyalty ledger | Story  | P2         | Backend / Frontend | JP-DEC-008     | Loyalty earning/redemption/expiry rules TBD |

## Suggested parallel lanes

### Commerce / Backend
- **JP-PROMO-004** — Implement loyalty ledger

## Exit criteria

- Points are implemented with ledger semantics.
- Refund/return adjustments reconcile correctly.
- The feature can be audited and disabled safely.

## Dependency / blocker handling

A ticket must not be implemented by inventing an unresolved business rule. If its `Blocked By / Decisions` field is unresolved, the team may implement only the non-dependent foundation and must keep the behavior disabled or incomplete until the decision is approved.

## Ticket details

### JP-PROMO-004 — Implement loyalty ledger
**Type:** Story
**Priority:** P2
**Workstream:** Backend / Frontend
**Requirements:** REQ-LOYALTY-001, REQ-LOYALTY-002
**Dependencies:** JP-DEC-008
**Blocked by / decisions:** Loyalty earning/redemption/expiry rules TBD

Implement loyalty only after business rules are approved; use append-only/ledger semantics for points movements.

**Acceptance criteria**

- Every points movement has customer/delta/reason/order/timestamp.
- Refund/return effects follow approved rules.
- Balance is derived/reconcilable from ledger.
- No loyalty rule is invented in code.
