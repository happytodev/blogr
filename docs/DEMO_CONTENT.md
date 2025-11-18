# Blogr Demo CMS Pages

This document describes the demo CMS pages included with Blogr and how to work with them.

## Overview

Blogr ships with two beautiful, fully-functional demo CMS pages:

1. **Home Page** - A modern landing page showcasing Blogr's features
2. **Contact Page** - A contact page with call-to-action and information sections

Both pages are available in **English and French**, demonstrating Blogr's multilingual capabilities.

## Installing Demo Pages

### Automatic Installation

During installation, you can choose to install demo pages:

```bash
php artisan blogr:publish-demo-pages --force
```

### Command Options

- **`--force`** - Overwrite existing demo pages (otherwise asks for confirmation)
- **`--backup`** - Create a backup of existing CMS pages before publishing

### Examples

```bash
# Install demo pages with confirmation (default)
php artisan blogr:publish-demo-pages

# Force install without confirmation
php artisan blogr:publish-demo-pages --force

# Install with automatic backup
php artisan blogr:publish-demo-pages --force --backup

# Create backup of current CMS pages
php artisan blogr:publish-demo-pages --backup
```

## Home Page Details

**Slug:** `home-page`  
**Template:** Landing  
**Published:** Yes  
**Homepage:** Yes (set as your site's homepage)

### Blocks Used

1. **Hero Block**
   - Title: "Blogr: Modern Multilingual Blog Platform"
   - Subtitle: Introducing Blogr's capabilities
   - Call-to-action button linking to `/blog`
   - Gradient background (purple to pink)

2. **Features Block**
   - 6 feature cards highlighting key capabilities:
     - Easy Content Management
     - Multilingual Support
     - High Performance
     - Analytics Ready
     - Secure & Reliable
     - Fully Customizable

3. **Content Block**
   - Markdown-formatted text explaining why choose Blogr
   - Lists key benefits (Fast, Flexible, Secure, Scalable)
   - Renders as prose with nice typography

4. **Call-to-Action Block**
   - Heading: "Ready to Get Started?"
   - Invitation to create first blog post
   - Button linking to `/blog/new`
   - Same gradient background as hero

### URLs

- **English:** `/blog/home`
- **French:** `/blog/accueil`

## Contact Page Details

**Slug:** `contact`  
**Template:** Contact  
**Published:** Yes  
**Homepage:** No

### Blocks Used

1. **Call-to-Action Block**
   - Heading: "Let's Connect"
   - Invitation to send a message
   - Button with anchor link to contact form
   - Gradient background

2. **Features Block** (styled as contact info)
   - 3 contact method cards:
     - Email support
     - Live chat during business hours
     - Social media contact
   - Each card includes emoji and description

### Content

Markdown content explaining Blogr's availability and communication channels.

### URLs

- **English:** `/blog/contact`
- **French:** `/blog/contact`

Note: Contact pages use the same slug for both languages (this can be customized).

## Customizing Demo Pages

### Edit in Filament Admin

1. Navigate to Filament Admin Panel
2. Go to **CMS Pages**
3. Select "Home Page" or "Contact Page"
4. Edit blocks, translations, and publish settings
5. Save changes

### Edit Programmatically

```php
use Happytodev\Blogr\Models\CmsPage;

// Get home page
$home = CmsPage::where('slug', 'home-page')->first();

// Update English translation
$home->translations()->where('locale', 'en')->update([
    'title' => 'My Custom Home',
    'content' => 'My custom content...',
]);

// Add or modify blocks
$home->update([
    'blocks' => [
        [
            'type' => 'hero',
            'data' => [
                'title' => 'My Custom Title',
                // ... more data
            ],
        ],
        // ... more blocks
    ],
]);

$home->save();
```

### Edit Seeder

Modify `/database/seeders/CmsPageSeeder.php` to change:
- Page content and translations
- Block structure and styling
- Featured URLs and calls-to-action

Then re-run:

```bash
php artisan db:seed --class=CmsPageSeeder
```

## Block Types Reference

### Hero Block

```php
[
    'type' => 'hero',
    'data' => [
        'title' => 'Main headline',
        'subtitle' => 'Secondary text',
        'cta_text' => 'Button label',
        'cta_url' => '/destination',
        'alignment' => 'center', // 'left', 'center', 'right'
        'background_color' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    ],
]
```

### Features Block

```php
[
    'type' => 'features',
    'data' => [
        'title' => 'Section title',
        'subtitle' => 'Section subtitle',
        'columns' => '3', // '2', '3', or '4'
        'items' => [
            [
                'title' => 'Feature 1',
                'description' => 'Description...',
            ],
            // ... more items
        ],
    ],
]
```

### Content Block

```php
[
    'type' => 'content',
    'data' => [
        'content' => '# Markdown content here',
        'max_width' => 'prose', // 'prose' or 'full'
    ],
]
```

### Call-to-Action (CTA) Block

```php
[
    'type' => 'cta',
    'data' => [
        'heading' => 'Main CTA headline',
        'subheading' => 'Subheading text',
        'button_text' => 'Button label',
        'button_url' => '/destination',
        'button_style' => 'primary', // 'primary' or 'secondary'
        'background_color' => 'linear-gradient(...)',
    ],
]
```

## Backup & Restore

### Create Backup

```bash
php artisan blogr:publish-demo-pages --backup
```

Backups are stored in `storage/app/blogr-backups/` with timestamps.

### List Backups

```php
$backupService = app(\Happytodev\Blogr\Services\CmsPageBackupService::class);
$backups = $backupService->listBackups();
```

### Restore from Backup

```php
$backupService = app(\Happytodev\Blogr\Services\CmsPageBackupService::class);
$result = $backupService->restore($backupPath);

// $result = [
//     'pages_restored' => 2,
//     'translations_restored' => 4,
//     'errors' => [],
// ]
```

## Removing Demo Pages

### Via Filament Admin

1. Go to **CMS Pages**
2. Select home page or contact page
3. Click **Delete** button
4. Confirm deletion

### Programmatically

```php
use Happytodev\Blogr\Models\CmsPage;

// Delete both demo pages
CmsPage::whereIn('slug', ['home-page', 'contact'])->delete();
```

## Tips & Best Practices

1. **Use as Template** - Copy demo pages as templates for your own pages
2. **Test Features** - Use demo blocks to understand Blogr's capabilities
3. **Customize Gradually** - Edit blocks in admin panel one at a time
4. **Backup Before Changes** - Always backup before major modifications
5. **Translation Workflow** - Update all language translations together
6. **Block Organization** - Order blocks from top to bottom for user flow

## Troubleshooting

**Demo pages not appearing after install:**
- Check database migrations ran: `php artisan migrate`
- Verify Filament admin is accessible
- Check storage logs: `tail storage/logs/laravel.log`

**Blocks not rendering:**
- Ensure all block components exist in `resources/views/components/blocks/`
- Verify block data structure matches block component requirements
- Check browser console for JavaScript errors

**Multilingual issues:**
- Verify locales are configured in `config/blogr.php`
- Check translation slug uniqueness constraints
- Review database for orphaned translations

## See Also

- [CMS Pages Documentation](./CMS_PAGES.md)
- [Block Builder Guide](./BLOCK_BUILDER.md)
- [Multilingual Setup](./MULTILINGUAL.md)
