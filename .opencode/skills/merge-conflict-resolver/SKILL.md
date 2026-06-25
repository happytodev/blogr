---
name: merge-conflict-resolver
description: >-
  Detects and resolves Git merge conflicts typical of a Laravel package project
  with parallel feature branches. Covers conflicts on service providers,
  Filament Schema components, Translation-First models, and config files.
compatibility: Git, shell access, Blogr package with parallel feature branches.
metadata:
  author: happytodev
  version: "1.0"
---

# Merge conflict resolution for Blogr

## When to use

- `git merge` or `git cherry-pick` reports a conflict
- Multiple PRs modify the same files in an additive way
- The user asks to resolve merge conflicts

## Generic workflow

```bash
git fetch origin
git checkout ma-branche
git merge origin/main
# → CONFLICT detected
```

For each conflicted file:

1. Identify `<<<<<<<`, `=======`, `>>>>>>>` blocks
2. Determine if the conflict is additive (both versions must coexist)
3. Resolve, add, continue

## Conflict patterns by file

### BlogrServiceProvider.php — Additive boot() calls

**Symptom:** Multiple branches add calls in `packageBooted()`.

**Resolution:** Keep all calls in alphabetical order.

```php
<<<<<<< HEAD
$schema->observe(BlogPost::class, BlogPostObserver::class);
=======
$schema->gate()->policy(Series::class, SeriesPolicy::class);
>>>>>>> main
```

→

```php
$schema->gate()->policy(Series::class, SeriesPolicy::class);
$schema->observe(BlogPost::class, BlogPostObserver::class);
```

### config/blogr.php — Additive config keys

**Symptom:** Two branches add different config keys (remember the duplicate key quirk).

**Resolution:** Keep both sets of keys. Respect the first-occurrence semantics for the duplicate sections.

```php
<<<<<<< HEAD
'feeds' => [
    'enabled' => true,
],
=======
'comments' => [
    'enabled' => false,
],
>>>>>>> main
```

→

```php
'comments' => [
    'enabled' => false,
],
'feeds' => [
    'enabled' => true,
],
```

### BlogPostForm.php / BlogCategoryForm.php — Schema components

**Symptom:** Multiple branches add different Schema sections or fields.

**Resolution:** Keep all Sections and fields. Import any missing Filament Schema classes.

```php
<<<<<<< HEAD
Section::make('SEO')->schema([
    TextInput::make('meta_title'),
]),
=======
Section::make('Publishing')->schema([
    Toggle::make('is_published'),
]),
>>>>>>> main
```

→

```php
Section::make('Publishing')->schema([
    Toggle::make('is_published'),
]),
Section::make('SEO')->schema([
    TextInput::make('meta_title'),
]),
```

### Translation model (e.g. BlogPostTranslation.php) — Additive methods

**Symptom:** Multiple branches add different scopes or accessors to the same translation model.

**Resolution:** Keep all methods. Add missing `use` imports.

### Migration files — Additive schema changes

**Symptom:** Two branches each add a column to the same translation table.

**Resolution:** Keep both columns. Reorder chronologically if timestamps conflict.

```php
<<<<<<< HEAD
$table->string('meta_description')->nullable();
=======
$table->boolean('featured')->default(false);
>>>>>>> main
```

→

```php
$table->boolean('featured')->default(false);
$table->string('meta_description')->nullable();
```

### tests/ — Conflicting test files

**Symptom:** Two branches add different test methods or test files.

**Resolution:** Keep all tests. Ensure each Feature test file has the correct `uses()` declaration.

## Verification after resolution

```bash
# No conflict markers remain
git grep -l "<<<<<<< HEAD"

# Run the full suite
vendor/bin/pest --parallel
```
