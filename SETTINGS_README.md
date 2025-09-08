# Blogr Settings Page

The Blogr Settings page provides a comprehensive interface for managing all blog configuration options through the Filament admin panel.

## Features

- **General Settings**: Configure posts per page, route prefix, and middleware
- **Appearance**: Customize colors and visual elements
- **Reading Time**: Configure reading speed calculation and display options
- **SEO Settings**: Manage site metadata, titles, descriptions, and social media handles
- **Open Graph**: Configure default Open Graph image and dimensions
- **Structured Data**: Enable JSON-LD structured data and organization information

## Usage

1. Access the settings page through the Filament admin panel under "Blogr > Settings"
2. Modify any configuration options as needed
3. Click "Save Settings" to update the configuration
4. The system will automatically clear the configuration cache

## Configuration File

All settings are stored in `config/blogr.php`. The page provides a user-friendly interface to modify this file without manual editing.

## Technical Implementation

- **Page Class**: `Happytodev\Blogr\Filament\Pages\BlogrSettings`
- **View**: `resources/views/filament/pages/blogr-settings.blade.php`
- **Plugin Registration**: Automatically registered in `BlogrPlugin.php`

## Validation

The settings page includes comprehensive validation for all form fields:
- Numeric fields have min/max constraints
- Required fields are properly validated
- Color fields accept valid hex color codes

## Testing

Run the test suite to verify functionality:

```bash
./vendor/bin/pest tests/Feature/BlogrSettingsTest.php
```

## Cache Management

When settings are saved, the system automatically runs `php artisan config:clear` to ensure changes take effect immediately.
