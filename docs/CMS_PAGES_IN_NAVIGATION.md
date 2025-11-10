# CMS Pages in Navigation Menu

## Overview

You can now add CMS pages directly to your navigation menu. This allows you to link to any CMS page you've created within your blog's navigation bar, just like you would with categories or external URLs.

## Features

- **Multi-locale support**: CMS pages with multiple translations automatically use the correct slug for each language
- **Multilingual labels**: Set different menu item labels for each language
- **Active state detection**: The menu item is highlighted when visiting that CMS page
- **Graceful fallback**: If a CMS page doesn't have a translation for the current locale, the link is skipped

## Configuration

### Via Filament Settings (UI)

1. Go to **Blogr Settings** → **Navigation**
2. In **Navigation Menu Items**, click **Add Menu Item**
3. Set **Link Type** to **"CMS Page"**
4. Select the **CMS Page** from the dropdown
5. Add **Labels** (language-specific text for the menu)
6. Choose **Open in** (same or new window)
7. Save

### Via Config (Code)

Add menu items to `config/blogr.php`:

```php
'ui' => [
    'navigation' => [
        'menu_items' => [
            [
                'type' => 'cms_page',
                'label' => 'About Us',  // Fallback label
                'cms_page_id' => 1,    // ID of the CMS page
                'target' => '_self',
            ],
            // With multilingual labels
            [
                'type' => 'cms_page',
                'labels' => [
                    ['locale' => 'en', 'label' => 'Contact'],
                    ['locale' => 'fr', 'label' => 'Nous Contacter'],
                ],
                'cms_page_id' => 2,
                'target' => '_self',
            ],
        ],
    ],
],
```

## Menu Item Types

The following link types are supported:

| Type | Description |
|------|-------------|
| `external` | External URL (HTTP/HTTPS) |
| `blog` | Link to blog home page |
| `category` | Link to a blog category |
| **`cms_page`** | **NEW: Link to a CMS page** |
| `megamenu` | Dropdown menu with sub-items |

## Examples

### Example 1: Simple CMS Page Link

```php
[
    'type' => 'cms_page',
    'label' => 'Services',
    'cms_page_id' => 3,
    'target' => '_self',
]
```

Renders as: `/en/services` (for English locale)

### Example 2: CMS Page with Multilingual Labels

```php
[
    'type' => 'cms_page',
    'labels' => [
        ['locale' => 'en', 'label' => 'About Our Company'],
        ['locale' => 'fr', 'label' => 'À Propos de Notre Entreprise'],
    ],
    'cms_page_id' => 5,
    'target' => '_self',
]
```

Displays "About Our Company" in English, "À Propos de Notre Entreprise" in French.

### Example 3: Mixed Menu

```php
'menu_items' => [
    [
        'type' => 'blog',
        'label' => 'Blog',
        'target' => '_self',
    ],
    [
        'type' => 'cms_page',
        'label' => 'Contact Us',
        'cms_page_id' => 2,
        'target' => '_self',
    ],
    [
        'type' => 'external',
        'label' => 'GitHub',
        'url' => 'https://github.com/happytodev/blogr',
        'target' => '_blank',
    ],
]
```

## How It Works

### URL Generation

When rendering the menu, the component:

1. Finds the CMS page by ID
2. Looks for a translation matching the current locale
3. Generates the correct URL: `/{locale}/{slug}`

Example with multiple locales:

```
Page: "About Us" (ID: 1)
├─ EN translation: slug = "about"  → /en/about
├─ FR translation: slug = "a-propos" → /fr/a-propos
└─ DE translation: slug = "uber-uns" → /de/uber-uns
```

### Active State

The menu item is marked as "active" when:

- The current route is `cms.page.show` AND
- The current slug matches the CMS page translation slug

### Graceful Handling

The component handles missing data gracefully:

- **Missing CMS page**: Link is skipped (page was deleted but reference remains)
- **Missing translation**: Link is skipped for that locale (user navigates to different language)
- **Unpublished page**: Still renders the link (you can control visibility via page publishing)

## Testing

Tests are included in `tests/Feature/NavigationMenuCmsPageTest.php`:

```bash
./vendor/bin/pest tests/Feature/NavigationMenuCmsPageTest.php
```

## Backward Compatibility

This change is fully backward compatible. Existing menu configurations with `external`, `blog`, `category`, and `megamenu` types continue to work unchanged.

## Related

- [Navigation Menu Documentation](./NAVIGATION_MENU.md)
- [CMS Pages Documentation](./CMS_PAGES.md)
