<div align="center">

# 🚀 Blogr – The Ultimate FilamentPHP Blog Plugin

[![Latest Version](https://img.shields.io/packagist/v/happytodev/blogr.svg?style=flat-square)](https://packagist.org/packages/happytodev/blogr)
[![Tests](https://img.shields.io/github/actions/workflow/status/happytodev/blogr/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/happytodev/blogr/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Code Style](https://img.shields.io/github/actions/workflow/status/happytodev/blogr/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/happytodev/blogr/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Downloads](https://img.shields.io/packagist/dt/happytodev/blogr.svg?style=flat-square)](https://packagist.org/packages/happytodev/blogr)

![Blogr Banner](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/blogr.webp)

**A production-ready, feature-rich blog system for Laravel & FilamentPHP**

[Features](#-key-features) • [Installation](#-quick-start) • [Documentation](#-documentation) • [Screenshots](#-screenshots) • [Support](#-support)

</div>

---

## ✨ Overview

Transform your Laravel application into a powerful blogging platform with **Blogr** – a comprehensive FilamentPHP plugin designed for developers who demand excellence. Built with modern best practices, fully tested (680+ tests), and packed with features you'll actually use.

**Why Blogr?**
- 🌍 **True Multilingual** – Translate everything (posts, series, categories, tags)
- 📚 **Blog Series** – Organize content into cohesive learning paths
- � **CMS Page Builder** – Create static pages (About, Contact, etc.) with block system
- �💾 **Backup & Restore** – Export/import all data with media files
- 🎨 **Fully Customizable** – Theme system, dark mode, configurable UI
- 🔍 **SEO Optimized** – Meta tags, Open Graph, Schema.org, RSS feeds
- ⚡ **Production Ready** – Comprehensive test coverage, battle-tested code

---

## 🎯 Key Features

<table>
<tr>
<td width="50%">

### 💾 Backup & Migration System
- **Complete data export** to JSON or ZIP
- **Media files included** (images, avatars)
- **One-click restore** from admin panel
- **Migration-ready** for site transfers

### 🌍 Multilingual Support
- **4+ languages** out of the box (en, fr, es, de)
- **Localized routes** (`/{locale}/blog/...`)
- **Translation UI** in admin panel
- **SEO-friendly** hreflang tags
- **Language switcher** component

### 📚 Blog Series
- **Organize related posts** into series
- **Auto-navigation** (prev/next)
- **Position ordering** within series
- **Featured series** highlighting
- **Progress tracking** for readers

### 📄 CMS Page Builder
- **Static pages** (About, Contact, etc.)
- **Block-based editor** (Hero, Features, Testimonials, CTA)
- **Multiple templates** (Default, Full Width, Sidebar)
- **Homepage option** – Set any page as homepage
- **Reserved slugs** protection
- **Multilingual pages** support

</td>
<td width="50%">

### ✍️ Content Management
- **Markdown editor** with live preview
- **Drag & drop images** in content
- **Post scheduling** (draft/scheduled/published)
- **Categories & tags** system
- **Reading time** calculation
- **Table of contents** (auto-generated)
- **TL;DR** summaries

### 🎨 Theming & UI
- **CSS variables** theming system
- **Dark mode** support (auto/manual)
- **Customizable colors** per component
- **Flexible layouts** (sidebar TOC, centered)
- **Author profiles** with avatars & bios
- **Responsive design** mobile-first

### 🔍 SEO & Performance
- **Meta tags** (title, description, keywords)
- **Open Graph** & Twitter Cards
- **Schema.org** structured data
- **RSS feeds** (global, per category/tag)
- **Optimized URLs** & slugs
- **Sitemap ready**

</td>
</tr>
</table>

### 📊 Dashboard Widgets

Five powerful widgets to monitor your blog:
- **BlogStatsOverview** – Posts, categories, tags metrics
- **RecentBlogPosts** – Latest posts with quick actions
- **ScheduledPosts** – Upcoming publications
- **BlogPostsChart** – Publication trends (12 months)
- **BlogReadingStats** – Reading time analytics

### 👥 Author Features

- **Enhanced profiles** with bio (Markdown support)
- **Avatar management** with auto-fallback
- **Author pages** (`/blog/author/{userId}`)
- **Role-based permissions** (Admin, Writer)
- **Self-service profile editing**

### ⚙️ Admin Experience

- **Filament v4** native integration
- **Intuitive settings page** with tabs
- **Tutorial content** for onboarding
- **Demo seeders** for quick start
- **Extensive documentation**



---

## 📸 Screenshots

<details>
<summary><b>🖼️ Click to view screenshots</b></summary>

### Frontend Views

**Blog Home Page**
![Blogr home](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/blogr-home.png)

**Blog Post View**
![Blog post view](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/image-1.png)

**Blog Series**
![Series](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/blogr-series.png)

### Admin Panel

**Posts List**
![Backend - List of posts](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/image-2.png)

**Post Editor**
![Backend - Edit post](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/image-3.png)

**Settings Page**
![Backend - Settings](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/image-4.png)

![Backend - New Settings](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/blogr-new-settings.png)

**Dashboard Widgets**
![Backend - Widgets](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/image-5.png)

### Interactive Demo

**Drag & Drop Images**
![Drag & Drop Demo](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/demo-1.gif)

</details>

---

## 🚀 Quick Start

### Prerequisites

- **Laravel 12.x**
- **FilamentPHP v4.x**
- PHP 8.3+

### Installation (2 minutes!)

```bash
# 1. Install via Composer
composer require happytodev/blogr

# 2. Run automated installer
php artisan blogr:install

# 3. That's it! 🎉
```

The installer handles everything:
- ✅ Publishes config & migrations
- ✅ Runs database migrations
- ✅ Configures Alpine.js & Tailwind CSS
- ✅ Installs npm dependencies
- ✅ Creates storage symlink
- ✅ (Optional) Installs tutorial content

### Installation Options

```bash
# Full installation (recommended)
php artisan blogr:install

# Skip tutorial content
php artisan blogr:install --skip-tutorials

# Skip asset building (build later)
php artisan blogr:install --skip-build

# Skip all frontend setup
php artisan blogr:install --skip-frontend
```

### Manual Installation

<details>
<summary><b>Click for manual installation steps</b></summary>

# Skip tutorial and series content
php artisan blogr:install --skip-tutorials --skip-series
```

### Manual Installation (Advanced)

If you prefer to configure everything manually or need more control, follow these detailed steps:

#### 1. Install Alpine.js

```bash
npm install alpinejs
```

Then add Alpine.js to your `resources/js/app.js`:

```javascript
import Alpine from 'alpinejs'

window.Alpine = Alpine

// Theme Switcher Component (required for light/dark/auto mode)
Alpine.data('themeSwitch', () => ({
    theme: localStorage.getItem('theme') || 'auto',
    
    init() {
        this.applyTheme();
        
        // Watch for system preference changes when in auto mode
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (this.theme === 'auto') {
                this.applyTheme();
            }
        });
    },
    
    setTheme(newTheme) {
        this.theme = newTheme;
        localStorage.setItem('theme', newTheme);
        this.applyTheme();
    },
    
    applyTheme() {
        const isDark = this.theme === 'dark' || 
                      (this.theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        
        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
}));

Alpine.start()
```

#### 2. Configure Tailwind CSS v4 for dark mode

Add the dark mode variant to your `resources/css/app.css`:

```css
@import 'tailwindcss';

@plugin "@tailwindcss/typography";

/* Add these @source directives to include Blogr views */
@source '../../vendor/happytodev/blogr/resources/views/**/*.blade.php';
@source '../views/vendor/blogr/**/*.blade.php';

/* Configure dark mode with class strategy */
@variant dark (.dark &);
```

**⚠️ Important**: The `@variant dark (.dark &);` line is **required** for the theme switcher to work with Tailwind CSS v4.

#### 3. Publish configuration and migrations

```bash
php artisan vendor:publish --provider="Happytodev\Blogr\BlogrServiceProvider"
```

#### 4. Run migrations

```bash
php artisan migrate
```

#### 5. Add BlogrPlugin to your AdminPanelProvider

#### 1. Publish configuration and migrations

```bash
php artisan vendor:publish --provider="Happytodev\Blogr\BlogrServiceProvider"
php artisan migrate
```

#### 2. Install Alpine.js

```bash
npm install alpinejs
```

Add to `resources/js/app.js`:
```javascript
import Alpine from 'alpinejs'
window.Alpine = Alpine

// Theme Switcher Component
Alpine.data('themeSwitch', () => ({
    theme: localStorage.getItem('theme') || 'auto',
    init() { this.applyTheme(); },
    setTheme(newTheme) {
        this.theme = newTheme;
        localStorage.setItem('theme', newTheme);
        this.applyTheme();
    },
    applyTheme() {
        const isDark = this.theme === 'dark' || 
                      (this.theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.classList[isDark ? 'add' : 'remove']('dark');
    }
}));

Alpine.start()
```

#### 3. Configure Tailwind CSS v4

Add to `resources/css/app.css`:
```css
@import 'tailwindcss';
@plugin "@tailwindcss/typography";

@source '../../vendor/happytodev/blogr/resources/views/**/*.blade.php';
@source '../views/vendor/blogr/**/*.blade.php';

@variant dark (.dark &);
```

#### 4. Register BlogrPlugin

Edit `app/Providers/Filament/AdminPanelProvider.php`:
```php
use Happytodev\Blogr\BlogrPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([BlogrPlugin::make()])
        ->profile(\Happytodev\Blogr\Filament\Pages\Auth\EditProfile::class);
}
```

#### 5. Build assets

```bash
npm run build
```

</details>

### First Steps

After installation:

1. **Access admin panel**: `/admin`
2. **Create your first post**: Admin → Blog Posts → New
3. **Configure settings**: Admin → Blogr Settings
4. **View your blog**: `/blog` (or your configured prefix)

---

## 📚 Documentation

### Configuration

All settings are manageable via the admin panel **Settings** page or `config/blogr.php`:

<details>
<summary><b>Key Configuration Options</b></summary>

```php
// config/blogr.php

// Route configuration
'route' => [
    'prefix' => 'blog', // Change to '' for homepage
    'middleware' => ['web'],
],

// Multilingual
'locales' => [
    'enabled' => true,
    'default' => 'en',
    'available' => ['en', 'fr', 'es', 'de'],
],

// SEO
'seo' => [
    'site_name' => 'My Blog',
    'default_title' => 'Blog',
    'og' => [
        'image' => '/images/og-default.jpg',
        'image_width' => 1200,
        'image_height' => 630,
    ],
],

// Theming
'colors' => [
    'primary' => '#FA2C36',
],

// Posts per page
'posts_per_page' => 10,
```

</details>

### Core Concepts

<details>
<summary><b>📝 Blog Posts & Translations</b></summary>

**Creating Posts:**
- Markdown editor with live preview
- TL;DR summaries
- Custom slugs
- Featured images (drag & drop)
- Categories & tags
- Publication scheduling

**Translations:**
- Add translations via Repeater in admin
- Each translation has independent:
  - Title, slug, content
  - SEO meta tags
  - Categories & tags
- Automatic language detection

</details>

<details>
<summary><b>📚 Blog Series</b></summary>

**Setup:**
1. Create series: Admin → Blog Series → New
2. Add translations (title, description, SEO)
3. Assign posts to series with position ordering

**Frontend Components:**
```blade
{{-- Series navigation (prev/next) --}}
<x-blogr::series-navigation :post="$post" />

{{-- Complete series list --}}
<x-blogr::series-list :series="$series" :currentPost="$post" />

{{-- Series badge --}}
<x-blogr::series-badge :post="$post" />
```

**URL:** `/blog/series/{slug}`

</details>

<details>
<summary><b>📄 CMS Page Builder</b></summary>

**Enable CMS:**
```php
// config/blogr.php
'cms' => [
    'enabled' => true,
    'prefix' => '', // Leave empty for /about, or set to 'page' for /page/about
],
```

**Create Static Pages:**
1. Admin → CMS → Pages CMS → New
2. Set slug (e.g., `about`, `contact`)
3. Choose template:
   - **Default**: Standard page with sidebar
   - **Full Width**: Wide content area
   - **Sidebar Left/Right**: Custom layouts
4. Add translations (title, content, SEO)
5. Publish the page

**Block System:**

Build pages using pre-designed blocks:

```php
// Available blocks
- Hero Section (title, subtitle, CTA, background)
- Features Grid (icon, title, description)
- Testimonials (author, quote, avatar)
- Call-to-Action (button, background)
- Content Block (rich text, Markdown)
- Image Gallery
- Contact Form
```

**Set as Homepage:**
1. Create a CMS page
2. Toggle "Page d'accueil" (Homepage)
3. Configure in `config/blogr.php`:
```php
'homepage' => [
    'type' => 'cms', // 'blog' or 'cms'
],
```

**Reserved Slugs:**
These slugs are protected and cannot be used:
- `blog`, `feed`, `author`, `category`, `tag`, `series`
- `admin`, `login`, `logout`, `register`, `dashboard`
- `api`, `assets`, `storage`, `vendor`

**URL Examples:**
- About page: `/about` or `/en/about` (with locales)
- Contact: `/contact` or `/fr/contact`
- Custom prefix: `/page/about` (if prefix = 'page')

</details>

<details>
<summary><b>🌍 Multilingual Setup</b></summary>

**Enable in Settings:**
- Admin → Blogr Settings → Multilingual
- Toggle "Enable Localized Routes"
- Set default locale and available locales

**URLs:**
- Enabled: `/{locale}/blog/{slug}` (e.g., `/fr/blog/mon-article`)
- Disabled: `/blog/{slug}` (translation via relationships)

**Components:**
```blade
{{-- Language switcher --}}
<x-blogr::language-switcher 
    current-route="blog.show" 
    :route-parameters="['slug' => $post->slug]" 
/>

{{-- Hreflang SEO tags --}}
<x-blogr::hreflang-tags 
    current-route="blog.show" 
    :route-parameters="['slug' => $post->slug]" 
/>
```

</details>

<details>
<summary><b>💾 Backup & Import</b></summary>

**Export Data:**
1. Admin → Blogr Settings → Backup tab
2. Choose format (JSON or ZIP with media)
3. Download backup file

**Import Data:**
```bash
php artisan blogr:import backup.zip
```

**What's included:**
- Posts, series, categories, tags
- All translations
- Media files (images, avatars)
- Relationships preserved

</details>

<details>
<summary><b>👤 Author Profiles</b></summary>

**Self-Service Profile:**
- Click user avatar → Edit Profile
- Upload avatar (auto-cropped)
- Write bio (Markdown supported)
- Update password

**Author Bio Component:**
```blade
{{-- Full bio box --}}
<x-blogr::author-bio :author="$post->user" />

{{-- Compact inline --}}
<x-blogr::author-bio :author="$post->user" :compact="true" />
```

**Configuration:**
```php
'author_profile' => ['enabled' => true],
'author_bio' => [
    'enabled' => true,
    'position' => 'bottom', // top, bottom, both
    'compact' => false,
],
```

</details>

### Advanced Features

<details>
<summary><b>🔍 SEO Configuration</b></summary>

**Per-Post SEO:**
- Meta title & description
- Keywords
- Custom OG image
- Auto-generated Schema.org markup

**Global SEO:**
```php
'seo' => [
    'site_name' => env('APP_NAME'),
    'default_title' => 'Blog',
    'twitter_handle' => '@yourhandle',
    'og' => [
        'type' => 'website',
        'image' => '/images/og-default.jpg',
    ],
    'structured_data' => [
        'enabled' => true,
        'organization' => [
            'name' => 'My Blog',
            'logo' => 'https://yoursite.com/logo.png',
        ],
    ],
],
```

</details>

<details>
<summary><b>📊 RSS Feeds</b></summary>

**Available Feeds:**
- Main: `/{locale}/blog/feed`
- Category: `/{locale}/blog/feed/category/{slug}`
- Tag: `/{locale}/blog/feed/tag/{slug}`

**Configuration:**
```php
'rss' => [
    'enabled' => true,
    'limit' => 20,
    'cache_ttl' => 3600, // 1 hour
],
```

</details>

<details>
<summary><b>🎨 Theming</b></summary>

**CSS Variables:**
```css
:root {
    --blogr-primary: #FA2C36;
    --blogr-category: #3B82F6;
    --blogr-tag: #10B981;
}
```

**Dark Mode:**
- Auto-detection via system preference
- Manual toggle (light/dark/auto)
- Configured via Alpine.js component

**Customization Points:**
- Card colors & borders
- TOC positioning (center, left, right sidebar)
- Publication date display
- Tag positioning (top/bottom)
- Heading permalinks (symbol, spacing, visibility)

</details>

---

## 🧪 Testing

Blogr is battle-tested with **680+ tests** and **1900+ assertions**:

```bash
cd vendor/happytodev/blogr
./vendor/bin/pest --parallel

# Test coverage
Tests:  57 skipped, 645 passed (1866 assertions)
```

**Test Coverage:**
- ✅ Import/Export with media files
- ✅ Multilingual translations
- ✅ Blog series relationships
- ✅ SEO meta tags & Schema.org
- ✅ Author profiles & permissions
- ✅ RSS feed generation
- ✅ Database schema integrity

---

## 🗺️ Roadmap

### 🎯 RC1 (November 2025)

- [x] **Import/Export system** with media backup
- [x] **CMS Page Builder** – Create static pages (About, Contact, etc.) with block system ✨ NEW
- [ ] **Clickable titles** in RecentBlogPosts widget
- [ ] **Public blog link** in admin panel
- [ ] **Theme presets** (predefined color schemes)
- [ ] **Enhanced tables** (sortable, filterable)
- [ ] **Writer notifications** for admins
- [ ] **Comprehensive testing** for v1 release

### ✅ Beta 3 (Completed - September 2025)

- [x] Full multilingual support
- [x] Blog series feature
- [x] Writer role with permissions
- [x] RSS feeds (global, category, tag)
- [x] Configurable TOC positioning
- [x] Theme system with dark mode
- [x] Author profiles & bios
- [x] Customizable permalinks
- [x] Language switcher component
- [x] Footer & navigation settings

### ✅ Beta 2 (Completed - September 2025)

- [x] SEO fields (meta, OG, Schema.org)
- [x] Scheduled publishing
- [x] Quick publish toggle
- [x] Auto-generated TOC
- [x] Reading time calculation
- [x] Dashboard widgets
- [x] Settings page with tabs
- [x] Tutorial content seeder

---

## 🤝 Support

<div align="center">

### Need Help?

[📖 Full Documentation](https://github.com/happytodev/blogr/wiki) • [🐛 Report Bug](https://github.com/happytodev/blogr/issues) • [💡 Request Feature](https://github.com/happytodev/blogr/issues/new)

### Love Blogr?

If this package saves you time, consider:

[![GitHub Sponsors](https://img.shields.io/badge/Sponsor-❤-pink?style=for-the-badge&logo=github)](https://github.com/sponsors/happytodev)
[![Star on GitHub](https://img.shields.io/github/stars/happytodev/blogr?style=for-the-badge&logo=github)](https://github.com/happytodev/blogr/stargazers)

</div>

---

## 📄 License

**MIT License** – See [LICENSE.md](LICENSE.md) for details.

## 👏 Credits

Created with ❤️ by [Frédéric Blanc](https://github.com/happytodev)

**Contributors:**
- [All Contributors](../../contributors)

**Special Thanks:**
- FilamentPHP team for the amazing framework
- Laravel community for continuous inspiration
- All users providing feedback and bug reports

---

<div align="center">

**[⬆ Back to Top](#-blogr--the-ultimate-filamentphp-blog-plugin)**

Made with ❤️ using Laravel & FilamentPHP

</div>
