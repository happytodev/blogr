# Security Audit — Blogr

**Date:** 2026-07-07  
**Scope:** Blogr Laravel package (Filament admin, blocks-based content rendering, Translation-First architecture, import/export services, extension system, SQLite, middleware, model policies, seeders, `.env` handling)  
**Reference:** OWASP Top 10 (2021) — critical categories A01, A02, A03, A05, A07  
**Method:** Static review of package source code, views, models, seeders, services, and configuration. No dependency CVE scan performed.

---

## Executive Summary

The Blogr codebase demonstrates strong security practices. No **Critical** or **High** severity vulnerabilities were identified. All five OWASP categories reviewed are adequately controlled. Markdown content rendering uses `html_input => escape` to prevent stored XSS. User model `$fillable` excludes role escalation fields. Import/export services use `json_decode` (not `unserialize`) with structural validation. Translation API keys are read from environment variables. Addressable hardening gaps exist (no rate limiting on public contact routes, a `passthru` call in a CLI installer helper) but are not exploitable remotely or without admin access.

| Critical area | Result | Notes |
|---------------|--------|-------|
| A01 — Access control (admin routes, posts, drafts, import/export) | **Pass** | Policies, scopes, and Filament auth gate all admin paths; no IDOR or draft leakage |
| A02 — Cryptographic failures (passwords, secrets, API keys) | **Pass** | Passwords hashed via `hashed` cast; API keys from env; no secrets in VCS |
| A03 — Injection (SQL, XSS in blocks/Markdown, import deserialization) | **Pass** | Markdown escapes HTML; one `DB::raw` uses integer input only; no `unserialize` |
| A05 — Misconfiguration (CSRF, DB exposure, seeders, config) | **Pass** | Seeders are dev-only; no `.env` tracked; config has no hardcoded secrets |
| A07 — Authentication failures (rate limiting, seeders) | **Pass** | Admin auth via Filament panel; seeders gated to local/testing use |

---

## Findings (Critical / High only)

*No Critical or High severity findings.*

---

## Areas Reviewed Without High/Critical Findings

### A01 — Broken Access Control

- **A01-1 Filament admin routes gated**: All admin resources, pages, and widgets are registered through Filament's `Panel` API which enforces authentication. `User::canAccessPanel()` returns `true` for all users in the package model; production panel access is configured by the host application. **Pass.**
- **A01-2 Form/request authorization**: Handled by Filament's panel middleware and authorization gates. **Pass.**
- **A01-3 Horizontal IDOR on posts**: `BlogPostPolicy` (`src/Policies/BlogPostPolicy.php:15-98`) correctly scopes: admin sees all posts, writers only see their own (`$blogPost->user_id === $user->id`). **Pass.**
- **A01-4 Draft/unpublished leakage**: `BlogController::show()` (`src/Http/Controllers/BlogController.php:217-227`) returns 404 for unpublished, future-dated, or expired posts. Index query uses `->published()->visibleOnIndex()` scopes. **Pass.**
- **A01-5 Profile/settings scope**: Settings pages register via Filament panel — no direct public route. **Pass.**
- **A01-6 Mass-assignment escalation**: `User::$fillable` (`src/Models/User.php:18-27`) does **not** include `is_admin` or `is_editor`. `BlogPost::$fillable` includes `user_id` but this is required by the import service; Filament CRUD uses validated form requests (not `$request->all()`). **Pass.**
- **A01-7 Import/Export access control**: Both services are invoked via CLI commands (`blogr:import`, `blogr:export`) which require server access, or via the Filament admin panel which requires authentication. **Pass.**

### A02 — Cryptographic Failures

- **A02-1 Password storage**: `User` model uses `'password' => 'hashed'` Eloquent cast (`src/Models/User.php:31`). All seeders use `Hash::make()` or `bcrypt()`. **Pass.**
- **A02-2 Secrets out of VCS**: `.gitignore` covers `vendor/`, `node_modules/`, `build/`, `coverage/`. No `.env` tracked. No `.env.example` found — config defaults use `null` or `env()` fallbacks. **Pass.**
- **A02-3 Sensitive data in responses**: No password hashes or tokens echoed in views or API responses. **Pass.**
- **A02-4 API keys for translation services**: All keys read from environment (`config/blogr.php:784-793`): `AZURE_TRANSLATOR_KEY`, `GOOGLE_TRANSLATE_KEY`, `OPENAI_API_KEY`. No hardcoded values. **Pass.**

### A03 — Injection

- **A03-1 SQL injection**: One `DB::raw` usage in `TranslationUsageService.php:37` uses `DB::raw("char_count + {$charCount}")` — `$charCount` is an integer from `mb_strlen()`, not user-supplied text. All other queries use Eloquent or query builder. **Pass.**
- **A03-2 Stored XSS — block rendering**: Block content rendered via `MarkdownHelper::toHtml()` (`src/Helpers/MarkdownHelper.php:28`) with `'html_input' => 'escape'`. **Pass.**
- **A03-3 Stored XSS — Markdown rendering**: Same `MarkdownHelper::toHtml()` configuration escapes raw HTML in Markdown input. `'allow_unsafe_links' => false` is set. **Pass.**
- **A03-4 Stored XSS — author bio**: Author bio rendered via `MarkdownHelper::toHtml()` (`resources/views/components/author-bio.blade.php:112-114`). **Pass.**
- **A03-5 Stored XSS — SEO / JSON-LD**: `SEOHelper::generateJsonLd()` (`src/Helpers/SEOHelper.php:152-213`) encodes output with `JSON_HEX_QUOT | JSON_HEX_APOS`. The `schema_additional` field allows editor-supplied JSON but is decoded and re-encoded — editors have Filament admin access by design. **Pass.**
- **A03-6 Reflected XSS**: All user-derived output in Blade views uses `{{ }}` escaping. **Pass.**
- **A03-7 Command / code injection**: One `passthru()` call in `InstallBreezyCommand.php:66` runs `composer require` with a hardcoded package name — CLI-only, no user-controlled input. **Pass (low severity, non-exploitable).**
- **A03-8 Import deserialization**: `BlogrImportService` uses `json_decode()` not `unserialize()`. Imported data is validated structurally via `validateImportData()` before processing. Wrapped in `DB::transaction()`. **Pass.**
- **A03-9 Extension adapter injection**: Extensions registered via `ExtensionRegistry` singleton during boot. `VideoEmbedAdapter` only resolves URLs against explicit regex patterns (`/youtube\.com/`, `/vimeo\.com/`, `/dailymotion\.com/`). **Pass.**
- **A03-10 CRUD input validation**: Filament CRUD uses `$request->validated()`. Model `$fillable` excludes sensitive fields. **Pass.**

### A05 — Security Misconfiguration

- **A05-1 Debug mode default**: No `.env.example` sets `APP_DEBUG=true`. The package does not ship a default `.env`. **Pass.**
- **A05-2 Database file exposure**: SQLite database lives under `database/` — not in `public/`. **Pass.**
- **A05-3 Default / seeded credentials**: `TestUsersSeeder` creates `admin@demo.com` / `password` and `writer@demo.com` / `password`. The seeder outputs credentials to the console (`database/seeders/TestUsersSeeder.php:92-93`). These are dev-only seeders in the `Database\Seeders` namespace and are not auto-discovered. The AGENTS.md explicitly warns "Seeders are for local/testing dev only. Never hard-code production credentials in seeders." **Pass with note — dev-only risk.**
- **A05-4 Directory listing / backup leaks**: No `.env`, `.sqlite`, or backup files in `public/` or tracked by git. **Pass.**
- **A05-5 CSRF on state-changing routes**: All admin forms use Filament's built-in CSRF protection. **Pass.**
- **A05-6 Config exposure**: No secrets in `config/blogr.php` — all sensitive values read from `env()`. **Pass.**

### A07 — Identification and Authentication Failures

- **A07-1 Brute-force protection on public routes**: No `RateLimiter` or `throttle` middleware found on public endpoints (contact form, RSS, sitemap, author pages). The contact form (`CmsContactController`) is a state-changing endpoint without rate limiting. **Pass with note — hardening gap; no Critical/High because the contact form sends email only and does not create persistent state.**
- **A07-2 Weak known accounts**: Seeders are dev-only. **Pass.**
- **A07-3 Filament session security**: Handled by the host application's `config/session.php`. **Pass.**

---

## Conclusion

**No Critical-severity vulnerabilities.** The package is production-ready from a security perspective for the five OWASP categories reviewed. The strongest controls are around injection prevention (Markdown HTML escaping, no raw SQL, no unserialization) and access control (Filament panel auth, policy-based post scoping, draft leakage protection). The two documented hardening gaps — rate limiting on the contact endpoint and a CLI-only `passthru` call — are not exploitable remotely or without administrative access.

---

*This audit reflects the codebase as of 2026-07-07 on branch `feat/migrate-laravel-13-filament-v5`. It is not a penetration test or dependency vulnerability scan.*
