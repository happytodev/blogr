# Plan: Fusion AGENTS.md

## Changement à appliquer

Réécrire `AGENTS.md` avec la structure fusionnée ci-dessous.

## Contenu final proposé

```markdown
# Blogr AGENTS.md

## ⚠️ Commit policy — ZERO TOLERANCE

**NEVER commit, amend, tag, or push unless the user explicitly loads the `release-manager` skill and requests a release.** This includes bug fixes, hotfixes, and any other changes. All commits must go through the `release-manager` workflow. Violating this rule is a process error.

## ⚠️ TDD requirement — ZERO TOLERANCE

**Every bug fix and every feature addition MUST be driven by tests written first (TDD).** Before writing implementation code, write the test that proves the bug exists or the feature works. Run the test to confirm it fails, implement the fix/feature, then run the test again to confirm it passes.

This applies to:
- **Bug fixes**: Write a test that reproduces the bug (fails before fix, passes after)
- **New features**: Write tests covering the expected behavior before implementing
- **Admin UI changes**: At minimum, add a syntax check test that catches parse errors

Without a matching test, the change is not complete.

## Project

FilamentPHP v4 plugin package (`happytodev/blogr`) — a multilingual blog system for Laravel 12+.

## Resources

| File | Content |
|------|---------|
| [README.md](README.md) | Installation, prerequisites, basic commands |
| [docs/](docs/) | Feature documentation (CMS, gradients, translations, RGPD, etc.) |

## Stack

- PHP 8.3+, Laravel 12.x, FilamentPHP v4, Pest PHP 4.0, Tailwind CSS 4, Vite
- Testbench 10.x, in-memory SQLite, Spatie Package Tools + Spatie Permission
- Playwright for browser tests (Chromium only — `npx playwright install --with-deps`)

## Commands

```bash
# Tests (always use --parallel for full suite)
vendor/bin/pest --parallel
vendor/bin/pest --parallel --ci                # CI mode
vendor/bin/pest tests/Feature/SpecificTest.php
vendor/bin/pest --filter "test description"

# Dev
npm run build                                   # Build CSS/JS via Vite
composer test                                   # = vendor/bin/pest
composer serve                                  # Start testbench dev server
```

## Code Conventions

- **Validation**: Use `$request->validated()`. Never trust client-provided sensitive fields (`user_id`, `is_admin`).
- **Blade**: `{!! !!}` only for pre-sanitized HTML (e.g. block content rendered through the CMS). Always prefer `{{ }}` (escaped output).
- **Translations**: Always use the Translation-First pattern — store translatable content in translation models, not main tables.
- **Models**: Always eager-load translations with `Model::with('translations')`. Never use `Model::all()` alone.
- **Services**: Import/Export services (`BlogrImportService`, `BlogrExportService`) must be wrapped in transactions with `DB::table()->insert()` for ID preservation.

## Testing quirks

- **Feature tests declare `uses()` individually** (Pest.php only covers Unit, Arch, Browser). Each Feature test file must start with `uses(Happytodev\Blogr\Tests\TestCase::class)` or the appropriate variant.
- **Test base classes**:
  - `TestCase` — standard (locales disabled). Uses `Happytodev\Blogr\Models\User`.
  - `LocalizedTestCase` — locales enabled, uses `Workbench\App\Models\User`.
  - `CmsTestCase` — CMS + homepage enabled, extends TestCase.
  - `CmsWithLocalesTestCase`, `CmsWithPrefixTestCase`, `LocalizedCmsTestCase` — finer combos.
- Tests in `tests/Localized/` use `LocalizedTestCase` automatically via Pest.php.
- Architecture tests in `tests/ArchTest.php`: forbids `dd()`, `dump()`, `ray()`.
- `phpunit.xml.dist` uses **random execution order** and in-memory SQLite.
- `post-autoload-dump` runs `remove-orchestra-permission-migration` — expect file removals on `composer install/update`.

## Architecture: Translation-First

- **Main tables** store only non-translatable fields (IDs, timestamps, user_id). **Translation tables** hold title, slug, content, SEO, photos.
- **Unique constraints**: `[entity_id, locale]` per translation; `[locale, slug]` for categories/tags; `slug` globally unique for posts.
- **Pivot tables**: `blog_post_translation_category`, `blog_post_translation_tag` — link translations, not main entities.
- Always use `Model::with('translations')` — never `Model::all()` alone.
- Tags automatically sort alphabetically via `getTagsAttribute()` accessor.
- Import/Export services (`BlogrImportService`, `BlogrExportService`) wrapped in transactions with `DB::table()->insert()` for ID preservation.

## Filament v4 gotchas

- **`Schema`** (not `Form`): `Filament\Schemas\Schema`, `Filament\Schemas\Components\Section` (NOT `Filament\Forms\Components\Section`).
- **Navigation**: Use methods (`getNavigationIcon()`, `getNavigationGroup()`), not static properties.
- **Translations UI**: Use `Repeater::make('translations')->relationship()` — NOT the `Tabs\Tab` pattern.
- **Resources**: Delegate form/table to separate classes (`BlogPostForm::getFormSchema()`, `BlogPostTable::getTableSchema()`).

## CMS & Routes

- CMS migrations (`cms_pages`, `cms_page_translations`) are **conditionally loaded** based on `config('blogr.cms.enabled')`.
- Routes are registered by `BlogrServiceProvider::packageBooted()` — not in a routes file.
- Localized routes use `SetLocale` middleware. Route pattern is nested directly (no prefix groups) to avoid Laravel parameter binding bugs.
- CMS uses anti-collision regex against reserved slugs (blog, feed, author, category, tag, series, admin, etc.)

## Security Notes

- **No public registration**: User management is handled through the Filament admin panel. The package does not expose registration routes.
- **Seeders**: Admin seeders are for local/testing dev only. Never hard-code production credentials in seeders.
- **User content rendering**: Any user-provided HTML rendered through the block system must go through sanitization. Blocks are rendered via Blade components — avoid raw `{!! $blockContent !!}` patterns.
- **Rate limiting**: Apply `RateLimiter` on sensitive endpoints (contact forms, comment submissions if enabled).
- **Validation**: Always validate on the server side via Form Requests. Client-side validation is a UX enhancement, not a security control.

## Config duplicates

The `config/blogr.php` has duplicate keys (`locales`, `cms`, `posts` are defined twice). The first occurrence takes effect for config loaded before boot; the second takes effect in some runtime paths. Be aware when reading config values.

## ⚠️ CSS build requirement — ZERO TOLERANCE

**Before every release, run `npm run build` BEFORE `vendor/bin/pest --parallel`.** The CSS is compiled by Tailwind v4 via Vite and only includes classes detected in the source files at build time. If view files change without rebuilding, the dist CSS will be stale and Tailwind classes won't render in production.

This applies to:
- **Every release** — `npm run build` must be the first step of the release workflow
- **Any change to Blade files** in `resources/views/` that add or modify Tailwind classes
- **Any change to `resources/css/index.css`**

The built CSS (`resources/dist/blogr.css`) MUST be committed alongside view changes.

## Git & Branches

- **Feature or bugfix work**: Always work on a **dedicated branch** created from `main` — never commit directly to `main`.
- **Branch naming**: `{type}/{description-kebab-case}` (`feat/…`, `fix/…`, `docs/…`, `chore/…`, `skill/…`). One topic per branch, no mixing unrelated feat + fix.
- **Finishing work**: Follow the [git-changelog-workflow](.opencode/skills/git-changelog-workflow/SKILL.md) skill (analysis, CHANGELOG proposal, atomic commits, PR).
- **Post-merge fixes**: If `main` has advanced, use `git cherry-pick` from the fix branch — see the [hotfix-cherry-pick](.opencode/skills/hotfix-cherry-pick/SKILL.md) skill.
- **Project quality**: After each sprint, improve the quality harness via [harness-evolution](.opencode/skills/harness-evolution/SKILL.md) (tests, PHPStan, lint JS, coverage, CI, hooks).
- **Dependencies**: For safe updates with security guardrails, use [safe-dependency-update](.opencode/skills/safe-dependency-update/SKILL.md) (72h filter, patch/minor/major, human validation).

## CI

- Runs on `ubuntu-latest`, PHP 8.4, Laravel 12.*, `prefer-stable`.
- Installs Playwright browsers before tests.
- Test command: `vendor/bin/pest --parallel --ci`.

## Local Dev

See [README.md](README.md) for installation instructions and prerequisites. The package uses Testbench for local development — `composer serve` starts the workbench dev server.
```

## Résumé des changements

| Section | Action |
|---------|--------|
| Commit policy | Conservé |
| TDD requirement | Conservé |
| Project | Conservé |
| **Resources** | **Nouveau** — liens vers README.md et docs/ |
| Stack | Conservé |
| Commands | Conservé (pas de `composer quality` — n'existe pas dans Blogr) |
| **Code Conventions** | **Nouveau** — validation, Blade, traductions, modèles, services |
| Testing quirks | Conservé |
| Architecture | Conservé |
| Filament v4 gotchas | Conservé |
| CMS & Routes | Conservé |
| **Security Notes** | **Nouveau** — registration, seeders, sanitization, rate limiting |
| Config duplicates | Conservé |
| CSS build requirement | Conservé |
| **Git & Branches** | **Nouveau** — nommage, workflow, liens vers skills OpenCode |
| CI | Conservé |
| **Local Dev** | **Nouveau** — renvoi vers README.md |

## Contenu externe NON intégré

| Raison |
|--------|
| `SafeMarkdown`, `SafeComment`, `CommentController` — n'existent pas dans Blogr |
| `ArticlePolicy`, `UserPolicy` — Blogr utilise Filament authorization |
| Breeze auth routes — pas de Breeze dans un package |
| `php artisan` commands — Blogr utilise `vendor/bin/pest` |
| ESLint/Prettier — pas configuré dans Blogr |
| `.gitlab-ci.yml` — Blogr utilise GitHub Actions |
| `app/` paths — Blogr utilise `src/` |
| `composer lint/format/analyse/quality` — n'existe pas dans Blogr |
