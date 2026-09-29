# Customer accounts — JP-ACC-001

Implements REQ-ACCOUNT-001/002 using native WooCommerce/WordPress authentication,
logout, password reset/change and customer profile/billing/shipping address forms.
No custom password, token, session or address storage is introduced.

## Native account setup

An administrator's first setup configures account registration and guest checkout.
If no native account page is configured, WooCommerce creates **Mon compte** with
`[woocommerce_my_account]`. Existing pages/content are not overwritten. A missing,
unpublished, non-page or assigned page without the native account shortcode, or a
configuration failure, prevents recording the setup version;
the fixed `jouvence_para_account_configuration_error` hook reports the category.
After version 1, normal WooCommerce settings remain operator-controlled.

The theme links to the published native account page. WooCommerce owns dashboard
endpoints, nonce-protected logout, current-user profile/address edits, current
password verification and reset key expiration. The existing login throttle and
privileged-user TOTP policy remain active; its optional code field also appears on
the native WooCommerce login form. Ordinary customer accounts are not required to
enroll in staff two-factor authentication.

WooCommerce username/password generation follows its existing settings. Working
registration/password-reset email delivery requires the sender/provider/DNS work
in JP-EMAIL-001; this ticket does not claim live email delivery or select a sender.
French WordPress/WooCommerce language packs and public HTTPS are release checks.

## Communication preference

The native account-details form displays the owner's current email-marketing
preference. The chooser defaults to **Conserver mon choix actuel**; enabling offers
requires explicitly selecting **J’accepte de recevoir des offres par e-mail** and
saving. No signup/marketing grant is preselected. New, malformed or unknown-version
records default off. Selecting disable withdraws the preference; selecting keep
does not renew or alter it.

Store one private WooCommerce customer metadata record,
`_jp_communication_preferences`: version 1, a boolean email-marketing choice and
UTC change timestamp. No address, password, cookie, reset key or provider data is
included. No public preference endpoint or user-ID input is exposed.

Native account nonce, a separate owner-bound nonce, action, presence marker and
controlled choice are validated before the native save. Consume the authorized
choice afterward for that same logged-in user: password updates may rotate the
session/nonces between these hooks. Direct/replayed callbacks, another user's ID,
invalid requests and native save errors cannot write preferences. Equal choices
do not rewrite timestamps. Metadata readback failure displays an error instead of
claiming success.

This is a saved communication preference, not newsletter provider enrollment or
confirmation. JP-MKT-001 must integrate delivery/unsubscribe and honor withdrawals
before sending marketing. Browser tracking consent is independent. Transactional
account/order/security messages remain separate from marketing.

## Focused runtime checks before release

1. Run administrator setup; check a single published native account page,
   registration enabled and guest checkout still optional. Repeat setup and confirm
   no duplicate pages/content changes. Check private/missing-page failure handling.
2. In an incognito browser, register, log in and log out through native account
   navigation. Confirm logout nonce and account privacy; exercise login throttling.
3. Change personal details and each saved address as customer A; requests selecting
   customer B must never edit/read B. Verify native geography and field validation.
4. Change password with incorrect/current/mismatched values, then correctly. Verify
   native session behavior and old-password rejection. Change preference in the
   same request and confirm it persists despite native nonce/session rotation.
5. Exercise valid/expired/replayed password reset keys and actual delivery through
   the approved sender. Never record keys/passwords in logs/screenshots.
6. Inspect the chooser's label/keyboard/focus/native validation. Enable, reload,
   keep and disable the choice; check default off, timestamp stability and privacy.
7. Use an enrolled staff account on native WooCommerce login; missing/invalid TOTP
   must fail, valid TOTP must authenticate under the existing policy.

Automated tests cover configuration, owner/nonce validation, saved choice/replay,
password-session rotation, malformed records, persistence failure and escaped
account navigation with explicit fixtures.

## Isolated runtime verification

On 2026-09-29, an isolated Docker project with fresh test volumes ran WordPress
7.1, WooCommerce 11.1.2, PHP 8.3 and MySQL 8.4. Native page creation and account
configuration completed. Agent-browser verified customer registration, login,
nonce-protected logout, profile editing, shipping-address saving, and explicit
marketing enable/withdrawal. A password change and withdrawal in the same native
form submission succeeded; WooCommerce metadata and WordPress password checks
confirmed the saved choice, new password and old-password rejection. New native
registrations defaulted to marketing off. Browser error collection was empty.

Native WordPress API checks accepted a valid reset key and rejected an expired
key and a used key. Keys and passwords were not included in the report. The
runtime checks used synthetic customers; no production data or existing site's
volumes were reused. Actual email delivery, complete customer-isolation/security
testing, enrolled-staff TOTP, and the production HTTPS/environment matrix remain
release checks. These local checks do not claim production verification.

Rollback: revert this commit; native WooCommerce account pages/settings remain
operational and private preference records remain stored but inert. Disable
marketing integrations until preference handling is restored. Retention/export
policy belongs to JP-PRIV-001.
