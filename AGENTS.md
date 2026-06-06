# Blogr AGENTS.md

## Project

FilamentPHP v4 plugin package (`happytodev/blogr`) — a multilingual blog system for Laravel 12+.

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

## Config duplicates

The `config/blogr.php` has duplicate keys (`locales`, `cms`, `posts` are defined twice). The first occurrence takes effect for config loaded before boot; the second takes effect in some runtime paths. Be aware when reading config values.

## CI

- Runs on `ubuntu-latest`, PHP 8.4, Laravel 12.*, `prefer-stable`.
- Installs Playwright browsers before tests.
- Test command: `vendor/bin/pest --parallel --ci`.
