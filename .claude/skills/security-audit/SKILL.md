---
name: security-audit
description: Whole-codebase Laravel security audit (OWASP Top 10, Laravel pitfalls, shared-hosting specifics). Use when the user asks for a security review, security audit, threat model, vulnerability scan, or pre-release hardening check. Distinct from the bundled /security-review, which only inspects pending branch changes.
argument-hint: [optional path to narrow scope]
allowed-tools: Read, Grep, Glob, Bash(composer audit), Bash(composer outdated *), Bash(php artisan route:list*), Bash(php artisan about*), Bash(grep *), Bash(rg *), Bash(find *), Bash(mkdir *), Bash(date *), Write
---

# Laravel security audit

You are performing a whole-codebase security audit of a Laravel application. Scope: `$ARGUMENTS` if provided, otherwise the entire project.

This skill is a static-analysis sweep. It reads code, runs read-only shell checks, and produces a severity-ranked findings report. It does not perform live exploitation or modify any code.

## Procedure

1. **Read the generic checklist** at `.claude/skills/security-audit/checklist.md`. Apply every section (A–M) to the in-scope code.
2. **Project-specific regression watch.** If the file `.claude/skills/security-audit/known-gaps.local.md` exists in this skill directory, read it and apply each item as a regression check in addition to the generic checklist. If it does not exist, skip this step and proceed with only the generic checklist. This file is intentionally gitignored — it holds project-specific references that must not appear in the committed repo.
3. **Run the verification commands** (section below). Incorporate anomalies into findings; do not dump raw output into the report.
4. **Produce a severity-ranked findings report** using the output format at the end of this file. Emit it in chat.
5. **Persist the report** to `storage/audits/YYYY-MM-DD.md` (create the directory if missing; use the local date from `date +%Y-%m-%d`). If a file already exists for today, append with a `## Run N — HH:MM` divider rather than overwriting.

Skip checklist items that are not applicable. If a category is N/A, say so in the "Not reviewed / out of scope" section of the report with a one-line reason.

Severity by impact, not by category. An admin-writable stored XSS is Critical even though XSS lives in section D. A missing `SESSION_ENCRYPT=true` with a database session driver and off-site backups is High, not Medium. Use your judgement and explain the impact.

## Verification commands

Run these (all read-only). Note anomalies in the findings — do not paste raw output into the report.

- `composer audit` — CVEs in locked dependencies
- `composer outdated --direct` — drift from latest for direct dependencies
- `php artisan route:list --except-vendor` — enumerate routes; confirm auth/admin middleware is applied where expected
- `grep -rn "DB::raw\|whereRaw\|selectRaw\|orderByRaw" app/` — raw-SQL sweep
- `grep -rn "{!!" resources/views/` — unescaped Blade output sweep
- `grep -rn "\\$request->validate\|->validate(" app/Livewire app/Http` — spot-check validation coverage
- `grep -rn "Gate::\|->authorize(\|@can" app/ resources/views/` — authorization coverage
- `grep -rn "Log::\\(debug\\|info\\)" app/` — candidates for PII-in-logs review

## Output format

Emit a single markdown report with this structure:

```
# Security audit — YYYY-MM-DD

## Summary
<2–3 sentences: overall posture; top 3 issues.>

## Findings

### Critical
- **<title>** — `path/to/file.php:LINE`
  Evidence: <quote or describe>
  Impact: <why it matters for this app specifically>
  Remediation: <concrete fix>

### High
...

### Medium
...

### Low / Informational
...

## Project regression watch status
<one line per item in known-gaps.local.md: UNCHANGED / IMPROVED / REGRESSED / FIXED; or "skipped — no local watchlist present" if step 2 was skipped>

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
