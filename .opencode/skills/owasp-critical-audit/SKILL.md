---
name: owasp-critical-audit
description: >-
  Runs a static OWASP Top 10 security audit on Blogr (Laravel 12.x, FilamentPHP v4,
  SQLite, blocks-based content, Translation-First). Covers only the five
  most critical categories: A01 access control, A02 cryptographic failures,
  A03 injection, A05 misconfiguration, A07 authentication failures. Use when the
  user asks for a security audit, OWASP review, vulnerability scan of package
  code, or to refresh SECURITY-AUDIT.md.
compatibility: OpenCode agent with read access to the repository. Static review only — no dependency CVE scan or penetration test.
metadata:
  author: happytodev
  version: "1.0"
  reference: OWASP Top 10 (2021)
---

# OWASP Critical Security Audit

Static security review of this Blogr package. Scope and severity model follow [SECURITY-AUDIT.md](../../SECURITY-AUDIT.md) at the repository root.

## When to run

Invoke explicitly when the user requests a security audit, OWASP check, or updated `SECURITY-AUDIT.md`. Do not run proactively on unrelated tasks.

## Out of scope

- Dependency / CVE scanning (`composer audit`, Snyk, etc.)
- Dynamic penetration testing or production probing
- Host application auth layer (login, registration, password reset — those belong to the host Laravel app, not this package)
- OWASP categories **not** in the critical five below (A04, A06, A08, A09, A10)
- Medium/Low/Informational findings unless the user asks for full coverage

## Workflow

Copy this checklist and track progress:

```
Audit progress:
- [ ] 1. Confirm scope and read project context (README, composer.json, prior SECURITY-AUDIT.md)
- [ ] 2. Run checks per references/checklist.md (A01, A02, A03, A05, A07 only)
- [ ] 3. Record only Critical / High findings with file:line evidence
- [ ] 4. Write report using references/report-template.md
- [ ] 5. Save to SECURITY-AUDIT.md (or path given by the user)
```

### Step 1 — Context

Identify stack and attack surface:

- Laravel 12.x, FilamentPHP v4 admin panel, SQLite, blocks-based content, Translation-First architecture, import/export services, extension adapters
- Key paths: `src/BlogrServiceProvider.php`, `src/Http/Controllers/`, `src/Http/Middleware/`, `src/Models/`, `src/Policies/`, `src/Services/`, `src/Extensions/`, `src/Filament/`, `resources/views/`, `database/seeders/`, `config/blogr.php`, `.env.example`, `.gitignore`

Routes are registered in `BlogrServiceProvider::packageBooted()` — not in separate route files.

Read [references/checklist.md](references/checklist.md) before inspecting code.

### Step 2 — Inspect

For each OWASP category in the checklist:

1. Search and read relevant files (grep for `{!!`, `DB::raw`, `Str::markdown`, `MarkdownHelper::toHtml`, import/export, etc.)
2. Cross-check existing Pest tests under `tests/Feature/` when they cover the control
3. Mark each control **Pass**, **Fail**, or **N/A** with a one-line note

Do not report latent hardening gaps as High unless there is a reachable exploit path.

### Step 3 — Severity

| Severity | Use when |
|----------|----------|
| **Critical** | Exploitable without privileged access; full account takeover, data breach, or RCE |
| **High** | Exploitable with admin session or leaves trivially guessable credentials in non-local deploy |
| *(omit)* | Pass, N/A, or latent gap with no current endpoint |

### Step 4 — Report

Produce markdown using [references/report-template.md](references/report-template.md).

Default output file: `SECURITY-AUDIT.md` at repository root. Update the **Date** and branch note in the footer.

### Step 5 — Handoff

After saving the report, give the user:

1. One-paragraph executive summary (Critical/High count)
2. Table of findings only if any Critical/High exist
3. Offer to fix findings only if the user asks — do not patch unprompted

## Blogr-specific signals

Quick grep targets (adapt as needed):

```bash
rg -n "\{!!" resources/views/            # Unescaped Blade output — XSS in blocks, SEO, Markdown
rg -n "DB::raw|whereRaw|selectRaw" src/  # Raw SQL queries
rg -n "Str::markdown" src/ resources/    # Unsanitized Markdown rendering
rg -n "MarkdownHelper::toHtml" src/ resources/  # Markdown via helper — XSS vector
rg -n "is_admin|is_editor" src/          # Admin guard checks
rg -n "validated\(\)|fillable|guarded" src/  # Mass-assignment protection
rg -n "BlogrImportService|BlogrExportService" src/  # Import/export — deserialization risk
rg -n "ExtensionRegistry|VideoEmbedAdapter" src/  # Extension system — code injection surface
rg -n "RateLimiter|throttle" src/        # Rate limiting on import/export, public routes
rg -n "password|Hash::" database/seeders/  # Seeder credential patterns
rg -n "exec|shell_exec|eval|passthru|system" src/  # Command injection surface
rg -n "DB::transaction|DB::beginTransaction" src/Services/  # Import/export atomicity
```

## Additional resources

- Detailed per-category checks: [references/checklist.md](references/checklist.md)
- Report structure: [references/report-template.md](references/report-template.md)
- Prior audit baseline: [SECURITY-AUDIT.md](../../SECURITY-AUDIT.md)
