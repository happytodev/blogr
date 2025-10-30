# GitHub Copilot Instructions for Blogr

## Project Overview
Blogr is a **FilamentPHP v4 plugin** adding a multilingual blog system to Laravel applications. Core stack: PHP 8.3+, Laravel 12, Pest PHP 4.0 (555+ tests). Package version: 0.12.5 (approaching RC1 - Oct 2025).

## Architecture: Translation-First Design

**Critical Pattern**: All content entities use separate translation tables.

### Database Schema Pattern
- **Main tables** (`blog_posts`, `blog_series`, `categories`, `tags`): Non-translatable fields only (IDs, timestamps, user_id, etc.)
- **Translation tables** (`*_translations`): All locale-specific content (title, slug, excerpt, content, seo fields, photos)
- **Unique constraints**:
  - All translations: `[entity_id, locale]` (one translation per locale per entity)
  - Categories/Tags: `[locale, slug]` (slug unique within locale)
  - Posts: `slug` globally unique (no locale prefix needed)
- **Pivot tables**: `blog_post_translation_category`, `blog_post_translation_tag` (link translations, not main entities)

### Model Relationships
```php
// Main model
public function translations() {
    return $this->hasMany(EntityTranslation::class);
}

// Always query through translations:
BlogPost::with('translations')->get(); // NOT BlogPost::all()
```

### FilamentPHP Integration
- **Resources**: Use `BlogPostForm::getFormSchema()` and `BlogPostTable::getTableSchema()` pattern (don't define forms/tables in Resource class)
- **RelationManagers**: All entities have `TranslationsRelationManager` for translation UI
- **Repeater Pattern**: Use Filament's `Repeater` component for inline translation editing
- **Authorization**: Check `user()->hasRole(['admin', 'writer'])` - admins can edit all, writers only their own posts

## Service Layer Architecture

### Import/Export Services (`src/Services/`)
**Pattern**: Transaction-wrapped, detailed logging, ID preservation during overwrite

#### BlogrExportService
```php
// Collects data + media files → creates ZIP with JSON + /media folder
collectMediaFiles() // Iterates BOTH main tables AND translation arrays
```

#### BlogrImportService
```php
// Key methods follow: validate → check existing → update/skip/create → log
importCategories()        // Check by ID first, then slug
importPostTranslations()  // Check [locale, slug] uniqueness (NOT [blog_post_id, locale])
importPosts()             // Validates category_id/blog_series_id exist, sets null if missing
```

**Logging Convention**: Prefix all logs with `BlogrImportService:` or `Blogr Import:`
```php
Log::info('BlogrImportService: Imported X categories');
```

## Testing Workflow (Pest PHP)

### Run Tests

MANUALLY run tests with: `./vendor/bin/pest --parallel` everytime it's possible.

```bash
./vendor/bin/pest --parallel                                   # All tests
./vendor/bin/pest tests/Feature/                    # Feature tests only
./vendor/bin/pest tests/Feature/BlogrImportCommandTest.php  # Single file
./vendor/bin/pest --filter "export includes translation photos"  # Specific test
```

### Test Structure
- `tests/Pest.php`: Base configuration using `TestCase` and `LocalizedTestCase`
- `tests/Feature/`: 555 tests covering import/export, CRUD, SEO, relationships
- `tests/ArchTest.php`: Architecture rules (no `dd()`, `dump()`, `ray()` in production code)
- **In-memory SQLite**: Tests use `:memory:` database (configured in `phpunit.xml.dist`)

### Writing Tests
```php
it('validates translation uniqueness', function () {
    // Setup
    $category = Category::factory()->create();
    $category->translations()->create([
        'locale' => 'en',
        'slug' => 'existing-slug',
        'name' => 'Test'
    ]);
    
    // Expect unique constraint violation
    expect(fn() => $category->translations()->create([
        'locale' => 'en',
        'slug' => 'existing-slug', // Duplicate!
        'name' => 'Test 2'
    ]))->toThrow(\Exception::class);
});
```

## Key Files Reference

### Core Services
- `src/Services/BlogrExportService.php` - Export logic, media collection
- `src/Services/BlogrImportService.php` - Import logic, validation, ID preservation

### Models & Relationships
- `src/Models/BlogPost.php` - Main post model with `translations()` relation
- `src/Models/BlogPostTranslation.php` - Translation model with `[locale, slug]` unique constraint
- `src/Models/Category.php`, `Tag.php`, `BlogSeries.php` - Follow same pattern

### FilamentPHP Resources
- `src/Filament/Resources/BlogPostResource.php` - Resource definition with authorization
- `src/Filament/Resources/BlogPosts/BlogPostForm.php` - Form schema
- `src/Filament/Resources/BlogPosts/BlogPostTable.php` - Table schema
- `src/Filament/Resources/BlogPostResource/RelationManagers/TranslationsRelationManager.php` - Translation UI

### Migrations
- `database/migrations/*_create_blog_posts_table.php` - Main table (no content fields)
- `database/migrations/*_create_blog_post_translations_table.php` - Translation table with unique constraints

### Commands
- `src/Commands/BlogrExportCommand.php` - `php artisan blogr:export`
- `src/Commands/BlogrImportCommand.php` - `php artisan blogr:import`

## Debugging Workflow

### View Import/Export Logs
```bash
tail -f storage/logs/laravel.log | grep "Blogr"
```

### Check Database State
```bash
php artisan tinker
>>> BlogPost::with('translations')->count()
>>> Category::with('translations')->get()
```

### Run Specific Test with Debugging
```php
// In test file, use dd() temporarily
it('debugs import issue', function () {
    dd($exportData); // Will halt and display data
})->only(); // Run only this test
```

## Common Patterns

### Creating Entities with Translations
```php
// CORRECT: Create main entity first, then translations
$post = BlogPost::create(['user_id' => 1, 'blog_series_id' => null]);
$post->translations()->create([
    'locale' => 'en',
    'title' => 'My Post',
    'slug' => 'my-post',
    'content' => 'Content here'
]);

// INCORRECT: Don't try to create with nested translations
BlogPost::create(['translations' => [...]]);  // Won't work!
```

### Checking Translation Existence Before Import
```php
// Check by locale + slug (for categories/tags)
$existing = CategoryTranslation::where('locale', $locale)
    ->where('slug', $slug)
    ->first();

// Check by entity_id + locale
$existing = $entity->translations()
    ->where('locale', $locale)
    ->first();
```

### Foreign Key Validation Pattern
```php
// Always validate FKs exist before assigning
if ($data['category_id'] && !Category::find($data['category_id'])) {
    Log::warning("Category ID {$data['category_id']} not found, setting to null");
    $data['category_id'] = null;
}
```

## Development Principles

1. **TDD First**: Write test before implementation (555 tests, 1641 assertions maintained)
2. **Translation-First**: Always think "main entity + translations", never store content in main tables
3. **Foreign Key Safety**: Validate all FK references exist before assignment, set null if missing
4. **ID Preservation**: During overwrite, use `DB::table()->insert()` to preserve original IDs
5. **Detailed Logging**: Log all import/export operations with clear prefixes
6. **Filament Authorization**: Check user roles at Resource level (`canEdit()`, `canDelete()`)

## Common Gotchas

- ❌ **Don't** query `BlogPost::all()` - translations won't load
- ✅ **Do** use `BlogPost::with('translations')->get()`
- ❌ **Don't** check `[blog_post_id, locale]` for translation uniqueness
- ✅ **Do** check `[locale, slug]` for categories/tags uniqueness
- ❌ **Don't** forget to include translation photos in media exports
- ✅ **Do** iterate both main arrays AND `*_translations` arrays in `collectMediaFiles()`
- ❌ **Don't** use `Model::create()` to preserve IDs during import
- ✅ **Do** use `DB::table('table_name')->insert()` to preserve original IDs

## Documentation
- `docs/IMPORT_DEBUGGING.md` - Detailed import/export troubleshooting
- `docs/QUICK_CATEGORY_CREATION.md` - Feature documentation pattern (includes tests)
- `README.md` - User-facing features and installation
- `CHANGELOG.md` - Version history and breaking changes
