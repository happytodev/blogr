<div align="center">

# Blogr — Multilingual Blog Plugin for FilamentPHP

[![Latest Version](https://img.shields.io/packagist/v/happytodev/blogr.svg?style=flat-square)](https://packagist.org/packages/happytodev/blogr)
[![Tests](https://img.shields.io/github/actions/workflow/status/happytodev/blogr/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/happytodev/blogr/actions?query=workflow%3Arun-tests+branch%3Amain)
[![PHP Version](https://img.shields.io/packagist/php-v/happytodev/blogr?style=flat-square)](https://packagist.org/packages/happytodev/blogr)
[![Downloads](https://img.shields.io/packagist/dt/happytodev/blogr.svg?style=flat-square)](https://packagist.org/packages/happytodev/blogr)
[![GitHub Stars](https://img.shields.io/github/stars/happytodev/blogr?style=flat-square)](https://github.com/happytodev/blogr)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](https://opensource.org/licenses/MIT)
[![WCAG 2.2 A](https://img.shields.io/badge/WCAG-2.2%20A%20(partial)-blue?style=flat-square)](https://www.w3.org/TR/WCAG22/)

![Blogr Banner](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/blogr.webp)

**A production-ready, multilingual blog and CMS system for Laravel & FilamentPHP v4.**

[Features](#-key-features) • [Installation](#-quick-start) • [Documentation](#-documentation) • [Screenshots](#-screenshots) • [Support](#-support)

</div>

---

## ✨ Overview

Blogr turns your Laravel application into a full-featured blogging platform. Built as a FilamentPHP v4 plugin, it provides a translation-first content engine, a visual CMS page builder, role-based authoring, SEO tools, analytics integration, and a modern frontend with dark mode and theme presets — all configurable from a single admin settings page.

### Why Blogr?

- 🌍 **Translation-First** — Posts, series, categories, tags, CMS pages, and author bios are fully translatable.
- 🧱 **Visual CMS Builder** — Build static pages with 23 block types, 7 templates, and gradient-aware transitions.
- 🎨 **Theme System** — Light/dark/auto mode, 5 color presets, 16+ configurable colors, custom fonts.
- 🔌 **Extensible** — Plugin architecture via `BlogrExtension` for third-party packages.
- 🔍 **SEO Ready** — Meta tags, Open Graph, Twitter Cards, JSON-LD, XML sitemaps, RSS feeds, hreflang.
- 📊 **Dashboard Widgets** — 9 widgets including stats, charts, scheduled posts, SEO checklist, and more.
- 🧪 **Battle Tested** — 1,366+ automated tests covering every major feature.
- ♿ **Accessible** — WCAG 2.2 Level A partial compliance: skip navigation, keyboard-operable galleries and carousels, form labels, and screen-reader-friendly ARIA attributes (Level AA in progress).

---

## 🎯 Key Features

<table>
<tr>
<td width="50%">

### ✍️ Blog Engine
- Translation-first architecture (main entity + translation tables)
- Markdown content with Prism.js syntax highlighting
- Video embeds (YouTube, Vimeo, Dailymotion)
- Post scheduling (draft / scheduled / published)
- Categories and tags with inline creation
- Reading time calculation
- Auto-generated table of contents
- Heading permalinks with copy-to-clipboard
- TL;DR summaries
- Featured images with fallback

### 📚 Blog Series
- Group posts into numbered series
- Automatic prev/next navigation
- Custom or auto position ordering
- Featured series highlighting
- Multiple authors per series
- Translated slugs

### 🌍 Multilingual
- Built-in support for 24+ locales
- Localized routes (`/{locale}/blog/...`)
- Locale auto-detection from published content
- Locale disable/restrict per language
- Language switcher component
- Hreflang SEO tags

</td>
<td width="50%">

### 📄 CMS Pages
- 7 templates: Default, Landing, Contact, About, Pricing, FAQ, Custom
- 23 block types (see [CMS Blocks](#-cms-blocks))
- Block hide/show toggle
- Per-page version history and drafts
- Preview mode with signed URL
- Anti-collision reserved slugs
- Set any page as homepage

### 🎨 Theming & UI
- 5 color presets (Magenta, Ocean, Emerald, Sunset, Slate)
- 16+ configurable color pickers
- CSS custom properties
- Back-to-top button
- Mega menu and navigation builder
- Sticky navigation, logo, footer
- 9 social link slots

### 🔍 SEO & Syndication
- Meta title/description/keywords per post/page
- Open Graph and Twitter Cards
- Schema.org Article / Organization / BreadcrumbList
- XML sitemap (`/sitemap.xml`)
- RSS feeds (main, category, tag)
- Canonical URLs and robots meta

### ⚙️ Admin Experience
- Filament v4 native integration
- Global search across posts, pages, and users
- Sortable tables with filters and toggleable columns
- Admin notifications when writers save posts
- Centralized settings page with 85+ fields
- Auto-save for posts and CMS pages

</td>
</tr>
</table>

---

## 📸 Screenshots

<div align="center">

**Admin Dashboard**
![Blogr Dashboard](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/image-5.png)

**Post Editor**
![Blogr Post Editor](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/image-3.png)

**Frontend Blog Home**
![Blogr Home](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/blogr-home.png)

</div>

> More screenshots are available in the [`.github/images`](https://github.com/happytodev/blogr/tree/main/.github/images) folder on GitHub.

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.3+
- Laravel 12.x
- FilamentPHP v4.x
- Composer
- Node.js 18+ and npm

### Install in a fresh Laravel + Filament project

```bash
# 1. Create a Laravel project
laravel new my-blog
cd my-blog

# 2. Install FilamentPHP
composer require filament/filament
php artisan filament:install --panels

# 3. Create an admin user
php artisan make:filament-user

# 4. Install Blogr
composer require happytodev/blogr
php artisan blogr:install
```

The installer will ask a few questions (CMS enabled, homepage type, admin path, theme setup, tutorials, demo pages) and then publish configs, run migrations, configure your User model, install frontend assets, and build everything.

### After installation

1. Log in at `/admin` (or your configured admin path).
2. Go to **Blog Posts → New Post** to create content.
3. Visit **Blogr Settings** to customize appearance, SEO, navigation, and analytics.
4. View your blog at `/blog` or `/` if you set the blog as homepage.

For a detailed walkthrough, see [INSTALL.md](INSTALL.md).

---

## 🛠️ CLI Commands

Blogr ships with several Artisan commands:

| Command | Description |
|---------|-------------|
| `php artisan blogr:install` | Interactive installer (recommended) |
| `php artisan blogr:install-tutorials` | Install tutorial posts |
| `php artisan blogr:remove-tutorials` | Remove tutorial posts |
| `php artisan blogr:list-tutorials` | List available tutorials |
| `php artisan blogr:publish-demo-pages` | Publish Home + Contact demo CMS pages |
| `php artisan blogr:export` | Export posts, series, categories, tags, users, CMS pages |
| `php artisan blogr:import {file}` | Import from JSON or ZIP export |
| `php artisan blogr:sync-admin-path` | Sync the admin panel path after changing it |
| `php artisan blogr:migrate-translations` | Migrate legacy posts to translation-first schema |
| `php artisan blogr:install-user-management` | Install User resource stubs and permissions |

---

## ⚙️ Configuration Highlights

All settings are manageable from the **Blogr Settings** page in the admin panel, or directly in `config/blogr.php`.

### Routes and homepage

```php
'route' => [
    'prefix' => 'blog',          // Change to '' for homepage
    'frontend' => ['enabled' => true],
    'middleware' => ['web'],
],

'homepage' => [
    'type' => 'blog',            // 'blog' or 'cms'
],
```

### Locales

```php
'locales' => [
    'enabled' => true,
    'default' => 'en',
    'available' => ['en', 'fr'],
    'auto_detect' => false,
],
```

### CMS pages

```php
'cms' => [
    'enabled' => true,
    'prefix' => '',              // '', 'page', 'pages', etc.
    'templates' => [
        'default', 'landing', 'contact', 'about', 'pricing', 'faq', 'custom'
    ],
],
```

### SEO defaults

```php
'seo' => [
    'site_name' => ['en' => 'The blog', 'fr' => 'Le blog'],
    'default_title' => ['en' => 'Blog', 'fr' => 'Blog'],
    'twitter_handle' => '@yourhandle',
    'og' => [
        'image' => '/images/blogr.webp',
        'image_width' => 1200,
        'image_height' => 630,
    ],
    'structured_data' => [
        'enabled' => true,
        'organization' => [
            'name' => env('APP_NAME'),
            'url' => env('APP_URL'),
            'logo' => env('APP_URL').'/images/logo.png',
        ],
    ],
],
```

### Analytics

```php
'analytics' => [
    'enabled' => false,
    'provider' => null,          // 'google', 'plausible', 'umami', 'matomo'
    'google' => ['measurement_id' => 'G-XXXXXXXXXX'],
    'plausible' => ['domain' => 'yoursite.com'],
    'umami' => ['website_id' => null, 'src' => null],
    'matomo' => ['url' => null, 'site_id' => null],
],
```

---

## 📊 Dashboard Widgets

Nine widgets are available and can be enabled or disabled from **Settings → Dashboard**:

| Widget | Description |
|--------|-------------|
| `BlogStatsOverview` | Total, published, draft, scheduled posts + categories, tags, series, CMS pages |
| `RecentBlogPosts` | Latest posts with edit actions, filters, and column toggles |
| `ScheduledPosts` | Upcoming scheduled publications |
| `BlogPostsChart` | Post creations and publications over the last 12 months |
| `BlogReadingStats` | Reading time distribution (average, short, medium, long) |
| `CategoryPostsChart` | Doughnut chart of posts per category |
| `SeriesStatsOverview` | Series metrics overview |
| `WeeklyActivityChart` | Weekly publishing activity bar chart |
| `MissingSeoAlert` | SEO checklist alerting missing metadata |

---

## 🧩 CMS Blocks

The CMS page builder includes 23 block types:

**Content & Marketing**
- Hero Banner
- Hero Carousel
- Features Grid
- Call to Action
- Rich Content (Markdown)
- Blog Posts
- Blog Title

**Social Proof & Info**
- Testimonials
- Team Members
- Stats & Metrics
- Timeline
- FAQ Accordion

**Interactive & Media**
- Image Gallery
- Video Embed
- Map
- Contact Form
- Newsletter

**Decorative & Layout**
- Wave Separator
- Section Separator (diagonal)
- Transition: Clip Path
- Transition: Simple
- Transition: Animation

Each block supports 5 background types (none, solid, gradient, image, pattern), independent dark-mode settings, custom colors, and text shadows.

---

## Plugins

Blogr supports third-party plugins via the `BlogrExtension` interface. Plugins can register routes, Livewire components, custom link types, and settings pages.

```php
use Happytodev\Blogr\Contracts\BlogrExtension;
use Happytodev\Blogr\Services\ExtensionRegistry;

class MyPlugin implements BlogrExtension
{
    public function getId(): string { return 'my-plugin'; }
    public function getName(): string { return 'My Plugin'; }
    public function getDescription(): string { return 'What it does.'; }
    public function getVersion(): string { return '1.0.0'; }
    public function getAuthor(): string { return 'Your Name'; }
    public function getHomepage(): ?string { return null; }
    public function getDependencies(): array { return []; }
    public function getSettingsUrl(): ?string { return null; }
    public function registerExtension(ExtensionRegistry $registry): void {}
}
```

Register your plugin in your service provider:

```php
public function packageBooted(): void
{
    if ($this->app->has(ExtensionRegistry::class)) {
        $this->app->make(ExtensionRegistry::class)->register(new MyPlugin);
    }
}
```

| Plugin | Description | Repository |
|--------|-------------|------------|
| GDPR | Cookie consent, privacy policy, data export and erasure | [happytodev/blogr-gdpr](https://github.com/happytodev/blogr-gdpr) |
| Comments | Threaded comments with moderation, voting, anti-spam, and email notifications | [happytodev/blogr-comments](https://github.com/happytodev/blogr-comments) |
| Artist Portfolio | Artist portfolio with artwork management, portfolio pages, and commission showcase | [happytodev/blogr-artist](https://github.com/happytodev/blogr-artist) |

---

## 🛡️ Security

### Configurable admin path

Change the default `/admin` URL from **Settings → Admin Panel**, then run:

```bash
php artisan blogr:sync-admin-path
```

You can also set it via `.env`:

```env
BLOGR_ADMIN_PATH=backoffice
```

### Two-Factor Authentication

Add 2FA to your admin panel with [Filament Breezy](https://github.com/jeffgreco13/filament-breezy). Blogr provides an installer command:

```bash
php artisan blogr:install-breezy
```

See the Breezy documentation for full configuration details.

### Roles and permissions

Blogr uses Spatie Permission with two default roles:

- **Admin** — full access
- **Writer** — can create and edit own posts, cannot publish or manage other users

---

## 🧪 Testing

Blogr is tested with **1,266 Pest PHP tests** covering import/export, multilingual routing, CMS pages, blocks, SEO, widgets, permissions, and more.

```bash
cd vendor/happytodev/blogr
./vendor/bin/pest --parallel
```

CI runs on GitHub Actions with PHP 8.4, Laravel 12, and `prefer-stable`.

---

## 📚 Documentation

- [Installation Guide](INSTALL.md)
- [Feature Overview](docs/FEATURES_v100.md)
- [CMS Pages in Navigation](docs/CMS_PAGES_IN_NAVIGATION.md)
- [Theme Switcher](docs/THEME_SWITCHER.md)
- [RSS Feeds](docs/RSS_FEED.md)
- [Language Switcher Fix](docs/LANGUAGE_SWITCHER_CMS_PAGE_FIX.md)
- [Full docs folder](docs/)

---

## 🤝 Support

<div align="center">

### Need Help?

[📖 Full Documentation](https://github.com/happytodev/blogr#readme) • [🐛 Report Bug](https://github.com/happytodev/blogr/issues) • [💡 Request Feature](https://github.com/happytodev/blogr/issues/new)

### Love Blogr?

[![GitHub Sponsors](https://img.shields.io/badge/Sponsor-❤-pink?style=for-the-badge&logo=github)](https://github.com/sponsors/happytodev)
[![Star on GitHub](https://img.shields.io/github/stars/happytodev/blogr?style=for-the-badge&logo=github)](https://github.com/happytodev/blogr/stargazers)

</div>

---

## 📄 License

**MIT License** — See [LICENSE.md](LICENSE.md) for details.

Created with ❤️ by [Frédéric Blanc](https://github.com/happytodev) and the Laravel community.

---

<div align="center">

**[⬆ Back to Top](#blogr--multilingual-blog-plugin-for-filamentphp)**

Made with ❤️ using Laravel & FilamentPHP

</div>
