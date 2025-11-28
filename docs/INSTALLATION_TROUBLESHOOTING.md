# Installation Troubleshooting Guide

## Issue: Admin Panel Shows No Resources After Installation

### Symptoms
- After running `php artisan blogr:install`, you can access the admin panel at `/admin`
- However, the sidebar shows no resources (no Blog Posts, Categories, Users, Settings)
- The admin panel appears empty except for the Dashboard

### Root Cause
This happens when the `HasRoles` trait is added to your `User` model during installation, but PHP has already loaded the class definition **without** the trait. Subsequent attempts to assign the admin role fail silently because the trait's methods don't exist in the loaded class.

### Verification
Check if your user has the admin role:

```bash
php artisan tinker --execute='
$user = App\Models\User::first();
echo "User: " . $user->email . "\n";
echo "Has admin role: " . ($user->hasRole("admin") ? "YES" : "NO") . "\n";
'
```

If it shows "Has admin role: NO", you need to manually assign the role.

### Solution 1: Manually Assign Admin Role (Quick Fix)

```bash
# Get your user ID (usually 1 for the first user)
php artisan tinker --execute='
$user = App\Models\User::first();
echo "User ID: " . $user->id . "\n";
echo "Email: " . $user->email . "\n";
'

# Assign admin role to your user (replace ID if needed)
php artisan tinker --execute='
App\Models\User::find(1)->assignRole("admin");
echo "Admin role assigned!\n";
'
```

### Solution 2: Re-run Installation (Complete Fix)

If you want to re-run the full installation process:

```bash
# 1. Clear the cache
php artisan config:clear
php artisan cache:clear

# 2. Re-run the installation
php artisan blogr:install --force

# The installation should now correctly assign the admin role
```

### Solution 3: Create Admin Role Assignment Command

Create a custom artisan command to assign roles:

```bash
php artisan make:command AssignAdminRole
```

Then edit `app/Console/Commands/AssignAdminRole.php`:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class AssignAdminRole extends Command
{
    protected $signature = 'user:make-admin {email}';
    protected $description = 'Assign admin role to a user by email';

    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found!");
            return 1;
        }

        if ($user->hasRole('admin')) {
            $this->info("User {$email} already has admin role.");
            return 0;
        }

        $user->assignRole('admin');
        $this->info("Admin role assigned to {$email}!");
        
        return 0;
    }
}
```

Usage:
```bash
php artisan user:make-admin your-email@example.com
```

## Issue: CMS Homepage Not Configured After Installation

### Symptoms
- During installation, you selected "CMS" as the homepage type
- After installation, accessing the root URL (/) shows a 404 error
- The blog is accessible at `/blog` but not at `/`

### Root Cause
In versions prior to v0.15.10, the installation command tried to write CMS preferences **before** the config file was published, resulting in lost preferences.

### Verification
Check your config file:

```bash
cat config/blogr.php | grep -A 2 "homepage"
cat config/blogr.php | grep -A 2 "'cms'"
```

If you see:
```php
'homepage' => [
    'type' => 'blog', // Should be 'cms'
],
'cms' => [
    'enabled' => false, // Should be true
],
```

Then your CMS preferences were not saved.

### Solution 1: Manual Config Update

Edit `config/blogr.php`:

```php
'homepage' => [
    'type' => 'cms', // Changed from 'blog'
],

'cms' => [
    'enabled' => true, // Changed from false
    'prefix' => '', // Leave empty for URLs like /about
],
```

Then clear the config cache:
```bash
php artisan config:clear
```

### Solution 2: Upgrade and Re-install

```bash
# 1. Update Blogr to v0.15.10 or later
composer update happytodev/blogr

# 2. Clear cache
php artisan config:clear

# 3. Re-run installation (will use fixed version)
php artisan blogr:install --force
```

## Verification After Fixes

### Check Admin Role
```bash
php artisan tinker --execute='
$user = App\Models\User::first();
echo "Email: " . $user->email . "\n";
echo "Roles: " . $user->getRoleNames()->implode(", ") . "\n";
echo "Can access admin panel: " . ($user->can("viewAny", App\Models\User::class) ? "YES" : "NO") . "\n";
'
```

Expected output:
```
Email: your@email.com
Roles: admin
Can access admin panel: YES
```

### Check CMS Homepage Config
```bash
php artisan tinker --execute='
echo "Homepage type: " . config("blogr.homepage.type") . "\n";
echo "CMS enabled: " . (config("blogr.cms.enabled") ? "YES" : "NO") . "\n";
'
```

Expected output (if you chose CMS during installation):
```
Homepage type: cms
CMS enabled: YES
```

### Access Admin Panel
1. Go to `http://your-app.test/admin`
2. Login with your credentials
3. You should see:
   - Dashboard
   - CMS section (if enabled)
     - CMS Pages
   - Blogr section
     - Blog Posts
     - Categories
     - Tags
     - Blog Series
   - Settings
   - Users (if you have admin role)

## Prevention for Fresh Installations

These issues have been fixed in Blogr v0.15.10+. To avoid them:

1. **Always use the latest version**:
   ```bash
   composer require happytodev/blogr:^0.15.10
   ```

2. **Run installation once**:
   - Don't interrupt the installation process
   - Let it complete fully before accessing the admin panel

3. **If you need to re-run installation**:
   - Use `--force` flag
   - Clear cache first: `php artisan config:clear`

## Getting Help

If you still have issues after following this guide:

1. Check the [GitHub Issues](https://github.com/happytodev/blogr/issues)
2. Create a new issue with:
   - Your Laravel version: `php artisan --version`
   - Your PHP version: `php -v`
   - Your Blogr version: `composer show happytodev/blogr`
   - The exact error message
   - Steps to reproduce

## Related Issues

- [#174: CMS homepage not set as default](https://github.com/happytodev/blogr/issues/174)
- [#172: MySQL installation failure](https://github.com/happytodev/blogr/issues/172)
