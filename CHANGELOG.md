# Changelog

All notable changes to `blogr` will be documented in this file.

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
