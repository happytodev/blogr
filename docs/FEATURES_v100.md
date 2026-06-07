# Blogr v1.0.0 — Complete Feature Overview

**Blogr** is a full-featured, multilingual blog system built as a plugin for **FilamentPHP v4** and **Laravel 12**. Whether you are a solo writer building a personal blog, an editor managing a multi-author publication, or a developer looking for a drop-in content solution for your Laravel app, Blogr gives you everything you need — and nothing you don't.

Out of the box, Blogr delivers a **translation-first architecture** (English, French, Spanish, German), a **visual CMS page builder** with 23 block types, a **rich Filament admin panel**, a sleek **frontend** with dark mode and theme presets, and a complete toolchain of **CLI commands**, **import/export services**, **SEO metadata**, **RSS feeds**, **XML sitemaps**, and **analytics integration** — all fully configurable from a single settings page.

It is tested daily with **over 920 passing tests**, runs on a modern **PHP 8.3+ / Laravel 12 / Filament v4** stack, and follows the same security and quality standards you expect from a production package.

Below is the complete, exhaustive list of every feature shipped in this first stable release.

---

## Table of Contents

1. [Flagship Features](#-flagship-features)
2. [Frontend](#-frontend)
3. [CMS Blocks](#-cms-blocks-23-types)
4. [Backend Services & Tools](#-backend-services--tools)
5. [Dashboard Widgets](#-dashboard-widgets-6)
6. [SEO & Metadata](#-seo--metadata)
7. [Theming & Customization](#-theming--customization)
8. [Internationalization](#-internationalization)
9. [Blog Series](#-blog-series)
10. [Testing & Quality](#-testing--quality)
11. [Tech Stack](#-tech-stack)

---

## 🏆 Flagship Features

### Translation-First Blog Engine
- **Architecture**: Translations are first-class entities — the main table stores only non-translatable fields (IDs, timestamps, user_id). Translation tables hold all localized content (title, slug, content, SEO, photos).
- **4 built-in locales**: English, French, Spanish, German
- **Auto-detection** of available locales from published content
- **Locale disable**: individually disable locales (returns 404 on frontend)
- **Locale restrict**: limit auto-detected locales to a curated list
- **Dynamic locale fields**: 4 arrays keyed by locale instead of 16 hardcoded properties — adding a new locale requires no code changes
- **Pivot tables**: `blog_post_translation_category`, `blog_post_translation_tag` — link translations, not main entities
- **ClearsLocaleCache trait**: auto-flushes locale cache on translation save/delete

### CMS Static Pages
- Conditional module (enable/disable in config)
- **7 page templates**: Default, Landing, Contact, About, Pricing, FAQ, Custom
- **23 CMS block types** with dynamic block builder (see [CMS Blocks section](#-cms-blocks-23-types))
- **Anti-collision slugs**: reserved slugs (blog, feed, author, category, tag, series, admin, etc.)
- **CMS page backup/restore**: individual page-level backup management
- **Demo pages command**: `blogr:publish-demo-pages` — publishes Home + Contact pages
- **Import/Export**: single CMS page export/import with media, URL rewriting, conflict strategies

### FilamentPHP v4 Admin Panel
- **5 CRUD Resources**:
  - `BlogPostResource` — posts with inline translation management
  - `CategoryResource` / `TagResource` — multilingual taxonomies
  - `BlogSeriesResource` — content series
  - `CmsPageResource` — CMS pages (conditional)
- **Blog Post Form**:
  - Repeater of translations (locale, title, slug, photo, Markdown content, TL;DR, SEO fields)
  - Category + tags with inline creation
  - Series selection with custom position
  - Per-post TOC visibility toggle
  - Publication status + scheduling (admin only)
  - Author selection (admin only)
- **Tables with toggleable columns, filters, sorting**:
  - Posts: title, slug, photo, author, category, locales, series, tags, status, dates
  - Categories/Tags: name, slug, post count
  - Series: title, slug, status, dates
  - Hidden-by-default columns: photo, locales, series, tags
- **Global Search**: searchable by post/page translations across all entities
- **Permissions**: Admin (full access) + Writer (own posts only, no publish)
- **Navigation**: all resources grouped under "Blogr" navigation group

### Centralized Settings Page (`BlogrSettings`)
**85+ fields across 7 tabs:**

- **General tab** — Posts per page, route prefix, frontend toggle, primary color, homepage type (blog/CMS), CMS enabled/prefix, contact email, reading time (speed + per-locale text), series settings, multilingual (enabled/default/auto-detect/available/disabled), sitemap toggle, version display

- **SEO tab** — Per-locale site name, default title/description/keywords, Twitter handle, Facebook App ID, Structured Data (organization name/url/logo)

- **Appearance tab** — Theme (light/dark/auto), **5 color presets** (Magenta, Ocean, Emerald, Sunset, Slate), **16 color pickers** (primary/hover/category/tag/author — light + dark variants), default post image, publication date (master toggle + card + article toggles), tags position, language switcher toggle, back-to-top (enabled/shape/color)

- **Content tab** — TOC (enabled/strict mode/position/collapsible), heading permalinks (symbol/spacing/visibility), author bio (enabled/position/compact), author display (pseudo/avatar/series authors limit)

- **Navigation tab** — Nav bar (enabled/sticky/logo/display/language switcher/theme switcher), **menu items Repeater** (external URL/blog home/category/CMS page/mega menu, per-locale labels, sub-menus, icons, target), footer (enabled/text/9 social links)

- **Analytics tab** — Toggle + provider selection: Google Analytics, Plausible, Umami, Matomo (each with provider-specific fields)

- **Backup tab** — Export JSON, Export ZIP + media, Import JSON/ZIP (with overwrite option + default author selector), CLI command reference

---

## 🖥️ Frontend

### Blog Pages
- **Blog Index** (homepage or `/blog`) — Responsive 3-column grid, featured series section, pagination, empty state with SVG illustration
- **Post Detail** — Title, locale-aware date, reading time, category badge, author info, featured image (with gradient fallback + default image), TL;DR, Markdown content with Prism.js syntax highlighting, Table of Contents (center/left/right/mobile), heading permalinks with copy-to-clipboard, series navigation (prev/next), tags (top or bottom), author bio box, translation warning, language indicator
- **Category / Tag pages** — Filtered post grid with header, pagination
- **Series Index** — All series listing, responsive grid, featured badge
- **Series Detail** — Breadcrumb, header with image, authors, posts count, numbered series list, posts grid
- **Author Profile** — Avatar (or initials fallback), name, stats, markdown-rendered bio, paginated posts grid

### Navigation & Layout
- **Mega menu** with hover/click (Alpine.js), responsive hamburger mobile menu
- **Menu items**: external URL / blog home / category / CMS page, per-locale labels, sub-menus, icons
- **Auto-add blog link** when CMS is the homepage
- **Sticky navigation** option
- **Logo**: text / image / both
- **Language Switcher**: flag emoji dropdown for 24+ locales
- **Theme Switcher**: light / auto / dark with localStorage persistence + system preference detection + flash prevention
- **Footer**: customizable text (HTML), 9 social networks (Twitter/X, GitHub, LinkedIn, Facebook, Bluesky, YouTube, Instagram, TikTok, Mastodon) with SVG icons, "Powered by Blogr" link

### Theme & UI Components
- **Back-to-top button**: floating, circle/square, custom color, appears after 200px scroll
- **Breadcrumb**: auto-generated (Home > Blog > Series > Post), Schema.org BreadcrumbList JSON-LD
- **Blog Post Card**: 3-column grid, image hover zoom, category badge, reading time badge, 2-line title clamp, 3-line TL;DR clamp, max 3 tags + overflow count, "Read more" link
- **Series Card**: image hover zoom, posts count badge, featured badge, authors, pagination in index
- **Series Badge**: inline on posts, shows part/total number, featured star
- **Series List**: gradient-styled numbered list, current post highlighted, "View all series" link
- **Series Navigation**: previous/next post grid, "Part of a series" header, disabled state
- **Series Authors**: stacked avatar circles with configurable limit and size, initials fallback
- **Author Info**: avatar with initials fallback, link to profile, size variants
- **Author Bio**: compact (inline border) or full (gradient background, avatar, markdown-rendered bio, "View all posts" button, email icon)
- **Clock Icon**: inline SVG clock for reading time display
- **Language Indicator**: badge showing current locale
- **Post Language Indicator**: shows post language if different from current locale
- **Translation Warning**: banner when viewing a non-primary translation
- **Hreflang Tags**: alternate URLs for all available locales

### Content Display
- **Markdown rendering**: CommonMark with video embed extension (YouTube, Vimeo, Dailymotion → responsive iframes)
- **Syntax highlighting**: Prism.js with autoloader, toolbar, copy-to-clipboard, line-numbers plugins
- **TL;DR**: 3-line clamped excerpt, optional display
- **Reading time**: auto-calculated from content, per-translation storage, locale-aware text format
- **Publication date**: toggleable on cards and articles, locale-aware via Carbon isoFormat
- **Empty states**: "No posts yet" / "Check back soon" with illustration on every listing page
- **Table of Contents**: collapsible, center/left/right sidebar positions, mobile dropdown, localStorage persistence
- **Heading Permalinks**: anchor links on every heading, configurable symbol/spacing/visibility, copy-to-clipboard with fallback

### Content Syndication
- **RSS Feed** (RSS 2.0): per-locale, filterable by category/tag, DC creator, Atom link
- **XML Sitemap**: auto-generated `/sitemap.xml` — posts, categories, tags, series, CMS pages, proper priority hierarchy, respects published/draft state

---

## 🧩 CMS Blocks (23 types)

- **Hero** — Full-width banner with title, subtitle, CTA button, background image, dynamic link resolution
- **Features** — Feature grid with icons, titles, descriptions
- **Testimonials** — Client testimonials with avatar, name, content
- **CTA** — Call-to-action with title, description, button
- **Gallery** — Image grid
- **FAQ** — Accordion-style questions/answers
- **Team** — Team members with photo, role, bio
- **Pricing** — Pricing cards with feature list
- **Content** — Freeform content area with Markdown
- **Blog Posts** — Dynamic list of recent posts
- **Stats** — Animated counters (IntersectionObserver)
- **Timeline** — Step-by-step timeline
- **Video** — Responsive embed (YouTube/Vimeo/Dailymotion)
- **Newsletter** — Email signup block
- **Map** — Map with dynamic bounding box, lazy loading
- **Contact Form** — Inline contact form
- **Wave Separator** — SVG wave with intelligent color blending from adjacent block gradients
- **Diagonal Transition** — Diagonal shape divider, dark mode support
- **Clip Path Transition** — Polygon clip-path divider
- **Margin Transition** — Empty spacer with gradient color extraction
- **Animation Transition** — Configurable height + animation type
- **Blog Title** — Customizable blog title display
- **Background Wrapper** — Unified wrapper — 5 background types (none/solid/gradient/image/pattern SVG), dark mode variants, text shadows

### Block Background System
- **5 background types**: none, solid color (with opacity), gradient (8 directions), image (with size/position), pattern (dots/grid/stripes/waves/circles/zigzag/cross/hexagons — all SVG-generated)
- **Separate dark mode configs** for every background type
- **Text shadow** levels: light/medium/heavy via Tailwind `drop-shadow`
- **Custom heading/text/subtitle colors** with dark mode variants via injected `<style>` tags

### Block Transition System
- **Adjacent block detection**: the `blocks-renderer` passes `previousBlock`/`nextBlock` context to transition blocks
- **WaveSeparatorService**: calculates SVG wave paths, extracts and blends colors from adjacent block gradients
- **ColorHelper**: color class manipulation, dark mode generation, hex/RGB/HSL conversions, brightness adjustment, format conversion
- **LinkResolver**: converts CMS block link references (external/blog/category/cms_page) into actual URLs

---

## 🔧 Backend Services & Tools

### Import / Export
- **BlogrExportService** — Full data export: posts, series, categories, tags, users, translations, CMS pages, pivot tables, media files. Output: JSON or ZIP.
- **BlogrImportService** — Full data import from JSON/ZIP with conflict resolution (skip/overwrite), ID preservation, media restoration
- **CmsPageImportExportService** — Single CMS page export/import with media, URL rewriting, conflict strategies
- **CmsPageBackupService** — Backup/restore/delete/clean old backups for individual CMS pages

### Security & Permissions
- **Spatie Permission** integration with **Admin** and **Writer** roles
- **BlogPostPolicy**: Admin = full access; Writer = own posts only (no publish)
- **UserPolicy**: Admin only
- **Auto-assign admin role** to first user during installation
- **Filament panel authorization** with domain-based access configuration

### Notifications
- **PostSavedByWriter**: database + mail notification to all admins when a writer creates/saves a post
- **SendPostNotificationJob**: queued job dispatched on post creation

### CLI Commands (10)
- `php artisan blogr` — Meta-command: install / remove / list-tutorials, prints feature list
- `php artisan blogr:install` — Interactive installer (12+ steps): CMS config, publishing, migrations, storage link, User model config, roles/permissions, test users, tutorials, series, widgets, backup, npm, CSS, AdminPanelProvider, route cleanup
- `php artisan blogr:install-tutorials` — Install tutorial posts from `resources/tutorials/`
- `php artisan blogr:remove-tutorials` — Permanently delete tutorial posts and category
- `php artisan blogr:list-tutorials` — List available tutorial posts
- `php artisan blogr:publish-demo-pages` — Publish demo CMS pages (Home + Contact in EN/FR)
- `php artisan blogr:export` — Export all blog data to JSON or ZIP
- `php artisan blogr:import` — Import blog data from JSON or ZIP
- `php artisan blogr:install-user-management` — Install UserResource stubs, permissions, configure User model
- `php artisan blogr:migrate-translations` — One-time migration: create translation entries from legacy non-translated posts

### Observers
- **BlogPostObserver**: handles post-creation notification dispatch
- **BlogSeriesTranslationObserver**: auto-updates series slug from first translation title

### Middleware
- **SetLocale**: extracts locale from URL segment 1, sets app locale + Carbon locale, stores in request attributes

---

## 📊 Dashboard Widgets (6)

- **BlogStatsOverview** — 6 stat cards: Total / Published / Draft / Scheduled posts + Categories + Tags
- **RecentBlogPosts** — Table of latest 10 posts with title, category (badge), author, status (badge), dates
- **ScheduledPosts** — Upcoming scheduled publications sorted by date
- **BlogPostsChart** — Line chart: post creations over the last 12 months
- **BlogReadingStats** — Reading time stats: average, short (<1min), medium (1-5min), long (>5min)
- **QuickVisitSite** — One-click button to visit the frontend homepage

---

## 🌍 SEO & Metadata

- **SEOHelper**: generates SEO metadata for blog listing pages and posts — title, description, keywords, canonical URL, Open Graph, Twitter Card, JSON-LD
- **ConfigHelper**: locale-aware config access with fallback chains for all SEO fields
- **Open Graph**: og:type, og:url, og:title, og:description, og:image (with dimensions), og:site_name
- **Twitter Cards**: summary_large_image, twitter:creator, twitter:site, twitter:title, twitter:description, twitter:image
- **JSON-LD Structured Data**: Article schema (headline, image, datePublished, dateModified, author, publisher), Organization schema, BreadcrumbList schema
- **Heading Permalinks**: anchor links with configurable symbol (# / § / ¶), spacing (none/before/after/both), visibility (always/hover)
- **Canonical URLs** on every page
- **XML Sitemap**: auto-generated, covers posts, categories, tags, series, CMS pages
- **RSS Feed**: syndication with locale filtering
- **Hreflang tags**: alternate language URLs for all available locales
- **Meta keywords**: per-post and per-CMS-page, plus global defaults
- **Robots meta**: control indexing

---

## 🎨 Theming & Customization

- **5 color presets**: Magenta (default), Ocean Blue, Emerald Green, Sunset Orange, Slate (minimal)
- **16 individual color pickers**: primary (light + dark + hover + hover dark), category background, tag background, author background — all with light and dark variants
- **Presets auto-fill** all color fields client-side (no server round-trip) via `wire:model.defer` + JavaScript native value setter
- **Full dark mode**: every component, block, and page has dark variants
- **CSS variable system**: primary, hover, category, tag, author colors exposed as CSS custom properties
- **Blog card backgrounds**: light and dark variants for post cards and series cards
- **Back-to-top button**: circle or square shape, custom color or primary
- **Navigation**: sticky, logo (text/image/both), language switcher, theme switcher
- **Footer**: customizable HTML text, 9 social link slots with SVG icons
- **Settings tab URL persistence**: tabs are shareable/bookmarkable via `?tab=appearance::tab` using Filament's native `persistTabInQueryString()`

---

## 🌐 Internationalization

- **4 built-in languages**: English, French, Spanish, German
- **12 translation files**: `blogr.php`, `resources.php`, `navigation.php`, `locales.php` per language
- **24+ locale support**: flag emojis, native language names in the language switcher
- **Locale-aware dates**: formatted via Carbon `isoFormat` with locale setting
- **Reading time texts**: customizable per locale with `{time}` placeholder
- **All navigation labels**: multilingual (blog, CMS, series, settings)
- **Menu items**: per-locale labels for each navigation item
- **SEO fields**: site name, default title, description, keywords — all per locale
- **Translation warning**: banner on posts viewed in non-primary locale
- **Post language indicator**: shows available languages for each post
- **Locale auto-detect**: discovers available locales from published content
- **Locale restrict**: filter auto-detected locales to a curated list
- **Locale disable**: return 404 on frontend for disabled locales

---

## 📚 Blog Series

- Group posts into numbered series (e.g., multi-part tutorials)
- **Auto position**: "At end" or "At beginning" within the series
- **Custom position**: manual numeric ordering
- **Series navigation**: previous/next post at the bottom of each article
- **Featured series**: highlight on the blog index page
- **Series detail page**: breadcrumb, header with image, authors, numbered post list
- **Series badge**: inline on posts showing "Part X of Y"
- **Series list component**: gradient-styled numbered list
- **Translated slugs** per series for SEO-friendly URLs
- **Multiple authors**: per-series with configurable display limit
- **Series card**: image, description, authors, posts count

---

## 🧪 Testing & Quality

- **922 tests** (2,737 assertions) — consistently passing
- **56 skipped tests** (Playwright browser tests run separately)
- **Parallel execution**: 14 processes, ~6s runtime
- **Pest PHP 4.0** with architecture tests (forbids `dd()`, `dump()`, `ray()`)
- **In-memory SQLite** for all tests
- **Test base classes**:
  - `TestCase` — standard (locales disabled)
  - `LocalizedTestCase` — locales enabled
  - `CmsTestCase` — CMS + homepage enabled
  - `CmsWithLocalesTestCase`, `CmsWithPrefixTestCase`, `LocalizedCmsTestCase`
- **Playwright** browser tests (Chromium)
- **GitHub Actions CI**: PHP 8.4, Laravel 12, `prefer-stable`, Playwright browsers
- **Random execution order** to catch ordering dependencies

---

## 📦 Tech Stack

- **Language** — PHP 8.3+
- **Framework** — Laravel 12.x
- **Admin Panel** — FilamentPHP v4 (Schemas, not Forms)
- **Testing** — Pest PHP 4.0, Testbench 10.x
- **Permissions** — Spatie Permission
- **Backup** — Spatie Laravel Backup (optional)
- **CSS** — Tailwind CSS 4
- **Build** — Vite
- **Browser Testing** — Playwright (Chromium)
- **Syntax Highlighting** — Prism.js
- **Markdown** — League CommonMark + VideoEmbed extension
- **Frontend** — Alpine.js, Livewire v3
- **Database** — MySQL / SQLite (in-memory for tests)
- **Icons** — Blade Heroicons

---

*Generated for Blogr v1.0.0 release. Last updated: 2026-06-07*
