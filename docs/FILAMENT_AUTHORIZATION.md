# Filament Panel Authorization

## Overview

Starting from version 0.13.0, Blogr automatically configures your User model for Filament panel authorization during installation. This feature adds secure, domain-based authentication to control who can access the Filament admin panel.

## What Gets Configured

When you run `php artisan blogr:install`, the command automatically modifies your `app/Models/User.php` file to add:

1. **Interface Implementation**: Adds `FilamentUser` interface
2. **Required Imports**:
   ```php
   use Filament\Models\Contracts\FilamentUser;
   use Filament\Panel;
   ```
3. **Authorization Method**: Implements `canAccessPanel()` with domain-based security

## Security Implementation

The authorization uses email domain checking instead of a permissive `return true`:

```php
public function canAccessPanel(Panel $panel): bool
{
    return str_ends_with($this->email, '@' . config('app.domain', 'example.com'));
}
```

This means only users with email addresses matching your configured domain can access the panel.

### Configuration

Set your domain in `config/app.php`:

```php
'domain' => env('APP_DOMAIN', 'yourdomain.com'),
```

Or add to your `.env` file:

```env
APP_DOMAIN=yourdomain.com
```

## Edge Cases Handled

The installation process safely handles:

- ✅ **Already Configured**: Skips if FilamentUser is already present
- ✅ **Multiple Interfaces**: Correctly adds FilamentUser to existing interfaces
- ✅ **Existing Imports**: Preserves and enhances use statement order
- ✅ **No Duplication**: Never adds duplicate imports or methods

## Examples

### Before Installation
```php
class User extends Authenticatable
{
    use HasFactory, Notifiable;
    // ...
}
```

### After Installation
```php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;
    
    // ... existing code ...
    
    public function canAccessPanel(Panel $panel): bool
    {
        return str_ends_with($this->email, '@' . config('app.domain', 'example.com'));
    }
}
```

### With Multiple Interfaces
```php
// Before
class User extends Authenticatable implements MustVerifyEmail
{
    // ...
}

// After
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    // ...
    
    public function canAccessPanel(Panel $panel): bool
    {
        return str_ends_with($this->email, '@' . config('app.domain', 'example.com'));
    }
}
```

## Manual Configuration

If you need to customize the authorization logic, simply modify the `canAccessPanel()` method in your User model:

```php
public function canAccessPanel(Panel $panel): bool
{
    // Custom logic examples:
    
    // Allow specific emails
    return in_array($this->email, ['admin@example.com', 'editor@example.com']);
    
    // Check user role
    return $this->hasRole('admin');
    
    // Combine conditions
    return $this->hasRole('admin') && $this->email_verified_at !== null;
}
```

## Testing

The feature includes comprehensive test coverage:

- **Unit Tests** (`UserModelFilamentConfigurationTest.php`): 4 tests
  - Adds configuration when not present
  - No duplication if already configured
  - Uses domain-based authorization
  - Handles multiple interfaces

- **Integration Tests** (`BlogrInstallFilamentConfigTest.php`): 2 tests
  - Verifies installation process
  - Tests idempotency (skip if already configured)

## Troubleshooting

### Issue: Users can't access the panel
**Solution**: Check your `APP_DOMAIN` configuration matches your users' email domains.

### Issue: Configuration not applied
**Solution**: The installation skips if FilamentUser is already present. Check your User model manually.

### Issue: Multiple interfaces causing errors
**Solution**: This is automatically handled. The installation correctly adds FilamentUser to existing interfaces.

## References

- [Filament Documentation - User Authorization](https://filamentphp.com/docs/4.x/users/overview#authorizing-access-to-the-panel)
- [Laravel Configuration](https://laravel.com/docs/configuration)
- [Blogr Installation Guide](README.md#installation)

## Version History

- **v0.13.0**: Feature added with TDD approach
- Comprehensive tests ensure reliability
- Handles all edge cases automatically
