# Branching and Release Workflow

## Branches

- `main` is the integration branch and must remain releasable.
- Create one short-lived branch per issue: `feat/<TICKET>-short-name`, `fix/<TICKET>-short-name`, or `chore/<TICKET>-short-name`.
- Do not combine unrelated tickets in one branch or pull request.

## Pull requests

Every pull request must include:

- ticket ID and linked issue;
- requirement IDs when applicable;
- behavior summary;
- tests and commands actually executed;
- screenshots for visual changes;
- migration/rollback notes when state changes;
- unresolved risks or follow-ups.

A dependent ticket must be rebased or regenerated after its dependency is merged. Sequential patch series must be applied in the documented order.

## Releases

- Production changes are merged to `main` only after required checks pass and review is complete.
- Release tags use `vMAJOR.MINOR.PATCH` once production releases begin.
- `CHANGELOG.md` records notable behavior and operational changes.
- Production deployments must use the deployment/rollback process defined by later delivery tickets; manual source editing on production is prohibited.
