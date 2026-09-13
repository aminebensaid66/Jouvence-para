# Environments and Secrets Strategy

## Environments

Jouvence Para uses three logical environments:

- **development** — local Docker/runtime; fake or sandbox integrations only;
- **staging** — production-like verification; no real marketing sends and no production payment credentials;
- **production** — customer traffic and real integrations.

`JP_ENVIRONMENT` identifies the environment. Application behavior must never infer production from hostname alone.

## Configuration rules

- `.env.example` contains names and non-sensitive development defaults only.
- Real `.env` and environment-specific `.env.*` files remain outside Git.
- Staging and production use different provider credentials, databases and message destinations.
- Production secrets are injected by the selected hosting/deployment secret mechanism, never baked into theme/plugin source or Docker images.
- Frontend JavaScript must never receive server-only credentials.

## Provider credential naming

When integrations are introduced, use environment-injected names with the `JP_` prefix, for example:

- `JP_PAYMENT_API_KEY`;
- `JP_PAYMENT_WEBHOOK_SECRET`;
- `JP_CARRIER_API_TOKEN`;
- `JP_SMTP_PASSWORD`;
- `JP_WHATSAPP_TOKEN`.

Do not add real values to `.env.example`; use obvious placeholders.

## Staging isolation

Every external provider adapter must have an explicit sandbox/staging configuration. A staging deployment must fail closed rather than silently falling back to production credentials.

Before an integration is enabled on staging, its ticket must document:

1. sandbox endpoint/account;
2. credential source;
3. message/payment side effects;
4. safe test recipients or provider sandbox behavior.

## Logging and redaction

Never log passwords, session secrets, API keys, bearer tokens, webhook secrets, raw payment-card data, authentication cookies, or unnecessary personal data.

Integration logging should record safe identifiers such as provider event ID, order ID, HTTP status and redacted error category. Provider request/response bodies are not logged wholesale unless a ticket defines an explicit redaction policy.

## Rotation and incidents

If a secret is exposed:

1. rotate/revoke it at the provider immediately;
2. remove it from current source and deployment configuration;
3. assess Git/history/log exposure;
4. document the incident and affected environment;
5. deploy the replacement through the normal secret mechanism.

Deleting the current file is not sufficient if the secret entered Git history.
