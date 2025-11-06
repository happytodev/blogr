# Navigation Menu System

## Overview

The navigation menu system allows creating custom links in the blog header. Menus are configurable from the **Settings > Navigation** page in the Filament administration.

## Features

- **4 link types**: External URL, CMS Page, Blog Home, Category
- **Responsive**: Desktop horizontal centered menu + mobile menu with hamburger button
- **Active state**: Automatic current page detection with different styling
- **Multilingual**: URLs are generated with the current locale
- **Reorderable**: Drag & drop to change item order
- **Configurable target**: Open in same tab or new tab
- **External icon**: Automatic display of an icon for external links (_blank)

## Configuration

### Accessing the interface

1. Log in to the Filament administration
2. Go to **Settings** (⚙️ in the sidebar)
3. Click on the **Navigation** tab
4. Enable navigation if not already done
5. Scroll down to the **Navigation Menu Items** section

### Adding a menu item

Click on **Add Menu Item** to create a new link. Each item requires:

#### Required fields

- **Label**: Text displayed in the menu (ex: "About", "Contact", "Blog")
- **Link Type**: Type of link to create

#### Available link types

##### 1. External URL
- Displays a **URL** field
- Allows linking to any website
- Example: `https://example.com`, `https://github.com/username`

##### 2. CMS Page
- Displays a **CMS Page** dropdown
- Lists all published CMS pages
- URL is automatically generated with the current locale
- Active page is automatically detected

##### 3. Blog Home
- No additional configuration
- Generates link to `/{locale}/blog`
- Active page is automatically detected

##### 4. Category
- Displays a **Category** dropdown
- Lists all available categories
- URL is automatically generated with the current locale
- Active category is automatically detected

#### Optional fields

- **Target**:
  - `Same window` (_self): Opens link in same tab (default)
  - `New window` (_blank): Opens link in new tab
- **Icon**: *Disabled for now* (requires blade-ui-kit)

### Reordering items

1. Use the ⋮⋮ (handle) icon on the left of each item
2. Drag and drop the item to the desired position
3. Save the changes

### Configuration example

Here is an example of a typical menu:

```
1. Home (Blog Home)
2. Articles (Category → "Tutorials")
3. About (CMS Page → "about")
4. Contact (CMS Page → "contact")
5. GitHub (External URL → https://github.com/happytodev/blogr, _blank)
```

## Frontend display

### Desktop (≥ 768px)

- Horizontal centered menu between logo (left) and language/theme switchers (right)
- Links with hover effect and active state
- Automatic "external link" icon for target="_blank"

### Mobile (< 768px)

- Hamburger button (☰) appears to the left of switchers
- Dropdown menu with slide-down animation
- Automatic closing when clicking outside the menu
- Items in vertical list with more spacing

## Styles and customization### CSS Classes used

```css
/* Desktop menu */
.flex items-center space-x-1 px-4 py-2 rounded-md text-sm font-medium

/* Active state */
text-[var(--color-primary)] dark:text-[var(--color-primary-dark)]
bg-gray-100 dark:bg-gray-800

/* Normal state with hover */
text-gray-700 dark:text-gray-300
hover:bg-gray-100 dark:hover:bg-gray-800
hover:text-[var(--color-primary-hover)] dark:hover:text-[var(--color-primary-hover-dark)]
```

### Customizable CSS Variables

You can customize colors by modifying CSS variables in your theme:

- `--color-primary`: Primary color (active state)
- `--color-primary-dark`: Primary color in dark mode
- `--color-primary-hover`: Hover color
- `--color-primary-hover-dark`: Hover color in dark mode

## Active page detection

The system automatically detects the current page to apply active styling:

- **Blog Home**: Route `blog.index`
- **CMS Page**: Route `cms.show` + slug comparison
- **Category**: Route `blog.category` + slug comparison

External links can never be active.

## Data storage

Menu items are stored in the configuration file `config/blogr.php`:

```php
'ui' => [
    'navigation' => [
        'enabled' => true,
        'sticky' => true,
        'show_logo' => true,
        'show_language_switcher' => true,
        'show_theme_switcher' => true,
        'menu_items' => [
            [
                'label' => 'Home',
                'type' => 'blog',
                'target' => '_self',
            ],
            [
                'label' => 'GitHub',
                'type' => 'external',
                'url' => 'https://github.com/happytodev/blogr',
                'target' => '_blank',
            ],
            // ...
        ],
    ],
],
```

## Technologies used

- **Filament v4**: Form with Repeater and conditional fields
- **Alpine.js**: Mobile menu with `x-data`, `x-show`, `x-transition`
- **Tailwind CSS 3**: Responsive utility classes and dark mode
- **Blade**: Templating with URL generation logic

## Current limitations

1. **Icons**: Heroicon support is disabled (requires blade-ui-kit)
2. **Sub-menus**: Multi-level dropdown menus are not supported
3. **Mega menu**: No support for mega menus with rich content

## Future developments

- [ ] Heroicon support via blade-ui-kit
- [ ] Sub-menus (1-level dropdown)
- [ ] Notification badges (ex: "New" on an item)
- [ ] Display conditions (user roles, dates)
- [ ] Import/Export of menu configurations

## Troubleshooting

### Menus don't appear

1. Check that `navigation_enabled` is enabled in Settings > Navigation
2. Check that you saved after adding items
3. Clear configuration cache: `php artisan config:clear`

### URLs don't work

1. Check that CMS pages or categories exist and are published
2. Check that routes are properly defined in `routes/web.php`
3. Check Laravel logs: `tail -f storage/logs/laravel.log`

### Mobile menu doesn't display

1. Check that Alpine.js is properly loaded: `npm run build`
2. Open browser console to see JavaScript errors
3. Check that `x-data="{ mobileMenuOpen: false }"` is on the `<nav>` tag

### Active state doesn't work

1. Check that the current route matches the expected name
2. Add `@dump(request()->route()->getName())` in navigation.blade.php for debugging
3. Compare slugs in database and URL

## Tests

Navigation system tests are in:

```bash
# Settings tests (save/load)
./vendor/bin/pest tests/Feature/BlogrSettingsTest.php

# Rendering tests (to create)
./vendor/bin/pest tests/Feature/NavigationMenuTest.php
```

Command to run all tests:

```bash
./vendor/bin/pest --parallel
```

## Contribution

To add a new link type:

1. Add the option in the `type` Select of `BlogrSettings.php`
2. Add the necessary conditional fields
3. Update the switch() in `navigation.blade.php` to generate the URL
4. Add active state detection logic if necessary
5. Write corresponding tests
6. Update this documentation

## Support

For any questions or issues:

- GitHub Issues: https://github.com/happytodev/blogr/issues
- Documentation: https://github.com/happytodev/blogr/tree/main/docs
