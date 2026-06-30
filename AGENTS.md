# Blogr AGENTS.md

## ⚠️ Issue creation — MANDATORY

**Every user request for a bug fix or new feature MUST trigger a GitHub
issue before any code is written or proposed.** This ensures traceability.

- User says "there is a bug" → create issue with `--label bug`
- User says "I need a feature" → create issue with `--label feature`
- The issue is created via `gh issue create` immediately upon understanding
  the need
- The issue number is referenced in all subsequent commits and PRs
- The issue is closed when the work is merged into `main`
- Skipping this is a process error

## ⚠️ Commit policy — ZERO TOLERANCE

**NEVER commit, amend, tag, or push unless the user explicitly loads the `release-manager` skill and requests a release.** This includes bug fixes, hotfixes, and any other changes. All commits must go through the `release-manager` workflow. Violating this rule is a process error.

## ⚠️ TDD requirement — ZERO TOLERANCE

**Every bug fix and every feature addition MUST be driven by tests written first (TDD).** Before writing implementation code, write the test that proves the bug exists or the feature works. Run the test to confirm it fails (RED), implement the fix/feature (GREEN), then run the test again to confirm it passes.

This applies to:
- **Bug fixes**: Write a test that reproduces the bug (fails before fix, passes after)
- **New features**: Write tests covering the expected behavior before implementing
- **Admin UI changes**: At minimum, add a syntax check test that catches parse errors

### Naming convention

- **Bug regression tests**: `regression_<issue_number>_<description>` (e.g. `regression_42_save_button_does_nothing`)
- **Feature tests**: `feature_<description>` (e.g. `feature_view_counter_increments_on_show`)

### RED phase (mandatory before any implementation)

1. Write the test that proves the bug exists or validates the expected feature behavior
2. Run `vendor/bin/pest --filter <test_name>` — confirm it **fails** (RED)
3. This proves the test detects the problem

### GREEN phase

1. Implement the fix or feature
2. Run `vendor/bin/pest --filter <test_name>` — confirm it **passes** (GREEN)
3. **Anti-false-positive gate**: Comment out the new implementation code and re-run the test — it must fail again. If it still passes, the test is a false positive and must be rewritten

### Regression test commitment

A regression test becomes part of the **permanent test suite**. It runs on every `vendor/bin/pest --parallel` execution and on CI. Any future re-introduction of the bug will be caught immediately.

### Commit message convention

- **Bug fixes**: Commit body MUST include `Regression test: #<issue_number>`
- **New features**: Commit body MUST include `Feature test: <description>`

Without a matching test and without this convention, the change is not complete.

## Project

FilamentPHP v4 plugin package (`happytodev/blogr`) — a multilingual blog system for Laravel 12+.

## Resources

| File | Content |
|------|---------|
| [README.md](README.md) | Installation, prerequisites, basic commands |
| [docs/](docs/) | Feature documentation (CMS, gradients, translations, RGPD, etc.) |
| [artist-portfolio-audit](.opencode/skills/artist-portfolio-audit/SKILL.md) | Variance analysis for the illustrator’s portfolio website |

## Shell Environment

PHP/Composer binaries are installed via Homebrew at `/opt/homebrew/bin/`.
Node/npm is installed via nvm at `~/.nvm/versions/node/*/bin/`.
GitHub CLI (`gh`) is also at `/opt/homebrew/bin/gh`.
The opencode shell may not include these directories in `PATH` automatically.
Always ensure it is set before running any PHP, Composer, or Node commands:

```bash
export PATH="/opt/homebrew/bin:$HOME/.nvm/versions/node/$(ls $HOME/.nvm/versions/node/)/bin:$PATH"
```

Or use the full path directly (e.g. `/opt/homebrew/bin/php vendor/bin/pest`).

Alternatively, prefix commands with the full path (e.g. `/opt/homebrew/bin/php vendor/bin/pest`).

If the PHP runtime has Xdebug enabled without coverage mode (`xdebug.mode=develop`),
Pest will warn about missing coverage and may bail out (due to `failOnWarning` in
`phpunit.xml.dist`). Set `XDEBUG_MODE=coverage` before running tests:

```bash
export PATH="/opt/homebrew/bin:$PATH" && XDEBUG_MODE=coverage vendor/bin/pest --parallel
```

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
- **Batch grouping**: When the working tree contains multiple coherent changes (e.g. a feature plus its associated bug fixes), `git-changelog-workflow` will propose to group them into a single PR with atomic commits per domain, instead of splitting into separate branches. This avoids PR proliferation for closely related work.
- **Finishing work**: Follow the [git-changelog-workflow](.opencode/skills/git-changelog-workflow/SKILL.md) skill (analysis, CHANGELOG proposal, atomic commits, PR).
- **Post-merge fixes**: If `main` has advanced, use `git cherry-pick` from the fix branch — see the [hotfix-cherry-pick](.opencode/skills/hotfix-cherry-pick/SKILL.md) skill.
- **Project quality**: After each sprint, improve the quality harness via [harness-evolution](.opencode/skills/harness-evolution/SKILL.md) (tests, PHPStan, lint JS, coverage, CI, hooks).
- **Dependencies**: For safe updates with security guardrails, use [safe-dependency-update](.opencode/skills/safe-dependency-update/SKILL.md) (72h filter, patch/minor/major, human validation).

## Workflow cycle — feature/bug to release

```
User command              Skill              Action
─────────────────────────────────────────────────────────────────────
"fix this bug"        →  issue-to-mr        Issue → analysis → TDD
"commit changes"      →  git-changelog      Batch analysis → branch
                                            → CHANGELOG → commits
                                            → PR → [optional merge]
"release"             →  release-manager    Pre-flight (all PRs merged?)
                                            → version bump → tag
                                            → GitHub Release
```

### Step-by-step user commands

| Phase | User says | What happens |
|-------|-----------|-------------|
| **Bug / feature** | `"there is a bug: …"` or `"I need a feature: …"` | `issue-to-mr` loads: creates GitHub issue, analyzes code, develops fix/feature with TDD, calls `git-changelog-workflow` |
| **Commit** | `"commit changes"` | `git-changelog-workflow` loads: batch analysis (group or split), branch from `main`, CHANGELOG proposal, atomic commits, PR creation, optional merge |
| **Merge** | `"merge the PR"` (optional) | `git-changelog-workflow` step 6: `gh pr merge --squash` |
| **Release** | `"release"` or `"cut a release"` or `"tag vX.Y.Z"` | `release-manager` loads: pre-flight check (all PRs merged?), version bump, CHANGELOG, tag, GitHub Release |

### Merge strategies

- **Merge immediately**: After `git-changelog-workflow` creates the PR, say **"merge the PR"**. The PR is squash-merged and `main` is updated.
- **Batch before release**: Skip merge, accumulate multiple PRs, then say **"release"**. The `release-manager` will list all open PRs and ask you to merge them before proceeding.
- **Hybrid**: Merge some PRs immediately, let others wait. The `release-manager` pre-flight check only blocks if unmerged PRs exist.

### Pre-flight gate

The `release-manager` will **not** cut a release while PRs are still open
against `main`. This prevents accidental releases with unmerged work.
Always merge all intended PRs before running `release`.

## CI

- Runs on `ubuntu-latest`, PHP 8.4, Laravel 12.*, `prefer-stable`.
- Installs Playwright browsers before tests.
- Test command: `vendor/bin/pest --parallel --ci`.

## Local Dev

See [README.md](README.md) for installation instructions and prerequisites. The package uses Testbench for local development — `composer serve` starts the workbench dev server.
