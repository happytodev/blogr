# Blogr Release Notes

## Versioning Scheme

Blogr follows [Semantic Versioning](https://semver.org). Major releases are aligned with major releases of **Laravel** and **FilamentPHP** — upgrading one generally requires upgrading the others.

Minor and patch releases never contain breaking changes. When requiring Blogr from your application, use a version constraint such as `^2.0`.

> **Named arguments** are not covered by backwards compatibility guarantees. Parameter names may change in future minor releases.

## Support Policy

Blogr adopts a **version-based** support policy:

- Only the **latest major version** receives bug fixes.
- The **previous major version** receives security fixes for **6 months** after the next major is released.
- Older versions are **end of life (EOL)**.

| Version | Range | Status | Bug Fixes | Security Fixes |
|---------|-------|--------|-----------|----------------|
| v1 | 1.0.0 – 1.32.0 | 🔴 EOL | ❌ | ✅ until Jan 7, 2027 |
| v2 | 2.0.0+ | 🟢 Active | ✅ | ✅ |
| v3 | (future) | ⚡ Upcoming | — | — |

---

## Version Compatibility

### Blogr Core

| Blogr | Laravel | Filament | Livewire | PHP | Status |
|-------|---------|----------|----------|-----|--------|
| v1.x | 12.x | v4 | 3.x | ^8.3 | 🔴 EOL |
| v2.x | 13.x | v5 | 4.x | ^8.3 | 🟢 Active |
| v3.x | 14.x | v6 | 5.x | ^8.4 | ⚡ Preview |

### Official Plugins

All first-party plugins follow Blogr's major version number. When upgrading Blogr to a new major, plugins must be upgraded in lockstep.

| Plugin | v1.x | v2.x | v3.x |
|--------|------|------|------|
| [blogr-comments](https://github.com/happytodev/blogr-comments) | ^1.1 | ^2.0 | ^3.0 |
| [blogr-gdpr](https://github.com/happytodev/blogr-gdpr) | ^1.5 | ^2.0 | ^3.0 |
| [blogr-artist](https://github.com/happytodev/blogr-artist) | ^1.0 | ^2.0 | ^3.0 |
| [blogr-docs](https://github.com/happytodev/blogr-docs) | ^1.10 | ^2.0 | ^3.0 |

### Additional Dependencies

| Dependency | v1.x | v2.x | v3.x |
|------------|------|------|------|
| spatie/laravel-permission | ^6.0 | ^8.0 | ~ |
| spatie/laravel-backup | ^9.0 | ^10.0 | ~ |
| spatie/laravel-package-tools | ^1.15 | ^1.15 | ~ |
| orchestra/testbench | ^10.11 | ^11.0 | ~ |
| pestphp/pest | ^3.x | ^4.0 | ~ |

---

## Blogr v2 — Laravel 13 / Filament v5

> Released July 2026 · [v2.0.0](https://github.com/happytodev/blogr/releases/tag/v2.0.0)

Blogr v2 is a **stack upgrade release**. It migrates the package from Laravel 12 / Filament v4 to Laravel 13 / Filament v5, picking up the latest framework improvements while maintaining full backward compatibility in the public API.

### Stack

- **Laravel 13** — first-party AI SDK, JSON:API resources, `PreventRequestForgery`, queue routing by class, expanded PHP attributes, vector/semantic search
- **Filament v5** — Livewire 4, instance-based panel providers, plain string icons, Schema components
- **Livewire 4** — `ComponentRegistry` removed, component name resolution via `Str::kebab()`
- **spatie/laravel-permission ^8.0** — upgraded from ^6.0

### Notable changes

- All filament packages pinned to `^5.0` (`filament/actions`, `filament/filament`, `filament/schemas`, `filament/support`, `filament/tables`)
- Livewire registration updated for Livewire 4 across core and all plugin packages
- Testbench upgraded to `^11.0`, Pest remains at `^4.0`

> **Note**: This section is a living document. New features shipped in v2.x releases will be documented here as they land.

---

## Blogr v1 — Laravel 12 / Filament v4

> Released June 2026 · v1.0.0 – v1.32.0

Blogr v1 introduced the full multilingual blog and CMS system for Laravel 12 and FilamentPHP v4.

### Translation-First Engine

- Architecture with dedicated translation tables for posts, categories, tags, series, and CMS pages
- 4 built-in locales (EN, FR, ES, DE), support for 24+ languages
- Locale auto-detection, disable, and restrict
- Dynamic locale fields — adding a locale requires no code changes

### CMS Page Builder

- 7 page templates (Default, Landing, Contact, About, Pricing, FAQ, Custom)
- 23 block types (Hero, Features, Testimonials, CTA, Gallery, FAQ, Team, Pricing, Content, Blog Posts, Stats, Timeline, Video, Newsletter, Map, Contact Form, Wave/Directional/Clip Path/Margin/Animation Transitions, Blog Title, Background Wrapper)
- 5 background types per block (none, solid, gradient, image, pattern) with dark mode variants
- Gradient-aware transition blocks with automatic color blending

### Filament Admin Panel

- 5 CRUD Resources (BlogPost, Category, Tag, BlogSeries, CmsPage)
- Translation repeater with inline management
- Role-based permissions (Admin / Writer)
- Centralized Settings page with 85+ fields across 7 tabs

### Frontend

- Responsive blog index, post detail, category/tag/author/series pages
- Markdown with Prism.js syntax highlighting and video embed support
- Dark mode, 5 color presets, 16+ configurable colors
- Mega menu, language switcher, theme switcher
- Table of Contents, heading permalinks, reading time, TL;DR
- WCAG 2.2 AA compliant

### Notable v1.x features (by release)

- **Callouts** (v1.29.0) — `:::tip` / `:::info` / `:::danger` / `:::caution` admonitions in Markdown with `CalloutMarkdownEditor` toolbar
- **Nav Link Types** (v1.30.0) — `LinkTypeRegistry` with plugin-resolvable link types, sub-menu CMS page support
- **Theme Switcher Dropdown** (v1.31.0) — text labels, translated tooltips, system mode icon
- **Image Lightbox** (v1.32.0) — full-screen Alpine.js overlay for Markdown images

---

## Blogr v3 — Future (Laravel 14 / Filament v6)

*This section will be filled when development begins.*

Expected stack: Laravel 14.x, Filament v6, Livewire 5.x, PHP 8.4+.

Plugins will follow the same major version bump pattern: `^3.0` for all first-party packages.
