# Cookie Consent — JP-COOKIE-001

Jouvence Para uses an explicit consent contract for non-essential tracking.

## Categories

- **Essential** — always allowed; required for security, cart, checkout, account and order processing.
- **Analytics** — optional and disabled until the customer consents.
- **Marketing** — optional and disabled until the customer consents.

Preferences are stored for 180 days in the first-party `jp_consent_v1` cookie using a versioned base64url JSON payload. The cookie uses `Path=/`, `SameSite=Lax`, and `Secure` on HTTPS.

When a visitor withdraws a previously granted category, the page reloads after saving the new preference. This stops already-running optional scripts for the current document; integrations must also honor the server-side and client-side gates on the next load.

## Server-side gating

Core/integration code can check:

```php
$allowed = apply_filters('jouvence_para_consent_allows', false, 'analytics');
```

Non-essential server-enqueued integrations must not enqueue their tracker when the result is false.

## Client-side gating

Scripts that must exist in markup before consent can use a non-executable placeholder:

```html
<script type="text/plain" data-jp-consent="analytics" data-src="..."></script>
```

The consent controller activates matching placeholders only after the relevant category is allowed. It also exposes `window.JouvenceParaConsent` and emits `jouvencepara:consentchange` when preferences change.

The current store does not ship analytics or marketing trackers, so rejecting optional cookies has no effect on catalog, cart, checkout, account or order behavior.

## Accessibility

The banner is keyboard-operable. The preferences dialog moves focus into the dialog, supports Escape, returns focus to the opener, has visible focus styling through the theme, and respects reduced-motion preferences.
