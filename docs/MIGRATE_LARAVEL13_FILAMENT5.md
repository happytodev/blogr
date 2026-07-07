# Migration Guide: Laravel 13 + FilamentPHP v5

Starting from **blogr v2.0.0**, the package requires **Laravel 13** and **FilamentPHP v5**.  
If your application is still on Laravel 12 / Filament v4, you must update it **before** upgrading blogr.

---

## Why a major dependency shift?

| Requirement | blogr ≤ v1.32.x | blogr ≥ v2.0.0 |
|-------------|-----------------|-----------------|
| Laravel     | 12.x            | **13.x**        |
| Filament    | v4.8.5          | **v5.0**        |
| Livewire    | 3.x             | **4.x**         |
| PHP         | 8.3+            | 8.3+ (unchanged) |

These changes are transitive: you cannot keep Laravel 12 or Filament v4 and upgrade blogr.

---

## Step-by-step upgrade

### 1 — Update dependencies (one command, no manual editing)

```bash
# Core + plugins + Laravel + Filament — all in one go
composer require \
  laravel/framework:^13.0 \
  laravel/tinker:^3.0 \
  filament/filament:^5.0 \
  happytodev/blogr:^2.0 \
  happytodev/blogr-comments:^2.0 \
  happytodev/blogr-gdpr:^2.0 \
  happytodev/blogr-artist:^2.0 \
  happytodev/blogr-docs:^2.0 \
  spatie/laravel-backup:^10.0 \
  -W --no-update

# Then run the actual update
composer update -W
```

**If you prefer to edit `composer.json` manually**, here are the diff:

```diff
"require": {
-    "laravel/framework": "^12.0",
+    "laravel/framework": "^13.0",

-    "laravel/tinker": "^2.10",
+    "laravel/tinker": "^3.0",

-    "filament/filament": "^4.2",
+    "filament/filament": "^5.0",

-    "happytodev/blogr": "^1.32",
+    "happytodev/blogr": "^2.0",

-    "happytodev/blogr-comments": "^1.1",
+    "happytodev/blogr-comments": "^2.0",

-    "happytodev/blogr-gdpr": "^1.5",
+    "happytodev/blogr-gdpr": "^2.0",

-    "happytodev/blogr-artist": "^1.0",
+    "happytodev/blogr-artist": "^2.0",

-    "happytodev/blogr-docs": "^1.10",
+    "happytodev/blogr-docs": "^2.0",

-    "spatie/laravel-backup": "^9.0",
+    "spatie/laravel-backup": "^10.0",
}
```

### 2 — Verify your `bootstrap/app.php`

Laravel 13 renamed `ValidateCsrfToken` to `PreventRequestForgery`.  
If your application references it, update the import:

```diff
- use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
+ use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
```

### 3 — Run your test suite

```bash
php artisan test
# or
vendor/bin/pest --parallel
```

### 3. Verify your `bootstrap/app.php`

Laravel 13 renamed `ValidateCsrfToken` to `PreventRequestForgery`.  
If your application references it, update the import:

```diff
- use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
+ use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
```

### 4. Run your test suite

```bash
php artisan test
# or
vendor/bin/pest --parallel
```

---

## Known breaking changes in the dependency chain

### Laravel 12 → 13

- **`VerifyCsrfToken` / `ValidateCsrfToken`** → renamed to `PreventRequestForgery` (deprecated aliases still work for now)
- **`JobAttempted` event**: `$exceptionOccurred` (bool) replaced by `$exception` (object or null)
- **`QueueBusy` event**: `$connection` renamed to `$connectionName`
- **`Container::call`**: Now respects nullable class defaults (returns `null` instead of resolving)
- **JSON encoding**: `Http::response()` with array body now throws on invalid UTF-8

### Filament v4 → v5

- **Livewire v4**: `Livewire\Mechanisms\ComponentRegistry` removed — use `Str::kebab(class_basename())` for component name resolution
- **`Filament\Support\Icons\Heroicon` enum** → replaced by plain string icons (`'heroicon-o-folder'` instead of `Heroicon::OutlinedFolder`)
- **PanelProvider methods**: `static function panel()` → `function panel()` (instance method)

### spatie/laravel-permission v6 → v8

Consult the [official upgrade guide](https://github.com/spatie/laravel-permission) for migration details.

---

## Rollback

If the upgrade fails, restore your previous `composer.json` and `composer.lock`:

```bash
git checkout -- composer.json composer.lock
composer install
```

Then fix any issues and retry.
