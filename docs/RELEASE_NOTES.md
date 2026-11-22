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

### v2.7.6 — 2026-11-22

A post slug is now unique **per locale**, which is what the route always implied.

- The table declared `unique('slug')` on `blog_post_translations`, so the English and the French version
  of one article could not share a slug: the second one was refused by the admin with "already taken",
  and the only way out was to invent different slugs for two URLs that were already distinct.
  Categories, tags and CMS pages were already unique per locale. A migration replaces the index with
  `unique(['locale', 'slug'])`.
- The admin slug rule is scoped to the item's locale: the same slug in another locale is accepted, a
  duplicate inside the same locale is still refused.
- `BlogController::show()` fell back on "find by slug alone" when the requested locale had no match. With
  two locales able to own the same slug that fallback would answer with another language's article under
  this locale; it now only applies when the post has no translation in the requested locale, and not at
  all when locales are out of the URL (nothing then claims a language).

Upgrading runs one migration: `php artisan migrate`.

### v2.7.5 — 2026-11-22

A type-safety pass over the package; what it turned up is fixed here.

- The sitemap and the SEO block dereferenced `published_at` / `updated_at` without a null check — a post
  without a publish date answered 500 on `/sitemap.xml` instead of being skipped.
- `calculateReadingTime()` stored `ceil()`'s float in an integer column.
- `SettingsRepository` read the console argv without checking it exists, under php-fpm it does not, and
  the one place that reads it is the `config:cache` guard.
- The admin draft import read offsets of a value it had not checked.
- The CMS block merge reads `blocks` on the translation, where the column moved.

Every model now documents its columns and relations, so PHPStan level 9 can follow a relation out of a
controller; the baseline is regenerated and both `pint` and `phpstan` pass. No migration, no
configuration change.

### v2.7.4 — 2026-11-22

Saving a new post right after the auto-save fired no longer fails.

The auto-save wrote a placeholder post *and* a partial translation — locale, title, slug — for the
first item of the translations repeater. The repeater had been mounted when no record existed, so it
held every item as new and knew nothing about that row: on save it inserted the same locale again.
The `(blog_post_id, locale)` unique index refused it, and the slug rule refused a slug the post
already carried, so the user faced a 500 on a post that had in fact been created, published and left
without what they had typed.

- The auto-save no longer writes a translation: the form owns them and writes them once. A draft now
  shows as "Untitled" in the admin list until it is saved.
- Drafts started before this version are repaired on save: the rows the repeater does not hold are
  dropped before it inserts.

No migration, no configuration change. `composer require happytodev/blogr:^2.7.4`, then
`php artisan view:clear && php artisan optimize:clear`.

### v2.7.0 — 2026-11-21

**A post that belongs to a series is served under that series.**
`/{locale}/{prefix}/series/{seriesSlug}/{postSlug}` is the canonical address of a series post:
the `canonical` tag, the `hreflang` alternates, the language switcher, the sitemap, the RSS feed,
the blog cards, the series page, the previous/next navigation, the author page and the admin
"view" link all use it. The former flat URL (`/{locale}/{prefix}/{postSlug}`) answers with a
**301** to the nested one, and a post that is not part of a published series keeps its flat URL
unchanged. `PostUrlHelper::forPost()` is the single way to build a post URL.

Sites that publish their own views (`resources/views/vendor/blogr/…`) keep generating flat post
URLs until those overrides are updated: the links still work, but go through a redirect. Update
the overrides or run `vendor:publish --tag=blogr-views --force`.

**Saving a post without a series no longer breaks the admin.** The series position select kept its
`auto-bottom` default even when the series field was hidden, and `series_position` is an integer
column: MySQL rejected the word (error 1366) behind a generic "Error while loading page". Both
create and edit hooks now store nothing but a number or nothing.

### v2.6.1 — 2026-10-14

**Stored settings are no longer frozen into the compiled configuration.** `php artisan config:cache`
boots the application before dumping the configuration, so the overrides stored in the database were
baked into `bootstrap/cache/config.php` — the compiled file then outranked the table, and deleting a
setting (or using *reset to the file values*) had no effect until the next cache rebuild. They are now
applied for real requests only, read once per request without a cache entry, and the reset clears the
compiled configuration too.

### v2.6.0 — 2026-10-14

**Settings are stored in the database instead of the config file.** Saving the Settings page rewrote
`config/blogr.php` — and regenerated it from an array, dropping every comment in it. On a host that
deploys by release directories, that file is replaced by the next deployment, so an admin save held
only until the following release and then vanished. Every value now goes into a `blogr_settings` table
and is applied on top of the installed config, which makes a save durable across deployments,
rollbacks and `config:clear`, while the committed file keeps its role of defaults. The settings page
reports how many values are stored and resets them to the file values in one click; `null` values are
never stored, and the previous behaviour is kept while the table is not migrated.

Run `php artisan migrate` after updating.

### v2.5.0 — 2026-10-13

**Saving a page no longer eats its content.** Editing a CMS page in the admin used to write a
draft built only from the fields the block form declares. Everything else — terminal lines,
comparison columns and cells, footnotes, table captions — was dropped, and the page rendered
broken until the data was restored by hand. The draft is now merged with the published blocks,
so whatever the form does not touch is kept as it was.

**The `pricing` block stores its features properly.** Its `features` field wrote a bare list of
strings where the views and the seeded data expected `feature` objects. It now matches.

### v2.4.0 — 2026-10-13

- **Comparison table** (v2.4.0) — options side by side, one row per criterion: columns with an optional badge and tagline, rows of cells whose length follows the number of columns, a "what the others do better" list, call-to-action buttons, a closing line and a footnote.
- **Installation steps** (v2.4.0) — numbered steps, one command per platform, a copy button that confirms itself with its own label, and an optional download link. Every label comes from the block, so a page can be written in any language. Each platform also carries its own terminal chrome.

### v2.3.0 — 2026-10-13

- **Image + rich text split block** (v2.3.0) — an image beside rich text, alternating sides down a page, with an optional list of items and a `side` field choosing which column holds the image.
- **Gallery and FAQ blocks fixed for UUID-keyed data** (v2.3.0) — the editor stores lists keyed by UUID once a block is edited; the gallery lightbox and the FAQ accordion both assumed integer keys and broke silently on edited pages.
- **Page translation saves fixed** (v2.3.0) — saving a translation persisted nothing, or aborted with a 404, because Filament re-resolved the page's record through the resource's own model. Broken since August; the two failing tests are green again.

### Notable changes

- All filament packages pinned to `^5.0` (`filament/actions`, `filament/filament`, `filament/schemas`, `filament/support`, `filament/tables`)
- Livewire registration updated for Livewire 4 across core and all plugin packages
- Testbench upgraded to `^11.0`, Pest remains at `^4.0`

### Notable v2.x features (by release)

- **Blog Post Preview** (v2.1.0) — signed-URL preview (30 min) of a translation's rendered draft before publishing, including unpublished and scheduled posts, via a Preview action in the edit form
- **Admin Branding** (v2.1.0) — Blogr brand name + logo in the Filament panel header
- **Hreflang SEO** (v2.2.0) — `<link rel="alternate" hreflang>` cross-language tags (with `x-default`) on localized blog pages, using each locale's translated slug
- **Slug Reload Action** (v2.2.0) — reload (↻) suffix action on the slug field of all content type forms to regenerate the slug from the title/name

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
