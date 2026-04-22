# Generic security-audit checklist

Apply this checklist against the in-scope code. Each item is a concrete check a reviewer can perform by reading code, reading config, or running a command. Skip items that are not applicable to the current scope and say why in the "Not reviewed" section of the report.

## A. Authentication and session management

- Verify session is regenerated immediately after a successful login (`$request->session()->regenerate()`).
- Confirm `password` column is at least 60 characters (bcrypt hash length).
- Verify `remember_token` is nullable, at least 100 characters, and only populated when remember-me is explicitly requested.
- Confirm logout calls `Auth::logout()`, invalidates the session, and regenerates the CSRF token.
- Verify password confirmation is enforced on sensitive routes via `password.confirm` middleware; check `password_timeout` in `config/auth.php`.
- Confirm login throttling is active and keyed on username + IP (not IP alone).
- Verify session cookie has `Secure`, `HttpOnly`, and `SameSite` flags set in `config/session.php`. `Secure` must be `true` in production.
- Confirm idle session timeout is appropriate for the data sensitivity (OWASP: ≤30 min for low-risk, shorter for high-value).
- Verify the session driver is explicitly configured and not relying on a shared filesystem location.
- Confirm magic-link or any signed-URL flows have a bounded expiration and a first-use invalidation check; signed URLs are not one-time by themselves.
- Verify `MustVerifyEmail` is implemented on the `User` model if email verification is a security requirement (not just a UX nicety).

## B. Authorization and access control (IDOR prevention)

- Verify every resource-modifying action has a policy or gate check (`Gate::authorize`, `$this->authorize`, `can` middleware, `@can` in views).
- Confirm policy `before()` only early-returns `true` for admins (never a blanket `true`).
- Verify IDOR prevention: policies compare `$user->id` (or role) against the owning foreign key on the resource.
- Confirm `Response::denyAsNotFound()` is used where revealing resource existence is itself a leak (prefer 404 over 403 for private records).
- Verify route-model binding does not bypass authorization. Authorization must run in the controller or policy, not be implied by a binding succeeding.
- Confirm guest users are handled in policies with a nullable `?User` parameter where appropriate.
- Verify there are no frontend-only authorization gates (every check must also exist server-side).
- Confirm UI elements respect `@can` so hidden form fields aren't exposing actions the user cannot perform.
- Verify custom `Auth::attempt` closures use callables, not raw SQL concatenation.

## C. Input validation and injection

- Confirm every controller action and every Livewire component validates input via `$request->validate()`, a `FormRequest`, or `#[Validate]` rules.
- Verify validation rules are allowlists (specific types, lengths, formats), not just `required`.
- Confirm Eloquent and the query builder are used with bindings; no string concatenation into queries.
- Verify no `whereRaw`, `selectRaw`, `orderByRaw`, `havingRaw`, or `DB::raw` receives user input without passing the input through the bindings array (second argument).
- Confirm no `DB::statement`, `DB::select`, or `DB::unprepared` is called with user input.
- Verify no `exec`, `shell_exec`, `system`, `passthru`, or backtick operator is reached with user-controlled strings.
- Confirm nested input uses dot notation in validation rules (e.g., `'contact.email'`).
- Verify file-upload MIME validation does not trust the `Content-Type` header alone.
- Confirm `unserialize()` is never called on untrusted data; prefer `json_decode`.
- Verify `eval()` is not used anywhere.

## D. Output encoding and XSS

- Verify `{{ $var }}` is used for all untrusted output (Blade auto-escapes).
- Confirm every `{!! $var !!}` is either (a) rendering static HTML, (b) rendering admin-curated content that is sanitized on write, or (c) a known vendor template like Markdown mailer. Any other use is a finding.
- Verify no user input is concatenated into inline JavaScript or HTML attributes without escaping.
- Confirm AJAX responses return JSON and the frontend uses `.text()` / `textContent` for dynamic insertion, not `.html()` / `innerHTML`.
- Verify alt/title/data-* attributes interpolate escaped values.
- Confirm user-supplied URLs are validated against an allowlist of safe schemes (`http`, `https`, `mailto`) before being rendered as `href`.
- Verify no deprecated `{{{ $var }}}` triple-brace syntax remains.

## E. CSRF and state-changing requests

- Verify `VerifyCsrfToken` middleware is applied to the `web` group and has no unjustified route exclusions.
- Confirm every POST/PUT/PATCH/DELETE form includes `@csrf`.
- Verify AJAX requests send the `X-CSRF-TOKEN` header (Axios auto-sends it from the `XSRF-TOKEN` cookie).
- Confirm no state-changing action is reachable via GET (no `Route::get('.../delete', ...)`).
- Verify CSRF cookie domain is not broader than needed (do not set a domain unless subdomain sharing is required).
- Confirm the CSRF token regenerates on logout and on privilege changes.
- Verify webhook routes (Stripe, Twilio, etc.) are explicitly excluded from CSRF and replaced with signature verification.

## F. Sensitive data and PII handling

- Confirm all PII columns that justify it (SSN, DOB, address, phone where sensitive) are encrypted at rest using `encrypted` cast or `Crypt::encryptString`.
- Verify `APP_KEY` is generated with `php artisan key:generate`, is in `.env`, and is not committed.
- Confirm no PII is written to logs (no `Log::info($user)`, no request-body dumps of sensitive payloads).
- Verify `.env` is in `.gitignore` and has never been committed (spot-check git history).
- Confirm HTTPS is enforced in production (middleware, web-server redirect, or `URL::forceScheme`).
- Verify password reset tokens are hashed in storage (Laravel default — confirm nothing overrode it).
- Confirm PII is never placed in URLs, query strings, or GET form submissions.
- Verify `autocomplete="off"` or `autocomplete="new-password"` is set on sensitive form fields where appropriate.
- Confirm response headers do not advertise server/framework versions unnecessarily.
- Verify data-retention policy is represented in code — old records deleted or anonymized on schedule if policy requires it.

## G. Cryptography, secrets, APP_KEY

- Verify `APP_KEY` is set and base64-encoded; not empty, not a placeholder.
- Confirm encryption is Laravel's built-in `Crypt` (AES-256-GCM or AES-256-CBC with MAC); no custom crypto.
- Verify `try/catch` wraps `Crypt::decryptString()` where a failed decrypt should not 500.
- Confirm `APP_PREVIOUS_KEYS` is planned for key rotation (even if unused today, document the rotation flow).
- Verify sensitive config values come from `env()` and are not hardcoded in `config/*.php`.
- Confirm `config/*.php` is the only place `env()` is called (so `config:cache` doesn't break at runtime).
- Verify password hashing uses bcrypt or Argon2id (`HASH_DRIVER`), with `Hash::needsRehash()` checked on login to auto-upgrade.
- Confirm `.env.example` contains placeholders only, no real secrets.

## H. File uploads and storage

- Confirm the allowed-extension list is an allowlist, not a blocklist.
- Verify file-size limits are enforced at both validation and web-server level.
- Confirm MIME validation uses the file's sniffed type, not the uploaded Content-Type header.
- Verify magic-byte / file-signature verification for high-risk flows.
- Confirm uploads are stored outside the webroot OR served through a controller with authorization, never directly accessible by URL.
- Verify `basename()` or similar is used to strip directory traversal from client-supplied filenames.
- Confirm filenames on disk are randomized (UUID) rather than user-controlled.
- Verify SVG uploads are rejected unless sanitized (they can contain script).
- Confirm upload endpoints require auth and have rate limits.
- If no file-upload features exist today: verify this section in the report by stating "no upload endpoints; any new upload is itself a finding to review."

## I. Rate limiting, brute force, account enumeration

- Verify login throttling is active and keyed on email + IP.
- Confirm password-reset request endpoint is throttled at the HTTP layer (not only at the notification layer).
- Verify API rate limits are applied if any API routes exist.
- Confirm custom `RateLimiter::for()` definitions exist for sensitive actions (file uploads, outbound email, SMS).
- Verify rate-limit state uses a persistent store (database, cache) rather than the file driver on a multi-process host.
- Confirm password-reset responses do not disclose whether an email is registered (same message for valid and invalid addresses).
- Verify login error messages are generic ("invalid credentials", not "user not found" vs "wrong password").
- Confirm registration flow does not let an attacker enumerate existing accounts via timing or error differences.
- Verify email verification is enforced on sign-up for any app that accepts public registrations, to deter spam accounts.

## J. Dependencies and supply chain

- Confirm `composer.lock` is committed.
- Verify `composer audit` runs clean (no advisories on locked versions).
- Confirm direct dependencies (`composer.json`) are within one minor version of current stable.
- Verify no abandoned packages are in use (`composer show --outdated` flags them).
- Confirm Dependabot or equivalent is monitoring both Composer and npm lockfiles.
- Verify frontend dependencies are also audited (`npm audit`).
- Confirm critical packages (auth, encryption, framework) are first-party or well-maintained.

## K. Error handling, logging, and debug exposure

- Verify `APP_DEBUG=false` in production `.env`.
- Confirm `APP_ENV=production` is set in production.
- Verify error responses do not include stack traces, framework versions, or SQL snippets to end users.
- Confirm log files are stored outside the webroot and not world-readable.
- Verify no `dd()`, `dump()`, `var_dump()`, or `ray()` call remains in production code paths.
- Confirm no `Log::debug()` or `Log::info()` line includes full request bodies or password fields.
- Verify 404 and 403 responses are generic; do not leak resource existence where that is itself sensitive.
- Confirm unhandled exceptions are reported to a monitoring channel (Sentry, Flare, or equivalent) rather than silently dropped.

## L. DreamHost shared-hosting specifics

- Confirm the domain's document root points at `public/`, not the project root. (Test: fetch `/.env` over HTTP; it should 404.)
- Verify `.env` file permissions are `600` (owner read/write only) and the file is owned by the application user.
- Confirm `storage/` and `bootstrap/cache/` are writable only by the application user; directories `750`, files `640`.
- Verify `public/` files are `644` and directories `755`.
- Confirm session storage does not rely on a shared `/tmp` directory (use `database` or `redis` session driver).
- Verify scheduled commands are wired through the shared-host cron panel with the correct PHP binary path, and their output is captured to a log file outside the webroot.
- Confirm any `phprc` or `php.ini` override does not re-enable dangerous functions (`exec`, `shell_exec`, `passthru`) beyond what the app requires.
- Verify `open_basedir` is restricted to the project tree if configurable.
- Confirm the storage symlink (`public/storage -> storage/app/public`) is in place after every deploy and does not expose private paths.
- Verify shared-host resource limits (memory, execution time) are high enough that scheduled jobs do not fail silently — errors should land in the Laravel log, not the shared-host error log.

## M. Laravel-specific pitfalls

- Verify every model uses `$fillable` (allowlist) or `$guarded` (denylist). No `$guarded = []` anywhere.
- Confirm `$request->validated()` or `$request->only([...])` is used before `Model::create()` / `->update()`; never `->all()`.
- Verify no `{{{ $var }}}` (deprecated triple-brace) remains.
- Confirm every `{!! $var !!}` has a documented justification as described in section D.
- Verify no `whereRaw` / `selectRaw` / `orderByRaw` with string-interpolated user input; all user values must pass through bindings.
- Confirm route-model binding is paired with a policy or gate check; a 404 from a missing model is not an authorization control.
- Verify `image` validation rule is used with care — SVGs are technically images but XSS-capable unless sanitized.
- Confirm no `unserialize()` on Request data; serialization format should be JSON.
- Verify `config/` files contain no hardcoded secrets; all secrets come from `env()`.
- Confirm `APP_DEBUG`, `APP_ENV`, `APP_URL` are correct for production.
- Verify sensitive routes (change password, delete account, change email) require `password.confirm` middleware.
- Confirm global scopes (soft-delete, tenant isolation) apply consistently and are not bypassed by `withoutGlobalScopes()` in user-facing code.
- Verify eager-loaded relationships (`with`, `load`) do not expose fields that should be authorization-gated.
- If a custom auth guard exists, confirm `validateCredentials()` uses `Hash::check()`, never a plaintext comparison.
- Verify Livewire components validate `#[Validate]` rules on every mutating action and do not trust `wire:model` bindings to enforce types server-side.
