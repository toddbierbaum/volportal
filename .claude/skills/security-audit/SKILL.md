---
name: security-audit
description: Whole-codebase security audit for the VolPortal Laravel app (auth, session, PII, XSS, CSRF, rate limiting, DreamHost hosting specifics). Use when the user asks for a security review, security audit, threat model, vulnerability scan, or pre-release hardening check. Distinct from the bundled /security-review, which only inspects pending branch changes.
argument-hint: [optional path to narrow scope, e.g. app/Livewire]
allowed-tools: Read, Grep, Glob, Bash(composer audit), Bash(composer outdated *), Bash(php artisan route:list*), Bash(php artisan about*), Bash(grep *), Bash(rg *), Bash(find *), Bash(mkdir *), Bash(date *), Write
---

# VolPortal Security Audit

You are performing a whole-codebase security audit of the VolPortal Laravel app. Scope: `$ARGUMENTS` if provided, otherwise the entire project.

VolPortal is a volunteer portal for Florida Chautauqua Theater. Laravel 13 / PHP 8.4 / Livewire 4 / MySQL, deployed to DreamHost shared hosting. It stores volunteer PII (name, email, phone, role, certifications) and has admin and volunteer roles. Central Time zone.

## Procedure

1. **Read the generic checklist** at `.claude/skills/security-audit/checklist.md`. Apply every section (A–M) to the in-scope code.
2. **Run the VolPortal known-gap regression watch** (section below). For each item, report UNCHANGED / IMPROVED / REGRESSED / FIXED.
3. **Run the verification commands** (section below). Incorporate anomalies into findings; do not dump raw output.
4. **Produce a severity-ranked findings report** using the format at the end of this file. Emit it in chat.
5. **Persist the report** to `storage/audits/YYYY-MM-DD.md` (create the directory if missing; use the local date from `date +%Y-%m-%d`). If a file already exists for today, append with a `## Run N — HH:MM` divider rather than overwriting.

Skip checklist items that are not applicable. If the code has no file uploads today, section H is N/A — but if a new upload endpoint has appeared, that is itself a finding to review.

Severity by impact, not by category. An admin-writable stored XSS is Critical even though XSS lives in section D. A missing `SESSION_ENCRYPT=true` with a database session driver and off-site backups is High, not Medium.

## VolPortal known-gap regression watch

These are known gaps or accepted-risk items from prior audits. Re-verify each; flag any that have silently worsened or now have a clear fix opportunity.

1. **Unencrypted session payloads in DB.** `config/session.php` defaults `SESSION_ENCRYPT` to `false` with a `database` driver. Session rows carry auth context in plaintext. Flag if backups leave the server without being encrypted.
2. **Email verification disabled on `User`.** `app/Models/User.php:5` has `// use Illuminate\Contracts\Auth\MustVerifyEmail;` commented out. Verification routes exist in `routes/auth.php` but the contract is not implemented. Flag any new flow that assumes `email_verified_at` is meaningful.
3. **Stored-XSS vector in public layout.** `resources/views/components/layouts/public.blade.php:14` renders `{!! Setting::get('google_analytics_code', '') !!}`. Admin-writable → stored XSS for every public visitor. Verify input-side sanitization in the settings manager OR that the value is constrained to a GA measurement ID pattern. If neither, this is **High**.
4. **No HTTP-endpoint rate-limit on password reset.** The default throttle lives inside the notification (60s); the reset POST route is not behind a `throttle:` middleware. Check `routes/auth.php`.
5. **No 2FA for admins.** Password-only admin login. Magic links are volunteer-only by controller logic. Confirm no new component has introduced partial 2FA scaffolding.
6. **No audit log, no soft deletes, no encrypted PII columns.** `User` carries name/email/phone/role/cert-timestamps/opt-ins in plaintext with no deletion trail. Verify no new PII column has been added without an encryption + retention decision.
7. **`SESSION_SECURE_COOKIE` missing from `.env.example`.** Production must be `true`. Confirm the production deploy sets it; flag if the example file still omits it.
8. **Magic-link signed URLs.** Verify signed-route expiration is set (not indefinite) and that the magic-link controller invalidates the token after first use. Replay of a leaked URL is the failure mode.
9. **Scheduled commands.** `SendOpportunityAlerts`, `SendSignupReminders`, `SendPendingVolunteersDigest` run on DreamHost cron. Verify none log recipient PII at `info` level or emit raw tokens into logs.
10. **Raw SQL.** The only known raw SQL is `selectRaw('SUM(hours_worked) as total')` — aggregate, no user input. Any new `selectRaw` / `whereRaw` / `orderByRaw` / `DB::raw` that touches user input without bindings is an immediate **High** finding.

## Verification commands

Run these (read-only). Note anomalies in the findings — do not paste raw output into the report.

- `composer audit` — CVEs in locked dependencies
- `composer outdated --direct` — drift from latest for direct deps (laravel/framework ^13, livewire/livewire ^4, livewire/volt ^1.7, twilio/sdk ^8.11)
- `php artisan route:list --except-vendor` — enumerate routes; confirm `admin` middleware on every `/admin/*` route
- `grep -rn "DB::raw\|whereRaw\|selectRaw\|orderByRaw" app/` — raw-SQL sweep
- `grep -rn "{!!" resources/views/` — unescaped Blade output sweep
- `grep -rn "\\$request->validate\|->validate(" app/Livewire app/Http` — spot-check validation coverage on new controllers and Livewire components
- `grep -rn "Gate::\|->authorize(\|@can" app/ resources/views/` — authorization coverage
- `grep -rn "Log::\\(debug\\|info\\)" app/` — candidates for PII-in-logs review

## Output format

Emit a single markdown report with this structure:

```
# VolPortal Security Audit — YYYY-MM-DD

## Summary
<2–3 sentences: overall posture; top 3 issues.>

## Findings

### Critical
- **<title>** — `path/to/file.php:LINE`
  Evidence: <quote or describe>
  Impact: <why it matters for VolPortal specifically>
  Remediation: <concrete fix>

### High
...

### Medium
...

### Low / Informational
...

## Known-gap regression status
1. Unencrypted DB sessions — UNCHANGED / IMPROVED / REGRESSED / FIXED
2. Email verification disabled — ...
3. Stored-XSS via GA snippet — ...
4. No endpoint rate-limit on password reset — ...
5. No admin 2FA — ...
6. No audit log / soft deletes / PII encryption — ...
7. SESSION_SECURE_COOKIE in .env.example — ...
8. Magic-link expiry + first-use invalidation — ...
9. Scheduled-command PII logging — ...
10. Raw SQL — ...

## Verification command output
- composer audit: <summary>
- composer outdated --direct: <summary>
- route:list: <anomalies or "clean">
- grep sweeps: <anomalies or "clean">

## Not reviewed / out of scope
<items skipped and why>
```

Then write the identical content to `storage/audits/YYYY-MM-DD.md`.

## When not to use this skill

- Reviewing a single pending branch diff — use the bundled `/security-review` instead.
- Dependency-only check — just run `composer audit` directly.
- Live penetration testing or active exploitation — out of scope; this skill is static analysis plus read-only shell checks.
