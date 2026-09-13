# Testing

## Sprint 1 harness

The repository intentionally starts with a dependency-free PHP test harness so foundational tests can run before Composer/npm test dependencies are selected.

Commands:

```bash
make test-unit
make test-integration
make test-e2e
make test-fast
```

- `unit`: pure PHP behavior without WordPress runtime dependencies.
- `integration`: plugin lifecycle/contracts tested with explicit WordPress-function fakes where required.
- `e2e`: HTTP smoke against `JP_E2E_BASE_URL`; skips when no running environment is supplied.
- `fixtures`: deterministic test data only. Production customer/catalog data must never be copied into tests.

Later tickets may add purpose-built WordPress integration infrastructure and browser automation while preserving these commands or replacing them through an ADR/documented migration.
