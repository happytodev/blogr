# OWASP Critical Checklist — Blogr

Five OWASP Top 10 (2021) categories most relevant to this package. Each item expects static code review evidence.

---

## A01:2021 — Broken Access Control

| # | Check | Where to look | Pass criteria |
|---|-------|---------------|---------------|
| A01-1 | Filament admin routes gated | `src/BlogrServiceProvider.php`, `src/Filament/`, `src/Http/Middleware/` | All admin routes use Filament panel auth (no public access to `/admin/*`) |
| A01-2 | Form/request authorization | `src/Filament/Forms/`, `src/Http/Requests/` (if any) | `authorize()` denies non-admins |
| A01-3 | No horizontal IDOR on posts | `src/Policies/BlogPostPolicy.php` | `getEloquentQuery()` scopes per-user for editors; admin sees all |
| A01-4 | Draft / unpublished leakage | `src/Http/Controllers/BlogController.php` | Unpublished or future-dated posts return 404 on public route |
| A01-5 | Profile / settings scope | `src/Filament/Pages/`, `src/Filament/Resources/` | Non-admin cannot access admin-only settings |
| A01-6 | Mass-assignment escalation | `src/Models/BlogPost.php`, `src/Models/User.php` | `is_admin`, `is_editor`, `user_id` not settable from client input |
| A01-7 | Import/Export access control | `src/Services/BlogrImportService.php`, `src/Services/BlogrExportService.php` | Import and export commands require Filament admin auth |

---

## A02:2021 — Cryptographic Failures

| # | Check | Where to look | Pass criteria |
|---|-------|---------------|---------------|
| A02-1 | Password storage | `src/Models/User.php` | Passwords hashed via Laravel cast / `Hash::make`, never logged |
| A02-2 | Secrets out of VCS | `.gitignore`, `.env.example` | `.env` gitignored; example file has no real secrets |
| A02-3 | Sensitive data in responses | `resources/views/`, API responses | No password hashes or tokens echoed to clients |
| A02-4 | API keys for translation services | `config/blogr.php`, `src/Services/Translation/` | Translation API keys not hardcoded; read from env |

---

## A03:2021 — Injection

| # | Check | Where to look | Pass criteria |
|---|-------|---------------|---------------|
| A03-1 | SQL injection | `src/Http/Controllers/`, `src/Models/`, raw queries | Eloquent / query builder only; no unsanitized user input in `DB::raw` |
| A03-2 | Stored XSS — block rendering | `resources/views/blog/show.blade.php`, `resources/views/components/blocks/content.blade.php` | Block content escaped or sanitized; `{!! !!}` only used with trusted HTML sources |
| A03-3 | Stored XSS — Markdown rendering | `src/Helpers/MarkdownHelper.php`, `resources/views/components/blocks/content.blade.php` | `Str::markdown()` or `MarkdownHelper::toHtml()` strips scripts and unsafe HTML |
| A03-4 | Stored XSS — author bio | `resources/views/components/author-bio.blade.php`, `src/Helpers/MarkdownHelper.php` | Bio Markdown rendered via `{!! !!}` uses safe Markdown parser |
| A03-5 | Stored XSS — SEO / JSON-LD | `resources/views/layouts/blog.blade.php`, `src/Helpers/SEOHelper.php` | `SEOHelper::generateJsonLd()` escapes or validates input |
| A03-6 | Reflected XSS | Search/forms, error pages | Blade `{{ }}` escaping on user-derived output |
| A03-7 | Command / code injection | `exec`, `shell_exec`, `eval`, `passthru`, `system` | None with user-controlled input |
| A03-8 | Import deserialization | `src/Services/BlogrImportService.php` | Imported data validated; no unsafe `unserialize()` or file inclusion |
| A03-9 | Extension adapter injection | `src/Extensions/ExtensionRegistry.php`, `src/Extensions/VideoEmbedAdapter.php` | Extensions validated; no dynamic class loading from user input |
| A03-10 | CRUD input validation | `src/Filament/Resources/BlogPostResource/`, model `$fillable` / `$guarded` | Only validated fields persisted; sensitive fields set server-side |

---

## A05:2021 — Security Misconfiguration

| # | Check | Where to look | Pass criteria |
|---|-------|---------------|---------------|
| A05-1 | Debug mode default | `.env.example`, `INSTALL.md` | Production must set `APP_DEBUG=false` |
| A05-2 | Database file exposure | `config/database.php`, `public/` | SQLite under `database/`, not web-served |
| A05-3 | Default / seeded credentials | `database/seeders/` | Seeders gated to `local`/`testing` or env-driven passwords |
| A05-4 | Directory listing / backup leaks | `public/`, repo root | No `.env`, `.sqlite`, or dumps in `public/` or tracked by git |
| A05-5 | CSRF on state-changing routes | Filament forms, import/export endpoints | Filament CSRF protection active; web middleware stack present |
| A05-6 | Config exposure | `config/blogr.php` | No secrets in config file; all sensitive values from env |

---

## A07:2021 — Identification and Authentication Failures

| # | Check | Where to look | Pass criteria |
|---|-------|---------------|---------------|
| A07-1 | Brute-force protection on public routes | `src/Http/Controllers/`, `src/Http/Middleware/` | Rate limiting on search, contact form, or other public endpoints |
| A07-2 | Weak known accounts | `database/seeders/`, `INSTALL.md` | No documented default `password` in non-local paths |
| A07-3 | Filament session security | `config/filament.php` (host app) | Session cookie `http_only` and `secure` flags set properly |

---

## Tests to cross-check

When present, confirm coverage aligns with findings:

- `tests/Feature/BlogPostPermissionsTest.php` — post CRUD access control
- `tests/Feature/UserPolicyTest.php` — user model authorization
- `tests/Feature/HeroBlockRenderingTest.php` — block output hardening
- `tests/Feature/BlogrImportCommandTest.php` — import validation
- `tests/Feature/BlogrExportCommandTest.php` — export access control
- `tests/Feature/DemoAdminUserRoleTest.php` — seeder environment guard
- `tests/Feature/FilamentUserAdminRoleIntegrationTest.php` — Filament auth guard
- `tests/Feature/SitemapTest.php`, `tests/Feature/RssFeedTest.php` — public routes no auth required
- `tests/Feature/AuthorBioOnArticleTest.php` — author bio rendering safety

Failed tests or missing coverage do not by themselves constitute a High finding unless they reveal an exploitable gap.
