# Fix for Issue #115: Double Comma in User Model Casts

## Problem

If you encountered this error during installation:

```
Cannot use empty array elements in arrays

at app/Models/User.php:50
   46▕     protected function casts(): array
   47▕     {
   48▕         return [
   49▕             'email_verified_at' => 'datetime',
➜  50▕             'password' => 'hashed',,
   51▕             'bio' => 'array',
   52▕         ];
   53▕     }
```

This is caused by a double comma (`,,`) in the casts array, which was a bug in version 0.11.0 of the installation script.

## Quick Fix

1. **Open your `app/Models/User.php` file**

2. **Find the `casts()` method** (or `$casts` property for Laravel 10)

3. **Remove the double comma**

   Change from:
   ```php
   protected function casts(): array
   {
       return [
           'email_verified_at' => 'datetime',
           'password' => 'hashed',, // ← Double comma here!
           'bio' => 'array',
       ];
   }
   ```

   To:
   ```php
   protected function casts(): array
   {
       return [
           'email_verified_at' => 'datetime',
           'password' => 'hashed', // ← Fixed: single comma
           'bio' => 'array',
       ];
   }
   ```

4. **Save the file and retry the installation**

## Permanent Solution

Update to Blogr v0.11.1 or later, which includes the fix for this issue.

```bash
composer update happytodev/blogr
```

Then you can safely re-run the installation command:

```bash
php artisan blogr:install
```

## Prevention

This bug has been fixed in the installation script (v0.11.1+) and now:
- Properly removes trailing commas before adding new array elements
- Includes automated tests to prevent this regression
- Will never create double commas in your code

If you still encounter issues, please report them at: https://github.com/happytodev/blogr/issues
