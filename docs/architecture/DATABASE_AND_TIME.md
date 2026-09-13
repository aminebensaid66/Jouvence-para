# Database and Time Conventions

## Character set

MySQL development is configured for `utf8mb4` with `utf8mb4_unicode_ci`. Production must use full-Unicode storage compatible with WordPress/WooCommerce requirements.

## Time strategy

- Persist machine/event timestamps in UTC unless a provider contract explicitly requires another representation.
- Convert timestamps for business/customer display using `Africa/Tunis`.
- Do not store localized display strings as the authoritative timestamp.
- `JouvencePara\\Core\\Support\\Clock` is the core helper for UTC/business-time conversion.

## Schema migrations

Custom core-plugin schema uses ordered integer migrations.

- Current schema version is stored in the non-autoloaded `jp_core_schema_version` option.
- Fresh activation initializes the stored version to `0`.
- The migrator applies versions in ascending order and advances the option only after a migration succeeds.
- A migration must be safe to evaluate more than once; retries must not corrupt data.
- Never edit an already-released migration to change production history. Add a new migration.

Migration execution uses an atomic WordPress option lock. Concurrent requests leave execution to
the lock owner, abandoned locks become recoverable after five minutes, and the lock is released in
a `finally` block. Migration exceptions and schema-version persistence failures remain visible.
Every migration must still be idempotent because a process can stop after changing the database but
before recording its version.

Schema version `1` is an intentional no-table baseline so the migration mechanism exists before the first custom table is introduced.
