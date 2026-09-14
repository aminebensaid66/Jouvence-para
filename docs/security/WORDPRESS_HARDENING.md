# WordPress hardening and 2FA — JP-SEC-002

Privileged Jouvence Para access means WordPress `administrator` and the `jp_store_manager` role. These users must enroll TOTP before normal wp-admin access. Enrollment and disable operations use dedicated nonces and apply only to the current privileged user.

## Controls

- TOTP (RFC 6238 / SHA-1 / 30-second / six digits) required after enrollment.
- Privileged users without enrollment are restricted to their profile/admin-post flow until enrollment completes.
- Password failures: 10 attempts per hashed username/IP key per 15 minutes.
- TOTP failures: 5 attempts per hashed user ID per 15 minutes.
- XML-RPC is disabled at the application layer for launch because no approved integration requires it.
- WordPress plugin/theme file-editor capability checks are denied with the public `file_mod_allowed` hook. Production should additionally set `DISALLOW_FILE_EDIT=true` in environment-managed `wp-config.php` as defense in depth.
- Privileged account creation/role grants emit a security event and remain covered by the existing audit-role hooks.
- Secrets are stored only in private user metadata; they must never be logged or exported to analytics.

## Emergency recovery

If an administrator loses the authenticator, recovery is an operational action performed by an authorized operator using WP-CLI/database access after identity verification. Delete `jp_2fa_secret`, `jp_2fa_pending_secret` and `jp_2fa_enabled` for that user, then require immediate re-enrollment. Do not expose a public recovery endpoint.
