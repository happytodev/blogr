# Changelog

All notable changes to `blogr` will be documented in this file.

## Unpublished

### 🔧 Improvements

- **Permalink Copy Functionality**: Enhanced heading permalink copy-to-clipboard feature
  - Added fallback support for non-HTTPS environments
  - Uses modern `navigator.clipboard` API when available (HTTPS/localhost)
  - Falls back to `document.execCommand('copy')` for HTTP contexts
  - Prevents "Cannot read properties of undefined" errors in non-secure contexts
  - Maintains visual feedback notification on successful copy

### 🐛 Bug Fixes

- **Publication Date Display**: Fixed malformed HTML output by removing stray '>' character at the end of @endif directive, now secured by `PublicationDateHtmlOutputTest.php` to prevent regression

### 📚 Documentation

- **README Updates**: Updated feature completion status
  - Marked Beta 2 features as completed (SEO fields, scheduled publishing, TOC, etc.)
  - Marked Beta 3 features as completed (multilingual, series, RSS feed, etc.)
  - Updated roadmap section with current completion status

## [v0.12.0](https://github.com/happytodev/blogr/compare/v0.12.0...v0.11.2) - 2025-10-21

### 🔒 Security

- **Settings Access Control**: Restricted BlogrSettings page to admin role only
  - Added `canAccess()` method to prevent writers from viewing/editing settings
  - Only users with 'admin' role can access the settings page
  - Settings link automatically hidden from navigation for non-admin users
  - Added 3 comprehensive tests validating access control

### ✨ Features

- **TOC Position Configuration**: Three-position Table of Contents system
  - New `toc.position` config supporting 3 modes: `'center'`, `'left'`, or `'right'` (default: `'center'`)
  - **Center mode**: TOC remains inline with content (traditional flow layout)
  - **Sidebar modes** (left/right): Sticky TOC in dedicated sidebar
    - 280px fixed-width sidebar that stays visible during scroll
    - Responsive grid layout with `lg:sticky` positioning
    - Automatic TOC extraction from content for sidebar rendering
  - Dynamic CSS classes (`blogr-toc-center`, `blogr-toc-sidebar`, `blogr-toc-{position}`)
  - Admin setting in BlogrSettings page to configure position
  - View automatically adjusts layout based on position (inline vs. sidebar)
  - Added 5 comprehensive tests validating functionality and styling

- **CSS Variables Theming System**: Complete theming system with CSS variables for colors
  - Primary, category, tag, and author colors with dark mode support
  - Hover effects and link styling using CSS variables
  - Applied across navigation, footer, cards, breadcrumbs, and all components
  - `ColorHelper` class for automatic dark mode color adjustments

- **Blog Series Translations**: Full translation support for series
  - Added `slug` column to `blog_series_translations` table for localized URLs
  - Series routes now use translated slugs (`/blog/series/{translatedSlug}`)
  - Automatic slug generation and fallback to default locale
  - Updated navigation, breadcrumbs, and cards to use translated slugs

- **Publication Date Configuration**: Granular control over date display
  - Master toggle: `ui.dates.show_publication_date` (global enable/disable)
  - `ui.dates.show_publication_date_on_cards` - Show dates on post cards
  - `ui.dates.show_publication_date_on_articles` - Show dates on article pages
  - Replaces deprecated `ui.blog_post_card.show_publication_date`

- **Default Image Management**: Improved fallback logic for post images
  - `BlogPost::getPhotoUrlAttribute()` with priority chain:
    1. Post photo (database)
    2. `config('blogr.posts.default_image')`
    3. Legacy `config('blogr.default_cover_image')` (backward compatibility)
    4. Hardcoded fallback: `/vendor/blogr/images/default-post.svg`
  - Works with translated content and author pages

- **Tags Position Configuration**: Control where tags appear on articles
  - `ui.posts.tags_position` setting: `'top'` or `'bottom'`
  - Configurable via BlogrSettings admin panel

- **Heading Permalink Configuration**: Customizable anchor links for headings
  - Symbol: `'#'`, `'§'`, `'¶'`, `'🔗'`, etc.
  - Spacing: `'none'`, `'before'`, `'after'`, `'both'`
  - Visibility: `'always'` or `'hover'`
  - Copy-to-clipboard functionality with visual feedback

- **Author Profile Enhancements**:
  - Bio rendered as Markdown with `MarkdownHelper` class
  - Avatar with gradient background using primary colors
  - Improved hover effects with primary color ring
  - Author name tooltip on avatar hover
  - Proper display of pseudo vs full name based on `display.show_author_pseudo`

- **Blog Post Card Component**: New reusable `blog-post-card.blade.php`
  - Translation support with automatic locale detection
  - Dynamic content rendering (title, tldr, image, tags)
  - Consistent styling across index, category, tag, and author views

- **Series Card Component**: New reusable `series-card.blade.php`
  - Responsive design with author avatars
  - Author limit display (`series_authors_limit` config)
  - Translated slug support for all links

- **RSS Feed System**: Complete RSS 2.0 feed implementation with multilingual support
  - Routes: `/{locale}/blog/feed` for main feed
  - Category feeds: `/{locale}/blog/feed/category/{slug}`
  - Tag feeds: `/{locale}/blog/feed/tag/{slug}`
  - RSS 2.0 format with Atom and Dublin Core namespaces
  - Automatic language detection per locale
  - 1-hour public cache (`Cache-Control: public, max-age=3600`)
  - Published posts only with proper date formatting
  - Configurable items limit via `blogr.rss.items_limit` (default: 20)
  - Uses TL;DR field for description (falls back to first 300 characters)
  - XML escaping for security
  - Full documentation in `RSS_FEED.md`

### 🔧 Improvements

- **Excerpt Field Removed**: Simplified content management by removing redundant `excerpt` field
  - **Rationale**: The `excerpt` field duplicated the `tldr` (Too Long; Didn't Read) functionality
  - Now uses only `tldr` field throughout the application
  - Updated `BlogPostTranslation` model: removed `excerpt` from `$fillable` array
  - Enhanced `tldr` form field: Changed from TextInput (255 chars) to Textarea (500 chars, 3 rows)
  - Updated all controllers to use `tldr` instead of `excerpt`
  - Simplified blog post card component (no excerpt fallback logic)
  - Updated RSS feed to use `tldr` for description
  - Updated migration command (`MigratePostsToTranslations`)
  - **Migration Note**: Existing `excerpt` data can be manually migrated to `tldr` if needed
  - All 476 tests updated and passing

- **Table of Contents Styling**: Enhanced TOC appearance and functionality
- **Author Bio Component**: Improved rendering with markdown support and avatar display options
- **Series Navigation**: Better layout and responsiveness with CSS variables
- **Language Switcher**: Updated styling for better visibility and theming
- **Footer Social Links**: Added Bluesky, YouTube, Instagram, TikTok, Mastodon support
- **Breadcrumb Navigation**: Improved hover effects and translated slug support
- **BlogrSettings**: Enhanced admin panel with better tab structure and color options
- **Translations**: Added French translations for author information and featured series

### 🐛 Bug Fixes

- **RSS Feed 404 Error**: Fixed RSS feed routes returning 404 errors
  - **Root Cause 1**: Invalid type hint `string $locale = null` in `RssFeedController` (should be `?string $locale = null`)
  - **Root Cause 2**: Route ordering issue - catch-all `{slug}` route was matching `/feed` before RSS routes
  - **Solution**: Moved RSS route registration BEFORE catch-all routes in `BlogrServiceProvider` (lines 195-202, 273-278)
  - Added comments to prevent future route ordering issues
  - All RSS routes now working: main feed, category feed, tag feed
  - Tests: 6 comprehensive RSS feed tests (18 assertions) all passing

- **Photo Fallback Logic**: Fixed default images not loading on author pages
  - Author page now correctly uses `config('blogr.posts.default_image')`
  - Proper asset() path resolution for vendor images
  - Consistent fallback across all views

- **TOC Display Logic**: Clarified priority checks in `BlogPost` model
  - Fixed nullable `display_toc` column in migration
  - Proper cascade: post setting → strict mode → global setting

- **TOC Position Test**: Fixed `TocPositionTest` failing on "respects display_toc setting"
  - Updated test to check for actual HTML elements (`<aside class="toc-sidebar-wrapper">`) instead of CSS class names
  - CSS class definitions in `<style>` tags were causing false positives
  - Test now correctly validates that TOC elements are not rendered when `display_toc` is false

- **Series Image Overlay**: Commented out gradient overlay for better image visibility

### 🧪 Tests

- Added comprehensive feature tests for:
  - **RSS Feed System**: 6 tests validating XML structure, post details, category/tag filtering, published-only posts, items limit, and XML escaping
  - **TOC Position**: Fixed and updated tests for TOC display logic and positioning
  - Author profile enhancements (layout, bio, dates, avatars)
  - Author avatar hover effects
  - Publication date display and fallback logic
  - Photo URL attribute and fallback chain
  - Blog series slug generation and translations
  - Settings configuration and persistence
- **Excerpt Removal**: Updated 20+ test occurrences across 6 test files to use `tldr` instead of `excerpt`
- Total: **476 passing tests** (1397 assertions), 10 skipped, 0 failing

### 📚 Documentation

- **RSS Feed**: Added comprehensive `RSS_FEED.md` documentation
  - Complete guide for RSS feed routes and configuration
  - Examples of XML output format
  - Multilingual support documentation
  - Configuration options and customization guide
- Updated config comments to clarify image publication paths
- Added inline documentation for new helpers and methods

### ⚠️ Breaking Changes

- **Deprecated Config**: `ui.blog_post_card.show_publication_date` replaced by `ui.dates.*` settings
- **Migration Required**: New `slug` column in `blog_series_translations` table


## [v0.11.2](https://github.com/happytodev/blogr/compare/v0.11.2...v0.11.1) - 2025-10-15

### 🐛 Bug Fixes

- **Author Bio Settings**: Fixed author bio settings not working properly ([Issue #116](https://github.com/happytodev/blogr/issues/116))
  - The `author_bio.enabled` setting now correctly controls bio visibility
  - The `author_bio.compact` setting now properly switches between compact and full display
  - The `author_bio.position` setting now works correctly (top/bottom/both)
  - Added conditional rendering in `show.blade.php` to check all config settings
  - Component now receives `compact` parameter from configuration
  - Added comprehensive TDD tests (10 tests) to verify all settings combinations
  - **Breaking**: Sites with published views must update `resources/views/vendor/blogr/blog/show.blade.php`

- **Installation Command**: Fixed double comma syntax error in User model casts ([Issue #115](https://github.com/happytodev/blogr/issues/115))
  - Installation script now properly removes trailing commas before adding `'bio' => 'array'` cast
  - Prevents `Cannot use empty array elements in arrays` fatal error
  - Added test to verify no double commas are created during installation


## [v0.11.1](https://github.com/happytodev/blogr/compare/v0.11.1...v0.11.0) - 2025-10-15

### 🐛 Bug Fixes

- **Bio Display Fix**: Author bio now properly displays in multilingual contexts
  - Installation command now automatically adds `'bio' => 'array'` cast to User model
  - Supports both Laravel 11+ style (`casts()` method) and Laravel 10 style (`$casts` property)
  - Prevents bio from displaying as raw JSON string
  - Essential for proper multilingual bio support
  - Added comprehensive documentation in README with manual configuration steps

- **Reading Time Calculation**: Fixed reading time display on author pages for multilingual blogs
  - Reading time now calculated from active translation content instead of main model content
  - Added dynamic calculation in `AuthorController` using translation title and content
  - Respects configured reading speed from `config('blogr.reading_speed.words_per_minute', 200)`
  - View updated to use `ConfigHelper::getReadingTimeText()` for proper formatting

### ✨ Features

- **Auto Admin Role Assignment**: Installation command now automatically assigns admin role to first user
  - Detects if user exists in database after installation
  - Automatically assigns 'admin' role using Spatie Permission package
  - Ensures immediate access to all Blogr features post-installation
  - Skips if no users exist or if role assignment not needed

## [v0.11.0](https://github.com/happytodev/blogr/compare/v0.11.0...v0.10.2) - 2025-10-15

### 🐛 Bug Fixes

- **Test Fixes**: Fixed failing `AuthorDisplaySettingsTest`
  - Added `author_profile.enabled => false` config to prevent author slug appearing in URLs when testing pseudo display settings
  - Test now correctly validates that author pseudo is hidden when `show_author_pseudo` is disabled

### 📚 Documentation

- **Code Coverage Setup**: Added comprehensive guide `CODE_COVERAGE_SETUP.md`
  - Xdebug installation instructions for macOS
  - PCOV alternative configuration (faster than Xdebug)
  - phpunit.xml coverage configuration
  - Troubleshooting common issues
  - Useful commands and aliases

### 🎨 UI Improvements

- **Author Page Improvements**: Major redesign of author profile pages
  - **Container Width**: Changed from `max-w-6xl` to `max-w-7xl` to match homepage consistency
  - **Author Bio Display**: Bio now prominently displayed in header section above article list with enhanced styling
  - **Card Layout Consistency**: Author page cards now match homepage/series card styling exactly
    - Category badges positioned absolutely in top-left corner (same as homepage)
    - Reading time badges in top-right corner with clock icon (using `getFormattedReadingTime()` method)
    - Image height increased from 48 to 56 (h-56) to match other pages
    - Added `group` hover effects and scale transitions on images
    - Rounded corners changed from `rounded-lg` to `rounded-xl`
    - Shadow upgraded from `shadow-md` to `shadow-lg` with `hover:shadow-2xl`
    - Added `transform hover:-translate-y-1` lift effect
  
- **Clickable Elements**: Enhanced interactivity across all listing pages
  - **Author Avatars**: Now clickable and link to author profile page when enabled
  - **Post Images**: All post card images wrapped in clickable links to articles
  - **Series Images**: Series card images now clickable and link to series pages
  - Added hover opacity transitions on clickable author info components
  
- **Visual Refinements**: Removed blue borders for cleaner design
  - Removed `border-2 border-blue-200 dark:border-blue-800` from featured series cards on homepage
  - Removed blue border from series header on individual series pages
  - Removed blue border from series cards on series index page
  - Cleaner, more modern card appearance with focus on content

- **Default Images for Posts and Series**: Added fallback to default images across all views
  - Blog posts without photos now display default image from `config('blogr.posts.default_image')`
  - Series without photos now display default image from `config('blogr.series.default_image')`
  - Applied to all listing pages: index, category, tag, series, series-index, author
  - Default images display with reduced opacity (50%) and centered icon overlay
  - Consistent visual experience even when images are not uploaded
  - Configurable in `config/blogr.php` for easy customization

- **Unified Author Page Layout**: Author profile page now uses consistent card layout
  - Migrated from horizontal list layout to vertical 3-column grid matching other blog pages
  - Cards display: photo, category badge, reading time, title, excerpt, tags, published date, and "Read more" button
  - Uses same CSS Grid `auto-rows-fr` for equal row heights
  - Bottom section (tags + date + read more) aligned at card bottom using `mt-auto`
  - Improved `AuthorController` to handle translated content and photo fallback logic (translation photo > post photo > any translation photo)
  - Added `getStorageUrl()` helper method for smart URL generation (temporary URLs for cloud, regular for local)
  - Better visual consistency across all listing pages (index, category, tag, series, author)

- **Blog Card Layout**: Improved consistency and alignment across all blog card layouts
  - Author information and "Read more" button now displayed side-by-side on the same line
  - Both elements always aligned at the bottom of cards using flexbox
  - Added visual separator (border-top) for better content hierarchy
  - Cards on the same row have equal heights with `auto-rows-fr` in CSS Grid
  - Applied to: Homepage (`index`), Category pages, Tag pages, and Series detail pages
  - Creates uniform card appearance with better visual balance and professional look


## [v0.10.2](https://github.com/happytodev/blogr/compare/v0.10.2...v0.10.1) - 2025-10-15

### 🐛 Bug Fixes

- **Blog & Series Images**: Fixed broken images after upload (403 Forbidden errors)
  - **Root Cause**: Files were uploaded to `storage/app/private` instead of `storage/app/public`
  - **Solution**: Configured all FileUpload fields (blog posts, series, translations) to use `->disk('public')` explicitly
  - **Storage URLs**: Implemented smart URL generation with automatic fallback (temporary URLs for S3, regular URLs for local)
  - **Affected Files**: BlogPostForm.php (3 fields), BlogSeriesForm.php (2 fields)

### ✨ Features

- **Per-Translation Photo Support for Series**: Series translations can now have their own photos
  - **Migration**: Added `photo` column to `blog_series_translations` table
  - **Form Enhancement**: Added photo upload field in series translation repeater with image editor
  - **3-Level Fallback Logic**: Translation photo → Series photo → Any other translation photo
  - **Applied in**: Series list (`seriesIndex()`), series detail (`series()`), and homepage
  - **View Enhancement**: Added series image display in `series.blade.php` detail page

- **Enhanced Repeater UI**: Improved visual styling for translation repeaters in both posts and series
  - Flag emojis for 12+ languages (🇬🇧 🇫🇷 🇪🇸 🇩🇪 etc.)
  - Styled labels with larger font, bold weight, and indigo color
  - Uses `HtmlString` for proper HTML rendering

### 🔧 Technical Improvements

- **Storage Abstraction**: Created `getStorageUrl()` helper method for consistent URL generation across all controllers
- **Disk Configuration**: All image uploads now explicitly use the `public` disk
- **Test Coverage**: Added 7 comprehensive tests for series photo fallback functionality

## [v0.10.1](https://github.com/happytodev/blogr/compare/v0.10.1...v0.10.0) - 2025-10-15

### ✨ Features

- **Per-Translation Photo Support**: Each translation can now have its own photo with intelligent fallback
  - **Migration**: Added `photo` column to `blog_post_translations` table
  - **Form Enhancement**: Added `FileUpload` field in translation repeater with image editor, aspect ratios, and helper text
  - **Fallback Logic**: Implements 3-level fallback system:
    1. Photo from current translation
    2. Photo from main post (if translation has no photo)
    3. Photo from any other translation (if main post has no photo)
  - **Applied in**: Both homepage cards (`index()`) and article detail pages (`show()`)
  - **Storage**: Uses temporary URLs with 1-hour expiry for secure access

### 🎨 UI Improvements

- **Enhanced Repeater Headers**: Translation repeater items now feature improved visual styling
  - **Flag Emojis**: Language-specific flag emojis for 12+ locales (🇬🇧 🇫🇷 🇪🇸 🇩🇪 🇮🇹 etc.)
  - **Styled Labels**: Larger font (1.1rem), bold weight, and indigo color (#6366f1)
  - **Rich Information**: Displays locale code, post title, and slug in hierarchical layout
  - **HTML Rendering**: Uses `HtmlString` for proper rendering instead of escaped HTML

- **Translation Form Reorganization**: 
  - Moved "Content & Translations" section to top of form for better workflow
  - Repeater with collapsible items for each translation
  - Added photo upload capability per translation with helpful guidance text

### 🧪 Testing

- **New Test Suite**: `TranslationPhotoFallbackTest.php` with 7 comprehensive tests
  - Tests translation-specific photo display
  - Tests fallback to main post photo
  - Tests fallback to other translation photos
  - Tests model fillable attributes
  - Tests storage and retrieval operations
  - Tests homepage card photo display
  - **All 293 tests passing** (906 assertions) ✅

### 📝 Technical Details

- **Modified Files**:
  - `src/Http/Controllers/BlogController.php`: Added photo fallback logic in `index()` and `show()` methods
  - `src/Filament/Resources/BlogPosts/BlogPostForm.php`: Enhanced with flags, styling, and photo upload
  - `src/Models/BlogPostTranslation.php`: Added `photo` to `$fillable` array
  - `database/migrations/2025_10_15_000001_add_photo_to_blog_post_translations_table.php`: New migration

- **Dependencies**: 
  - Added `use Illuminate\Support\HtmlString;` for safe HTML rendering in Filament


## [v0.10.0](https://github.com/happytodev/blogr/compare/v0.10.0...v0.9.1) - 2025-10-15

### 🔧 Admin Interface Refactoring

- **Translation-First Blog Post Form**: Complete restructuring of the admin edit interface
  - **Main Content Section**: Moved Translations relation manager to top of page (after header)
  - **Form Simplification**: Removed all translatable fields from main form (title, slug, content, SEO)
  - **Metadata Focus**: Main form now only manages post metadata (photo, category, tags, series, publication)
  - **Helper Text**: Added explanatory text for category/tags indicating automatic translation support
  - **Persistent Display**: Relation manager now persists through Livewire updates (file uploads, etc.)
  - **Ergonomic Layout**: Clear separation between content management and post metadata

### 🧪 Testing

- **Test Cleanup**: Removed obsolete `BlogPostFormTest.php` 
  - Test was for frontmatter/TOC management in old single-table content system
  - No longer relevant in Translation-First architecture
  - All remaining tests pass: **286 tests, 895 assertions** ✅

### 🐛 Bug Fixes

- **PHP 8.4 Compatibility**: Fixed deprecated nullable parameter warning in `BlogPostPolicy::publish()`
  - Changed implicit `= null` to explicit `?BlogPost $blogPost = null`
  - Zero deprecated warnings in test suite

## [v0.9.1](https://github.com/happytodev/blogr/compare/v0.9.1...v0.9.0) - 2025-10-13

### ✨ Features

- **Translation Fallback System**: Graceful handling of missing translations
  - Posts without translations in requested language now show default translation instead of crashing
  - New `translation-warning` Blade component displays visual alert when fallback occurs
  - Internationalized warning messages in 4 languages (EN/FR/DE/ES)
  - Quick-switch buttons to available translations directly from warning
  - Enhanced `BlogController` with null-safety checks and fallback logic

### 🎨 UI Improvements

- **Translation Warning Component**:
  - Yellow alert box with warning icon for high visibility
  - Clear message explaining which language is being displayed
  - List of available translations with quick-access links
  - Fully responsive and dark mode compatible

### 🧪 Testing

- **Translation Fallback Tests** (new file: `TranslationFallbackTest.php`):
  - ✅ Post with only English translation accessed in French shows English version
  - ✅ Post with bilingual translations shows correct language version
  - ✅ Unpublished post returns 404 even with translation
  - ✅ Future published post returns 404
  - Test coverage: 4 new passing tests ensuring translation safety

### 🐛 Bug Fixes

- **Critical: Translation Fallback Crash**: Fixed fatal error when switching languages on untranslated content
  - **Root Cause**: BlogController attempted to read property 'content' on null when translation missing
  - **Impact**: Production-breaking issue affecting all multilingual blogs
  - **Solution**: Added comprehensive null safety checks with graceful fallback to default translation
  - **User Experience**: Visual warning component replaces error page when translation unavailable
  - **Error Prevention**: Returns 404 only if no translations exist at all
  - **Testing**: 4 new tests ensure this cannot occur again


## [v0.9.0](https://github.com/happytodev/blogr/compare/v0.9.0...v0.8.3) - 2025-10-13

### ✨ Features

- **Multi-Author Series Display**: Complete implementation of series authors visualization
  - Added `display.show_series_authors` configuration option (default: true)
  - Added `display.series_authors_limit` configuration to limit displayed avatars (default: 4)
  - New `BlogSeries::authors()` method returns array of unique authors ordered by contribution
  - New `series-authors` Blade component with overlapping avatars, tooltips, and clickable links
  - Integrated in series index, series detail, and homepage featured sections
  - Settings panel in Blogr admin to toggle and configure display

- **Author Information Component**: Enhanced author display on articles
  - New `author-info` Blade component showing avatar and pseudo
  - Integrated in blog index cards and series article cards
  - Configurable via `display.show_author_pseudo` and `display.show_author_avatar` settings
  - Supports custom avatar images or auto-generated initials with gradient backgrounds
  - Clickable links to author profile pages

### 🎨 UI Improvements

- **Series Authors Visualization**:
  - Overlapping avatar design (-space-x-2) with elegant borders
  - Hover animations with scale effect and colored ring
  - Smart "+X" indicator when exceeding the configured limit
  - Tooltips showing author pseudos on hover
  - Responsive design with size variants (xs, sm, md, lg)
  - Dark mode support throughout

- **Article Cards Enhancement**:
  - Added author information on all article cards
  - Consistent styling across blog index, series pages, and featured sections
  - Improved visual hierarchy with proper spacing

### ⚙️ Configuration

- **New Display Settings**:
  - `display.show_series_authors`: Toggle series authors display
  - `display.series_authors_limit`: Maximum avatars to display (1-10)
  - Settings accessible via Admin > Blogr > Settings panel
  - Real-time configuration updates via Filament form

### 🧪 Testing

- **New Test Suite**: `SeriesAuthorsDisplayTest`
  - Configuration existence and defaults validation
  - `BlogSeries::authors()` method functionality
  - Unique authors and correct ordering
  - Empty series handling
  - Component rendering and conditional display
- **Total Coverage**: 301 passing tests across 30+ test suites

### 🐛 Bug Fixes

- **Series Authors Component**: Fixed undefined variable error in Blade component
  - Corrected variable declaration order in component template
  - Removed problematic slot usage causing compilation errors
- **View Caching**: Ensured proper cache clearing for component updates

### 📚 Documentation

- **Installation Guide**: Updated with series authors feature setup
- **Configuration Reference**: Documented all new display settings
- **Component Usage**: Examples for integrating series-authors in custom views
- **Testing Guide**: Comprehensive test scenarios for multi-author features

## [v0.8.3](https://github.com/happytodev/blogr/compare/v0.8.3...v0.8.2) - 2025-10-10

### ✨ Enhancements

- **Installation Command**: Enhanced automation of installation process
  - **BlogrPlugin auto-configuration**: Automatically adds `BlogrPlugin::make()` to AdminPanelProvider (handles both cases: with and without existing plugins array)
  - **User Model auto-configuration**: Automatically adds `HasRoles` trait from Spatie Permission to User model
  - Now detects and configures AdminPanelProvider automatically with user confirmation

### 🐛 Bug Fixes

- **Critical: User Model Configuration**: User model now automatically configured with Spatie Permission HasRoles trait
  - Fixes `Call to undefined method App\Models\User::hasRole()` error in BlogPostResource authorization
  - Automatically adds `use Spatie\Permission\Traits\HasRoles;` import to User model
  - Automatically adds `HasRoles` to User model traits
  - **Manual fix for existing installations**:
    ```php
    // In app/Models/User.php, add:
    use Spatie\Permission\Traits\HasRoles;
    
    // And in the class:
    use HasFactory, HasRoles, Notifiable;
    ```

- **Critical: Default Images Path**: Fixed images publication to use correct path
  - Removed duplicate image publication in `public/storage/images/`
  - Images now published **only** to `public/vendor/blogr/images/` via `blogr-assets` tag
  - Views reference `/vendor/blogr/images/` path correctly
  - **Manual fix for existing installations**: 
    ```bash
    # Remove old images
    rm -rf public/storage/images/blogr.webp public/storage/images/default-*.svg
    
    # Create correct directory and copy images
    mkdir -p public/vendor/blogr/images
    cp vendor/happytodev/blogr/resources/images/* public/vendor/blogr/images/
    ```

## [v0.8.2](https://github.com/happytodev/blogr/compare/v0.8.2...v0.8.1) - 2025-10-10

### ✨ Enhancements

- **Installation Command**: Complete automation of installation process
  - **Alpine.js configuration**: Automatically configures Alpine.js in `resources/js/app.js`
  - **Tailwind CSS v4 dark mode**: Automatically adds `@variant dark (.dark &);` to `resources/css/app.css`
  - **Series content**: New option to install example series with posts (`--skip-series` to skip)
  - **Asset building**: Automatically runs `npm run build` at the end (`--skip-build` to skip)
  - **Frontend configuration**: New `--skip-frontend` option to skip Alpine.js and Tailwind CSS configuration
  - **One-command setup**: After `composer require happytodev/blogr`, just run `php artisan blogr:install`
  - All configurations can now be applied automatically with user confirmation

### 🐛 Bug Fixes

- **Installation Command**: Fixed "no such table: roles" error during tutorial installation
  - Now automatically publishes Spatie Permission migrations before running migrations
  - Added optional prompt to publish Spatie Permission configuration
  - Updated README documentation to reflect Spatie Permission setup
  
- **Missing Default Images**: Fixed 404 error on default post and series images
  - Default images (default-post.svg, default-series.svg) are now correctly published to `public/vendor/blogr/images/`
  - **Manual fix for existing installations**: Run `php artisan vendor:publish --tag=blogr-assets --force` and copy images manually if needed

- **Theme Switcher Not Working**: Fixed light/dark/auto theme switcher functionality
  - Removed unreliable Alpine.js CDN loading from layout
  - Added Alpine.js as npm dependency for reliable asset bundling
  - Created `themeSwitch()` Alpine component for proper initialization
  - Added proper system preference detection for auto mode
  - Theme preferences now correctly persist in localStorage
  - **⚠️ CRITICAL**: Requires Tailwind CSS v4 dark mode configuration
  - **Manual fix for existing installations**: 
    1. Run `npm install alpinejs`
    2. Add Alpine initialization to `resources/js/app.js`:
       ```javascript
       import Alpine from 'alpinejs'
       window.Alpine = Alpine
       Alpine.data('themeSwitch', () => ({
           theme: localStorage.getItem('theme') || 'auto',
           init() {
               this.applyTheme();
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
    3. **CRITICAL**: Add dark mode variant to `resources/css/app.css`:
       ```css
       @variant dark (.dark &);
       ```
       This line must be added to your Tailwind CSS v4 configuration for dark mode to work.
    4. Remove Alpine CDN script from `resources/views/vendor/blogr/layouts/blog.blade.php` if present
    5. Run `npm run build`
    4. Run `npm run build`

  - Added Alpine.js as npm dependency requirement in installation docs
  - Alpine.js is now properly loaded via Vite for reliable initialization
  - **Manual fix for existing installations**: 
    1. Run `npm install alpinejs`
    2. Add Alpine initialization to `resources/js/app.js`:
       ```javascript
       import Alpine from 'alpinejs'
       window.Alpine = Alpine
       Alpine.start()
       ```
    3. Remove Alpine.js CDN script from published `resources/views/vendor/blogr/layouts/blog.blade.php`
    4. Run `npm run build`

## [v0.8.1](https://github.com/happytodev/blogr/compare/v0.8.1...v0.8.0) - 2025-10-09

### ✨ Features

- You can now actually use the blogr engine as your home page. The "homepage" setting will help you do this.

### 🔧 Route Configuration Fixes

- **Locale-aware Link Generation**: Updated category, blog post, series, and tag link generation to properly handle locale configuration
- **Route Registration**: Fixed route registration when locales are disabled to prevent "Route not defined" errors
- **Category and Tag Routes**: Added dedicated routes for category and tag pages in BlogrServiceProvider
- **Enhanced Methods**: Improved category and tag methods for locale handling and backward compatibility

### 🧪 Test Improvements

- **Comprehensive Navigation Tests**: Added BlogNavigationTest.php with tests for all homepage × locale configuration combinations
- **Route Test Fixes**: Removed locale parameters from route assertions when locales are disabled
- **Testbench Configuration**: Updated testbench config files with complete homepage and locales sections

### 🌐 Browser Test Updates

- **Authentication Setup**: Added skip() calls to browser tests requiring proper authentication setup with explanatory messages


## [v0.8.0](https://github.com/happytodev/blogr/compare/v0.8.0...v0.7.0) - 2025-10-08

### 📚 Blog Series Feature

- **Series Management**: Create and organize blog posts into series
  - Create series with slug, position, featured flag, and publication date
  - Assign posts to series with position ordering
  - Navigate between posts with previous/next links
  - Automatic series navigation on post pages
  
- **Multilingual Series Support**: Full translation support for series
  - Translate series titles, descriptions, and SEO fields
  - Support for en, fr, es, de (extensible to more languages)
  - Filament admin interface for managing translations
  
- **Filament Resources**: Complete admin interface
  - `BlogSeriesResource` with dedicated Form and Table classes
  - `BlogSeriesForm`: Series information and translations repeater
  - `BlogSeriesTable`: Columns, filters, actions, and bulk operations
  - Navigation group with badge showing series count

### 🌍 Multilingual Support

- **Content Translations**: Translate all content types
  - Blog posts with per-translation content, SEO fields, and reading time
  - Blog series titles and descriptions
  - Categories and tags with translation relationships
  - Automatic fallback to default locale when translation missing
  
- **Database Architecture**: Separate translation tables
  - `blog_post_translations`: LONGTEXT content with reading time
  - `blog_series_translations`: Title, description, SEO fields
  - `category_translations`: Name, slug, description per locale
  - `tag_translations`: Name, slug, description per locale
  - Unique constraints: [entity_id, locale] and [locale, slug]
  
- **Localized Routes** (Optional): URL structure with locale prefix
  - Pattern: `/{locale}/blog/{slug}` (e.g., `/en/blog/post`, `/fr/blog/article`)
  - `SetLocale` middleware for automatic language detection
  - Configurable via settings: enable/disable localized routes
  - Backward compatible: works without localized routes
  
- **Configuration Management**: Easy multilingual setup
  - `config/blogr.php`: locales.enabled, locales.default, locales.available
  - Filament settings page with toggle and locale management
  - Available locales: comma-separated list in admin interface

### 🎨 Frontend Components

- **Series Components**: Rich UI for series navigation
  - `series-navigation`: Previous/Next navigation with gradient design
  - `series-list`: Complete series view with position indicators
  - `series-badge`: Compact "Part X/Y" badge for posts
  - `breadcrumb`: Navigation with series context + Schema.org JSON-LD
  
- **Language Components**: International UX
  - `language-switcher`: Dropdown with flags and language names
  - `hreflang-tags`: Automatic SEO tags for search engines
  - Fully styled with Tailwind CSS
  
- **New Routes**: Series viewing page
  - `/blog/series/{seriesSlug}`: View complete series
  - `BlogController@series`: Controller method with locale support
  - `series.blade.php`: Beautiful series listing page

### 🔧 Helpers & Utilities

- **LocaleHelper**: Translation management utilities
  - `currentLocale()`: Get active language
  - `route()`: Generate localized URLs
  - `availableLocales()`: List supported languages
  - `alternateUrls()`: Generate hreflang URLs
  - `hreflangTags()`: Generate SEO meta tags

### 📊 Demo Data

- **BlogSeriesSeeder**: Realistic demo content
  - 2 complete blog series (Laravel & Vue.js)
  - 7 blog posts with full en/fr translations
  - Categories, tags, and proper relationships
  - "Laravel for Beginners": 4-post tutorial series (featured)
  - "Vue.js Best Practices": 3-post advanced series
  - Staggered publication dates over 30 days
  - Usage: `php artisan db:seed --class="Happytodev\Blogr\Database\Seeders\BlogSeriesSeeder"`

### 🧪 Testing

- **Maintained Test Coverage**: All 230 tests passing (767 assertions)
  - Series creation, translation, and navigation tests
  - Multilingual content persistence and retrieval
  - Route generation and middleware functionality
  - Frontend component rendering
  - Backward compatibility verification
  
- **Enhanced Test Coverage**: Comprehensive test suite with 134 passing tests
  - User management and role assignment
  - Permission verification for admin and writer roles
  - Data persistence validation

### 🎭 Role-Based Access Control

- **Admin & Writer Roles**: Two predefined user roles with distinct permissions
  - **Admin**: Full access to all features including user management and post publishing
  - **Writer**: Can create and edit content but cannot publish posts or manage users
  
- **Optional User Management**: Install user management when needed with a simple command
  - `php artisan blogr:install-user-management`
  - Automatically sets up roles, permissions, and admin interface
  - Includes optional test users for quick setup (`--with-test-users`)



## [v0.7.0](https://github.com/happytodev/blogr/compare/v0.7.0...v0.6.2) - 2025-10-05

### 🐛 Bug Fixes

- **Category Slug Validation**: Fix unique validation for category slugs to properly ignore current record during editing
- **Blog Post Publish Date Validation**: Allow editing existing published posts with past publish dates by making validation conditional based on whether it's a new post or existing record

### ✨ Features

- **Frontend Routes Configuration**: Add `blogr.route.frontend.enable` setting to control frontend route registration
- **Tag Display in Blog Posts Table**: Limit tag display to first 3 tags with "+X other(s)" indication for better UX, using proper singular/plural grammar ("+1 other" vs "+2 others")

### 🧪 Testing

- **Category Form Tests**: Add comprehensive unit tests for `CategoryForm` schema configuration
- **Category Model Tests**: Add functional tests for Category model behavior including slug generation, uniqueness validation, and relationships
- **Blog Post Publication Tests**: Add tests for publish date handling including:
  - Preservation of past publish dates when editing existing posts
  - Scheduling future publication dates
  - Immediate publication behavior
  - Draft post handling
- **Tag Display Tests**: Add tests for limited tag display in blog posts table including singular/plural grammar handling
- **Blog Post Tag Display Tests**: Add tests for limited tag display in table with "+X others" indication
- **Filament Integration Tests**: Add tests for Filament settings page integration and form validation

### 🎨 Code Quality

- **Code Formatting**: Improve code formatting and consistency in `BlogrSettings.php`


## [v0.6.1](https://github.com/happytodev/blogr/compare/v0.6.0...v0.6.1) - 2025-10-04

### 🐛 Bug Fixes

- Fix invalid named parameter `ignoringRecord` in category slug unique validation ([Issue #77](https://github.com/happytodev/blogr/issues/77))

## [v0.6.0](https://github.com/happytodev/blogr/compare/v0.5.0...v0.6.0) - 2025-09-10

### 🚀 Features

- **TOC Disable Feature**: Add ability to disable table of contents per post via frontmatter ([Issue #20](https://github.com/happytodev/blogr/issues/20))
- **Global TOC Setting**: Add global configuration option to enable/disable TOC by default for all posts
- **TOC Strict Mode**: Add strict mode to prevent individual posts from overriding global TOC setting
- **Settings Page Integration**: Add TOC configuration section in Filament settings page with TGE and TSM toggles
- **Real-time Updates**: Form updates frontmatter content when TOC toggle is changed
- **Dynamic Form Behavior**: TOC toggle becomes readonly when strict mode is enabled
- **Automated Installation**: Add `blogr:install` command for streamlined setup process
- **Default Content Creation**: Automatically create sample blog posts, categories, and tags to help users get started
- **Installation Options**: Support for `--skip-npm` and `--skip-tutorials` flags for flexible installation
- **User Onboarding**: Guided setup with welcome messages and next steps instructions

### 🧪 Testing

- **TOC Tests**: Add comprehensive tests for TOC disable functionality
- **Frontmatter Tests**: Add tests to ensure frontmatter is not displayed in rendered content
- **Form Integration Tests**: Add tests for Filament form TOC toggle functionality
- **Global Settings Tests**: Add tests for global TOC configuration and priority handling
- **Strict Mode Tests**: Add tests for all TGE/TSM matrix scenarios (4 combinations)
- **Matrix Behavior Tests**: Comprehensive test coverage for TGE=0/1 and TSM=0/1 combinations
- **Install Command Tests**: Add comprehensive unit tests for `blogr:install` command functionality
- **Command Registration Tests**: Add tests for command registration and help display
- **Mock-based Testing**: Implement proper mocking to avoid filesystem conflicts in parallel test execution

## [v0.5.0](https://github.com/happytodev/blogr/compare/v0.4.1...v0.5.0) - 2025-09-09

### 🚀 Features

- **Settings Page**: Add comprehensive Filament page for managing all blog configuration options
- **Configuration Sections**: General settings, appearance, reading time, SEO, Open Graph, and structured data
- **Auto Cache Clearing**: Automatic config cache clearing when settings are saved
- **Form Validation**: Comprehensive validation for all settings fields with constraints
- **Dashboard Widgets**: Blog statistics, recent posts, scheduled posts, and publication charts

### 🎨 UX/UI Improvements

- **User Interface**: Clean, organized settings interface with logical sections
- **Responsive Design**: Mobile and desktop optimized layout
- **Visual Hierarchy**: Proper spacing and form field organization

### 🧪 Testing

- **Settings Tests**: Comprehensive test suite for settings page functionality
- **Validation Tests**: Form validation and data persistence tests
- **Widget Tests**: Dashboard widget functionality tests

### 📚 Documentation

- **Settings Guide**: Complete SETTINGS_README.md with usage instructions
- **Technical Docs**: Implementation details and configuration options

### 📊 Dashboard Enhancements

- **Blog Statistics**: Color-coded status indicators and post counts
- **Recent Posts Table**: Latest posts with category and author info
- **Scheduled Posts**: Upcoming publications overview
- **Publication Charts**: Interactive trends visualization
- **Reading Analytics**: Content performance statistics

### 🧪 Testing

- **Settings Tests**: Form validation, cache clearing, and data persistence
- **Widget Tests**: Dashboard functionality and database interactions

## [v0.4.1](https://github.com/happytodev/blogr/compare/v0.4.0...v0.4.1) - 2025-09-07

### 🐛 Bug fixes

- fix(form): Improve publication date handling to prevent stale timestamps when editing posts
- fix(form): Implement smart publication logic - preserve future dates for scheduling, auto-fill current time for immediate publication

### 🧪 Testing

- test(form): Add test for immediate publication with automatic timestamp filling
- test(form): Add test for preserving future dates in scheduled publication
- test(form): Add test for handling slightly past timestamps gracefully

## [v0.4.0](https://github.com/happytodev/blogr/compare/v0.3.2...v0.4.0) - 2025-09-06

### 🐛 Bug fixes

- fix(form): Add validation to prevent scheduling posts with past dates
- fix(form): Restore proper date validation for published_at field with `after:now` rule
- fix(reading-time): Extract clock icon to Blade component for proper Tailwind CSS processing
- fix(reading-time): Hide clock icon when reading time display is disabled in configuration

### 🎨 UX/UI Improvements

- ux(navigation): Organize plugin menus in "Blog" navigation group for better organization
- ux(icons): Update TagResource navigation icon to Heroicon::OutlinedTag for better visual representation
- ux(icons): Update CategoryResource navigation icon to Heroicon::OutlinedFolder for better visual representation
- ux(navigation): Add navigation sort order (Blog Posts: 1, Categories: 2, Tags: 3) for consistent menu ordering

### 🚀 Features

- feat(reading-time): Add estimated reading time display with clock icon for all blog posts
- feat(reading-time): Display "<1 minute" for posts shorter than 1 minute reading time
- feat(config): Add configurable reading speed in blogr.php config file (default: 200 words/minute)
- feat(config): Include reading speed standards in config comments (150-300 words/minute range)
- feat(config): Add reading time display configuration with enable/disable option
- feat(config): Add customizable text format for reading time display with {time} placeholder
- feat(seo): Add comprehensive SEO meta fields integration (meta_title, meta_description, meta_keywords)
- feat(seo): Implement Open Graph (OG) meta tags for better social media sharing
- feat(seo): Add Twitter Cards support with customizable Twitter handle
- feat(seo): Integrate JSON-LD structured data for enhanced search engine understanding
- feat(seo): Add configurable default OG image with recommended dimensions (1200x630px)
- feat(seo): Implement organization structured data with logo and site information
- feat(seo): Add canonical URL support for proper SEO indexing
- feat(seo): Include image meta tags when blog posts have photos
- feat(seo): Add author information in meta tags and structured data
- feat(seo): Support article tags in meta keywords and structured data
- feat(seo): Add fallback to post data when meta fields are empty
- feat(seo): Implement robots meta tag configuration
- feat(config): Add complete SEO configuration section in blogr.php
- feat(config): Include Facebook App ID support for enhanced Open Graph
- feat(config): Add structured data enable/disable toggle
- feat(config): Support customizable site name and default titles/descriptions

### 🧪 Testing

- test(reading-time): Add comprehensive test for estimated reading time calculation
- test(reading-time): Verify "<1 minute" display for short posts
- test(reading-time): Test reading time with icon display functionality
- test(reading-time): Add test for reading time configuration settings (enable/disable)
- test(reading-time): Add test for customizable text format functionality
- test(reading-time): Verify icon is hidden when reading time is disabled
- test(form): Add test to validate that published_at dates cannot be in the past
- test(seo): Add comprehensive SEO meta tags tests for blog posts and index pages
- test(seo): Test Open Graph meta tags generation and validation
- test(seo): Verify Twitter Cards implementation with customizable handle
- test(seo): Add JSON-LD structured data validation tests
- test(seo): Test canonical URL generation for proper SEO indexing
- test(seo): Verify image meta tags when posts contain photos
- test(seo): Test author information inclusion in meta tags
- test(seo): Add robots meta tag configuration tests
- test(seo): Test structured data enable/disable functionality
- test(seo): Verify fallback behavior when meta fields are empty
- test(seo): Add SEO configuration loading and validation tests

### 📚 Documentation

- docs(reading-time): Update READING_TIME.md with new configuration options
- docs(reading-time): Add examples for customizable text formats
- docs(reading-time): Document complete deactivation feature (hides both icon and text)
- docs(seo): Add comprehensive SEO configuration documentation in README.md
- docs(seo): Document Open Graph image setup with recommended dimensions
- docs(seo): Add Twitter Cards configuration examples
- docs(seo): Document JSON-LD structured data setup with organization info
- docs(seo): Include complete SEO configuration examples with all options
- docs(seo): Add meta fields integration documentation
- docs(seo): Document canonical URL and robots meta tag configuration

## [v0.3.2](https://github.com/happytodev/blogr/compare/v0.3.1...v0.3.2) - 2025-09-02

### 🐛 Bug fixes

- fix(deps): Update filament/filament version constraint to allow minor updates


## [v0.3.1](https://github.com/happytodev/blogr/compare/v0.3.0...v0.3.1) - 2025-09-01

### 🐛 Bug fixes

- fix(form): Update published_at to current time when republishing a scheduled post

### 🧪 Testing

- test(form): Add test for published_at update when republishing scheduled posts


## [v0.3.0](https://github.com/happytodev/blogr/compare/v0.2.1...v0.3.0) - 2025-09-01

### 🚀 Feature

- feat(blog): Display message when no posts are published [#25](https://github.com/happytodev/blogr/issues/25)
- feat(blog): Add scheduled publishing functionality - posts can be published at a future date
- feat(blog): Publication status indicator with color coding (gray=draft, orange=scheduled, green=published)
- feat(blog): Enhanced admin interface with publish date picker and status display

### 🧪 Testing

- test(blog): Add comprehensive Pest PHP test for blog post display functionality
- test(blog): Verify blog post title, category, tags, TL;DR, and table of contents are displayed correctly
- test(blog): Add test-specific database migrations and factories for isolated testing
- test(blog): Configure TestCase for proper Laravel migrations loading in test environment
- test(blog): Add Pest test execution step to CI workflow
- refactor(test): Move test factories to `tests/database/factories/` directory for better organization
- refactor(test): Update factory namespaces to match new test directory structure

### 🐛 Bug fixes

- fix(test): Resolve "CreateUsersTable" class not found error in test migrations
- fix(test): Correct migration file naming convention for Laravel compatibility
- fix(test): Update TestCase configuration for proper factory and migration paths

### 🚜 Refactor

- refactor(test): Reorganize test database structure with dedicated migrations and factories directories
- refactor(test): Improve test isolation by using test-specific database setup
- refactor(package): Remove unnecessary UserFactory from package factories directory

### 📦 Dependencies

- test: Install orchestra/testbench for package testing

## [v0.2.2](https://github.com/happytodev/blogr/compare/v0.2.1...v0.2.2) - 2025-08-21

### 🐛 Bug fixes

- fix(blog): Fix typos in category labels across multiple files [#38](https://github.com/happytodev/blogr/issues/38)


## [v0.2.1](https://github.com/happytodev/blogr/compare/v0.2.0...v0.2.1) - 2025-08-20

### 📗 Documentation

- chore(doc): add missing instruction in the `Installation` section of `README.md` file


## [v0.2.0](https://github.com/happytodev/blogr/compare/v0.1.4...v0.2.0) - 2025-08-20

###  🚀 Feature

- feat(blog): add support for tags and categories in blog posts.
- feat(blog): display TL;DR section in the blog category view.
- feat(blog): using textarea for TL;DR with characters limit and dynamic helper text to visualize remaining characters.
- feat(config): allow customization of primary color in `blogr.php`.
- feat(blog): adding a table of contents at the beginning of the blog post

### 🐛 Bug fixes

- fix(blog): resolve issue with missing `Filament\Support\Colors\Color` class in configuration.
- fix(blog): When a blog post has 'published' to false, I should not to be able to see it on the blog index page

### 🚜 Refactor

- refactor(blog): improve layout for blog index cards with updated background and border colors.
- refactor(config): enhance configuration structure for blog index cards.


## v0.1.2 - 2025-08-16
- refactor(config): remove unused table prefix and admin path from blog configuration
- feat(blogr): enhance routing logic for blog routes with optional prefix

## v0.1.1 - 2025-08-16 
- docs: update README with installation instructions, usage, and features

## v0.1.0 - 2025-08-16
- feat(form): add SEO fields (meta_title, meta_description, meta_keywords) and TL;DR field to blog post form
- feat(form): slug is now autogenerated from title but remains editable
- feat(form): user_id is automatically set to the authenticated user
- feat(form): is_published toggle sets published_at to current date/time when activated, resets when deactivated
- feat: add user_id, is_published, published_at, meta_title, meta_description, meta_keywords and tldr fields


## 2025-08-10
- initial release
