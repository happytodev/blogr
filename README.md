# Blogr – FilamentPHP Plugin

[![Latest Version on Packagist](https://img.shields.io/packagist/v/happytodev/blogr.svg?style=flat-square)](https://packagist.org/packages/happytodev/blogr)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/happytodev/blogr/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/happytodev/blogr/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/happytodev/blogr/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/happytodev/blogr/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/happytodev/blogr.svg?style=flat-square)](https://packagist.org/packages/happytodev/blogr)

![alt text](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/blogr.webp)

Blogr is a FilamentPHP plugin that adds a powerful blog system to your Laravel application.

## Features

### 🎯 Blog Series (NEW)

- **Organize posts into series**: Group related posts together for better content structure
- **Automatic navigation**: Previous/Next navigation between posts in a series
- **Featured series**: Highlight important series with a featured flag
- **Position ordering**: Define custom order for posts within a series
- **Multilingual support**: Translate series titles and descriptions
- **Rich UI components**: 
  - Series navigation widget (prev/next)
  - Complete series list view
  - Series badge for posts
  - Breadcrumb with series context

### 🌍 Multilingual Support (NEW)

- **Multiple languages**: Built-in support for en, fr, es, de (extensible)
- **Per-entity translations**: Translate posts, series, categories, and tags
- **Localized routes**: Optional URL structure like `/{locale}/blog/{slug}`
- **Language switcher**: Beautiful dropdown component for language selection
- **SEO optimization**: Automatic hreflang tags for international SEO
- **Default locale**: Fallback to default language when translation is missing
- **Translation management**: Full Filament admin interface for translations

### Content management

- Create, edit, and delete blog posts
- Edit post in markdown
- A post can have a TL;DR
- The post's slug can be custom
- Status : Draft, Scheduled, Published
- Category (one per post)
- Tags (multiple per post)
- Posts by category page
- Posts by tag page
- Post reading time
- Support code (currently very simple)

#### Medias

- Main post image upload and editing
- Drag & Drop image in the post content (see video demo below)

### Table of contents

- Table of contents is automatically generated
- TOC could be deactivate for a post 
- User could define if TOC is activated by default or not for every post

### SEO

- Integrate meta fields 
- Content optimization for SEO


### Settings

- Many settings are available on an admin page or directly in `config/blogr.php`

### Tutorial

- Add default content to help user to start with Blogr

### Widgets

- Widgets available for the dashboard



## Screenshots

### Home page

![Blogr home](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/blogr-home.png)


### Blog post view

![Blog post view](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/image-1.png)

### Series

![Series](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/blogr-series.png)


### Backend - List of posts

![Backend - List of posts](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/image-2.png)

### Backend - Edit post

![Backend - Edit post](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/image-3.png)

### Settings

![Backend - Settings](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/image-4.png)

![Backend - New Settings](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/blogr-new-settings.png)

### Widgets

![Backend - Widgets](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/image-5.png)

## Video demo

### Drag & Drop image in the post content

![alt text](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/demo-1.gif)

## Roadmap

### Beta 2 (ETA 2025-09-15)

- [x] SEO fields (meta title, description, keywords) ✅ **Completed**
- [x] Scheduled publishing ✅ **Completed**
- [x] In the admin in the list of posts, display the toggle for is_published to quickly publish or unpublish ✅ **Completed**
- [x] Add a table of content for blog post ✅ **Completed**
- [x] When no post is published, display a message to user ✅ **Completed**
- [x] Add a reading time information for blog post ✅ **Completed**
- [x] Integrate meta fields ✅ **Completed**
- [x] Create widgets to display on dashboard ✅ **Completed**
- [x] Add a settings page to easily manage settings set in config/blogr.php ✅ **Completed**
- [x] TOC could be deactivate for a post ✅ **Completed**
- [x] User could define if TOC is activated by default or not for every post ✅ **Completed**
- [x] Add default content to help user to start with Blogr ✅ **Completed**


### Beta 3 (ETA 2025-09-30)

- [x] Multilingual
- [x] Series of posts
- [x] Add a writer role, which can write a post but not publish it
- [ ] Add a RSS feed for the blog posts
- [ ] Collapsible TOC
- [ ] Title of blog post in the widget 'recent blog posts' should be clickable
- [ ] Define TOC style like in the eventuallycoding.com blog
- [ ] More to come...

## Requirements

- **Laravel 12.x**
- **FilamentPHP v4.x**

```bash
laravel new mycompanyblog

cd mycompanyblog

composer require filament/filament:"^4.0"

php artisan filament:install --panels

php artisan make:filament-user
```

You have to start with a fresh install of Laravel and Filament v4 or add this package on existing app with these requirements.

## Installation

### Automated Installation (Recommended)

The easiest way to install Blogr is using our automated installation command. After installing the package via Composer, just run one command and let Blogr handle everything!

1. **Install the package via Composer**

```bash
composer require happytodev/blogr
```

2. **Run the automated installation**

```bash
php artisan blogr:install
```

**That's it!** 🎉 The installation command will:
- ✅ Publish configuration and migration files
- ✅ Run database migrations
- ✅ Install tutorial content (optional)
- ✅ Install series examples (optional)
- ✅ Configure Alpine.js in your `resources/js/app.js`
- ✅ Configure Tailwind CSS v4 dark mode in your `resources/css/app.css`
- ✅ Install npm packages (alpinejs, @tailwindcss/typography)
- ✅ Build frontend assets (`npm run build`)
- ✅ Configure the Blogr plugin in your AdminPanelProvider
- ✅ Configure the EditProfile page for user bio and avatar management
- ✅ Create storage symbolic link (for user avatars)

> **⚠️ Important**: If you skip the automated installation or encounter issues with avatar uploads showing permanent "Loading" state, make sure to run:
> ```bash
> php artisan storage:link
> ```
> This command is **required** for the avatar upload feature to work properly. See [STORAGE_CONFIGURATION.md](STORAGE_CONFIGURATION.md) for more details.

All steps are interactive with confirmations, so you have full control over what gets installed.

#### Available Options

The `blogr:install` command supports several options to customize your installation:

- `--skip-npm` - Skip npm dependencies installation
- `--skip-tutorials` - Skip tutorial content installation
- `--skip-series` - Skip series examples installation
- `--skip-frontend` - Skip Alpine.js and Tailwind CSS configuration
- `--skip-build` - Skip asset building step

**Examples:**
```bash
# Install everything (recommended for new installations)
php artisan blogr:install

# Skip building assets (build later manually)
php artisan blogr:install --skip-build

# Skip all frontend configuration (configure manually)
php artisan blogr:install --skip-frontend

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

Edit `app/Providers/Filament/AdminPanelProvider.php`:

```php
use Happytodev\Blogr\BlogrPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configurations
        ->plugins([
            BlogrPlugin::make(),
        ]);
}
```

#### 6. Build assets

```bash
npm run build
```

#### Available Options

The `blogr:install` command supports several options to customize your installation:

- `--skip-npm` - Skip npm dependencies installation
- `--skip-tutorials` - Skip tutorial content installation

**Examples:**
```bash
# Install everything (recommended for new installations)
php artisan blogr:install

# Skip npm installation (if you don't need typography plugin)
php artisan blogr:install --skip-npm

# Skip tutorial content
php artisan blogr:install --skip-tutorials

# Skip both npm and tutorials
php artisan blogr:install --skip-npm --skip-tutorials
```

#### What the command does

The automated installation performs the following steps:

1. **📦 Publishes configuration and migration files**
   - Publishes `config/blogr.php`
   - Publishes Blogr migration files
   - Publishes Blogr assets (default images to `public/vendor/blogr/images/`)
   - Publishes Spatie Permission migrations (for roles & permissions)
   - Publishes views and assets
   - Optionally publishes Spatie Permission config

2. **🗄️ Runs database migrations**
   - Creates necessary database tables
   - Creates roles and permissions tables
   - Handles migration conflicts gracefully

3. **📚 Installs tutorial content** (unless `--skip-tutorials` is used)
   - Creates 7 comprehensive tutorial posts
   - Includes welcome guide, installation help, and advanced features
   - Creates a dedicated "Blogr Tutorial" category

4. **📊 Installs dashboard widgets**
   - BlogStatsOverview - Blog statistics and metrics
   - RecentBlogPosts - Latest posts with quick actions
   - ScheduledPosts - Upcoming scheduled publications
   - BlogPostsChart - Publication trends over time
   - BlogReadingStats - Reading time analytics

5. **📦 Handles npm dependencies** (unless `--skip-npm` is used)
   - Installs `@tailwindcss/typography` if not present
   - Updates `resources/css/app.css` with typography plugin

6. **🔧 Checks AdminPanelProvider configuration**
   - Verifies BlogrPlugin is properly registered
   - Provides guidance if configuration is missing

7. **⭐ Prompts for GitHub star**
   - Asks if you'd like to support the project
   - Completely optional and non-intrusive

#### After installation

Once the command completes successfully, you can:

- **Access your admin panel** at `/admin`
- **View tutorial posts** (if installed) in the "Blogr Tutorial" category
- **Create your first blog post** using the "Blog Posts" section
- **Configure settings** in the "Blogr Settings" page
- **Explore dashboard widgets** for blog analytics

#### Troubleshooting

If you encounter issues:

- **Clear caches**: `php artisan optimize:clear`
- **Re-run migrations**: `php artisan migrate:fresh` (⚠️ This will reset your database)
- **Check file permissions**: Ensure web server can write to storage directories
- **Verify npm installation**: Run `npm install && npm run build` if needed

**Theme Switcher Issues:**

- **Dark mode doesn't work**: Ensure you have added `@variant dark (.dark &);` to your `resources/css/app.css` file (required for Tailwind CSS v4)
- **Auto mode doesn't detect system preference**: 
  - **Windows users**: Check Settings → Personalization → Colors → Choose your mode (set to Dark or Light)
  - ⚠️ **Note**: Windows 11 doesn't have built-in automatic light/dark switching by time of day. You need to manually set Dark/Light mode, or use a third-party app like [Auto Dark Mode](https://github.com/AutoDarkMode/Windows-Auto-Night-Mode)
  - **macOS users**: Check System Settings → Appearance (Dark or Auto mode available)
  - Clear browser cache and localStorage: Open DevTools (F12) → Console → run `localStorage.clear()` then refresh
  - Verify in console: `window.matchMedia('(prefers-color-scheme: dark)').matches` should return `true` if your system is in dark mode
- **Theme doesn't persist**: Check browser console for JavaScript errors, ensure Alpine.js is properly loaded

### Manual installation

If you prefer to install Blogr manually or need more control over the installation process, follow these steps:

1. **Install the package via Composer**

```bash
composer require happytodev/blogr
```

2. **Publish configuration and migration files**

```bash
php artisan vendor:publish --provider="Happytodev\Blogr\BlogrServiceProvider"
```

3. **Run the migrations**

```bash
php artisan migrate
```

4. **Add the plugin in AdminPanelProvider class**

Add this line in your file `app\Providers\Filament\AdminPanelProvider.php`

```php
            ->plugin(BlogrPlugin::make())
```

Don't forget to import the class : 

```php
use Happytodev\Blogr\BlogrPlugin;
``` 

5. **Install typography plugin**

Run `npm install -D @tailwindcss/typography`

6. **Add typography plugin in `resources\css\app.css`**

In `resources\css\app.css`, change : 

```css
@import 'tailwindcss';
@import '../../vendor/livewire/flux/dist/flux.css';
...
```

by 

```css
@import 'tailwindcss';
@import '../../vendor/livewire/flux/dist/flux.css';
@plugin "@tailwindcss/typography";
...
```

7. **Access the blog in Filament**

The plugin adds a Filament resource for managing blog posts.  
Log in to your Filament admin panel and go to the “Blog Posts” section.

## Configuration

You can customize the table prefix in the published config file:  
`config/blogr.php`

### Tailwind CSS v4 Dark Mode Configuration

**⚠️ CRITICAL**: If you're using Tailwind CSS v4, you **MUST** configure the dark mode variant for the theme switcher to work.

Add this line to your `resources/css/app.css`:

```css
@variant dark (.dark &);
```

**Complete example** of a Tailwind CSS v4 configuration compatible with Blogr:

```css
@import 'tailwindcss';

@plugin "@tailwindcss/typography";

/* Include Blogr package views */
@source '../../vendor/happytodev/blogr/resources/views/**/*.blade.php';
@source '../views/vendor/blogr/**/*.blade.php';

/* Your theme customizations */
@theme {
    /* Your theme variables here */
}

/* REQUIRED: Dark mode variant for Blogr theme switcher */
@variant dark (.dark &);
```

**Why is this required?**  
Tailwind CSS v4 doesn't enable class-based dark mode by default. The `@variant dark (.dark &);` directive tells Tailwind to generate dark mode classes (like `dark:bg-gray-900`) that are activated when a parent element has the `dark` class. This is how Blogr's theme switcher controls dark mode.

### Default Open Graph (OG) Image Configuration

To set a default Open Graph image that will be used when your blog posts don't have a specific image:

1. **Place your image** in the `public/images/` folder of your Laravel application
2. **Edit the file** `config/blogr.php`:

```php
'seo' => [
    // ... other SEO configurations ...
    
    'og' => [
        'type' => 'website',
        'image' => '/images/your-og-image.jpg', // Path to your OG image
        'image_width' => 1200, // Recommended width: 1200px
        'image_height' => 630, // Recommended height: 630px
    ],
    
    // ... other configurations ...
],
```

**Recommendations for OG image:**
- **Format**: JPG, PNG or WebP
- **Dimensions**: 1200x630 pixels (1.91:1 ratio)
- **Size**: Less than 1 MB
- **Content**: Your site logo or representative image

### Logo Configuration for Structured Data

To set your organization's logo in the JSON-LD structured data:

1. **Place your logo** in the `public/images/` folder of your Laravel application
2. **Edit the file** `config/blogr.php`:

```php
'seo' => [
    // ... other SEO configurations ...
    
    'structured_data' => [
        'enabled' => true,
        'organization' => [
            'name' => env('APP_NAME', 'My Blog'),
            'url' => env('APP_URL', 'https://yourwebsite.com'),
            'logo' => env('APP_URL', 'https://yourwebsite.com') . '/images/your-logo.png',
        ],
    ],
    
    // ... other configurations ...
],
```

**Recommendations for logo:**
- **Format**: PNG or SVG (transparent preferred)
- **Dimensions**: Minimum 112x112 pixels
- **URL Format**: Complete absolute URL (with https://)

### Complete Configuration Example

```php
'seo' => [
    'site_name' => 'My Awesome Blog',
    'default_title' => 'Blog',
    'default_description' => 'Discover our latest articles',
    'twitter_handle' => '@myblog',
    
    'og' => [
        'type' => 'website',
        'image' => '/images/og-default.jpg',
        'image_width' => 1200,
        'image_height' => 630,
    ],
    
    'structured_data' => [
        'enabled' => true,
        'organization' => [
            'name' => 'My Awesome Blog',
            'url' => 'https://myawesomeblog.com',
            'logo' => 'https://myawesomeblog.com/images/logo.png',
        ],
    ],
],
```
### Reading Time Configuration

Configure the reading time display for your blog posts:

```php
'reading_speed' => [
    'words_per_minute' => 200, // Average reading speed (150-300 recommended)
],

'reading_time' => [
    'enabled' => true, // Enable/disable reading time display
    'text_format' => 'Reading time: {time}', // Customize display text
],
```

**Reading speed recommendations:**
- Slow readers: 150-200 words per minute
- Average readers: 200-250 words per minute
- Fast readers: 250-300 words per minute

### Complete SEO Configuration

Configure all SEO settings for optimal search engine optimization:

```php
'seo' => [
    'site_name' => env('APP_NAME', 'My Blog'), // Your site name
    'default_title' => 'Blog', // Default title for listing pages
    'default_description' => 'Discover our latest articles and insights', // Default meta description
    'default_keywords' => 'blog, articles, news, insights', // Default meta keywords
    'twitter_handle' => '@yourhandle', // Twitter handle for Twitter Cards
    'facebook_app_id' => '', // Facebook App ID for enhanced Open Graph
    
    'og' => [
        'type' => 'website',
        'image' => '/images/og-default.jpg',
        'image_width' => 1200,
        'image_height' => 630,
    ],
    
    'structured_data' => [
        'enabled' => true,
        'organization' => [
            'name' => env('APP_NAME', 'My Blog'),
            'url' => env('APP_URL', 'https://yourwebsite.com'),
            'logo' => env('APP_URL', 'https://yourwebsite.com') . '/images/logo.png',
        ],
    ],
],
```

### Blog Appearance Configuration

Customize the visual appearance of your blog:

```php
'blog_index' => [
    'cards' => [
        'colors' => [
            'background' => 'bg-green-50', // Background color of blog cards
            'top_border' => 'border-green-600', // Border color of blog cards
        ]
    ]
],

'colors' => [
    'primary' => '#FA2C36' // Primary color for the blog
],
```

### Route Configuration

Configure the blog routes and middleware:

```php
'route' => [
    'prefix' => 'blog', // URL prefix (leave empty for homepage)
    'middleware' => ['web'], // Middleware for blog routes
],
```

### Posts Per Page

Control pagination settings:

```php
'posts_per_page' => 10, // Number of posts displayed per page
```

## Dashboard Widgets

Blogr provides powerful dashboard widgets to help you monitor and manage your blog content effectively. These widgets are automatically available in your Filament dashboard once the plugin is installed.

### Available Widgets

#### 📊 BlogStatsOverview
Displays comprehensive statistics about your blog:
- Total number of posts
- Published posts count
- Draft posts count
- Scheduled posts count
- Total categories
- Total tags

Each statistic is displayed with color-coded indicators and descriptive icons.

#### 📝 RecentBlogPosts
Shows a table of the 10 most recent blog posts with:
- Post title (with tooltip for long titles)
- Category (with colored badges)
- Author name
- Publication status (with color coding: published=green, scheduled=yellow, draft=gray)

#### ⏰ ScheduledPosts
Provides an overview of upcoming scheduled publications:
- Posts scheduled for future publication
- Publication dates
- Quick status overview

#### 📈 BlogPostsChart
Interactive chart showing blog post publication trends:
- Monthly publication data for the last 12 months
- Visual representation of content creation patterns
- Helps identify peak publishing periods

#### 📖 BlogReadingStats
Analytics focused on content engagement:
- Reading time statistics
- Average reading times across posts
- Content performance insights

### How to Add Widgets to Your Dashboard

#### Manual Registration
If you need to customize widget placement or behavior, you can manually register them in your `AdminPanelProvider`:

```php
use Happytodev\Blogr\Filament\Widgets\BlogStatsOverview;
use Happytodev\Blogr\Filament\Widgets\RecentBlogPosts;
use Happytodev\Blogr\Filament\Widgets\ScheduledPosts;
use Happytodev\Blogr\Filament\Widgets\BlogPostsChart;
use Happytodev\Blogr\Filament\Widgets\BlogReadingStats;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... other configurations ...
        ->widgets([
            BlogStatsOverview::class,
            RecentBlogPosts::class,
            ScheduledPosts::class,
            BlogPostsChart::class,
            BlogReadingStats::class,
        ])
        // ... other configurations ...
}
```


### Widget Customization

#### Changing Widget Size
Widgets support different column spans:

```php
class CustomBlogStatsOverview extends BlogStatsOverview
{
    protected int | string | array $columnSpan = 'full'; // or 1, 2, 3, etc.
}
```

#### Customizing Chart Data
The BlogPostsChart widget can be extended to show different time periods:

```php
class CustomBlogPostsChart extends BlogPostsChart
{
    protected function getData(): array
    {
        // Custom data logic here
        return [
            'datasets' => [
                [
                    'label' => 'Posts per Month',
                    'data' => [10, 15, 8, 12, 20, 18], // Your custom data
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        ];
    }
}
```

### Widget Permissions
If you're using Filament's permission system, you can control widget visibility:

```php
class BlogStatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->user()->can('view blog stats');
    }
}
```

## � Author Profile & Bio

Blogr provides comprehensive author profile and bio features to showcase your content creators and build trust with your readers.

### Author Profile Page

Each author has a dedicated profile page accessible at `/blog/author/{userId}` (or `/en/blog/author/{userId}` with localized routes).

### Database Migration

**Important:** Blogr automatically adds `avatar` and `bio` fields to your `users` table during installation.

The migration `2025_10_11_000002_add_author_fields_to_users_table.php` adds:
- `avatar` (string, nullable) - URL or path to author's profile picture
- `bio` (text, nullable) - Author's biography text

This migration is automatically run when you execute `php artisan blogr:install` or `php artisan migrate`.

**Features:**
- Author avatar (with automatic letter fallback if no image)
- Author name and email
- Author biography (if provided)
- Statistics (number of published posts)
- Paginated list of all published posts by the author
- Full post cards with images, categories, tags, and reading time

**Access an author profile:**
```blade
<a href="{{ route('blog.author', $post->user_id) }}">
    View author profile
</a>
```

**Note:** Author profile pages can be disabled in the settings if you don't want dedicated author pages.

### Author Bio Component

Display author information within blog posts using the customizable `author-bio` component.

#### Usage

**Full version** (bio box):
```blade
<x-blogr::author-bio :author="$post->user" />
```

**Compact version** (inline):
```blade
<x-blogr::author-bio :author="$post->user" :compact="true" />
```

#### Configuration

Configure author features in **Admin Panel > Settings > Author Bio** or `config/blogr.php`:

```php
// Author profile pages
'author_profile' => [
    'enabled' => true, // Enable/disable author profile pages (/blog/author/{userId})
],

// Author bio component on posts
'author_bio' => [
    'enabled' => true,          // Enable/disable author bio display on posts
    'position' => 'bottom',     // Options: 'top', 'bottom', 'both'
    'compact' => false,         // Use compact version instead of full bio box
],
```

**Settings Page Options:**
- **Enable Author Profile Pages** - Allow/disallow access to `/blog/author/{userId}` pages
- **Display Author Bio** - Show/hide author information on blog posts
- **Author Bio Position** - Choose where to display (top, bottom, or both)
- **Use Compact Version** - Toggle between full bio box and inline compact version

#### Customization

**Custom CSS classes:**
```blade
<x-blogr::author-bio 
    :author="$post->user" 
    class="my-8 shadow-lg" 
/>
```

**Position control:**
- `'top'` - Display at the beginning of the post
- `'bottom'` - Display at the end of the post (default)
- `'both'` - Display at both locations

#### Author Model Fields

The author bio component uses these User model fields:
- `name` - Author name (required)
- `email` - Contact email (optional, displayed with icon)
- `avatar` - Profile picture URL (optional, shows letter fallback if missing)
- `bio` - Author biography text (optional, not displayed if empty)

**These fields are automatically added by the Blogr migration.** You can manage author information through the Filament User Resource:
- Upload profile pictures (avatar field)
- Write biography text (bio field)
- Add contact information (email field)

### Managing Author Profiles

> **Note:** The Edit Profile page is automatically configured during installation via `php artisan blogr:install`. If you installed manually or the configuration is missing, add this to your `AdminPanelProvider.php`:
> ```php
> use Happytodev\Blogr\Filament\Pages\Auth\EditProfile;
> 
> public function panel(Panel $panel): Panel
> {
>     return $panel
>         // ... other configuration
>         ->login()
>         ->profile(EditProfile::class) // Add this line
>         // ... rest of configuration
> }
> ```

**Self-Service Profile Management:**

All authenticated users can manage their own profile through a dedicated profile page:

1. Click on your **user avatar** in the top-right corner
2. Select **"Edit Profile"** from the dropdown menu
3. You'll see two sections:

#### Profile Information
- **Name**: Your display name
- **Email**: Your email address
- **Profile Picture**: Upload an avatar (max 2MB, automatically cropped to circle)
- **Biography**: Write a short bio (max 500 characters)

#### Update Password
- **Current Password**: Required to confirm changes
- **New Password**: Set a new password (min 8 characters)
- **Confirm Password**: Confirm your new password

**For Administrators:**

Administrators can manage user accounts (name, email, role) through:
1. Go to **Admin Panel > Users**
2. Click on a user to edit
3. Modify administrative fields: name, email, email verification, role

**Important Security Note:**
- ✅ **Users can only edit their own profile** (avatar, bio, password)
- ✅ **Admins cannot edit other users' personal information** (avatar, bio) - This respects user privacy
- ✅ **Admins can manage roles and administrative fields** (name, email, role assignment)

This separation ensures user privacy while giving administrators control over user accounts and permissions.

**Behavior when author profile is disabled:**

When you disable author profile pages in settings, the author-bio component will still display author information but without links to the profile page. The `/blog/author/{userId}` routes will return a 404 error.

**Manual migration** (if needed):

If for some reason the migration didn't run automatically, you can add these fields manually:

```php
Schema::table('users', function (Blueprint $table) {
    $table->string('avatar')->nullable()->after('email');
    $table->text('bio')->nullable()->after('avatar');
});
```

## �📚 Blog Series

Blog series allow you to organize related posts together, making it easier for readers to follow a tutorial or thematic content.

### Creating a Series

1. Go to **Admin Panel > Blogr > Blog Series**
2. Click **New Blog Series**
3. Fill in the series information:
   - **Slug**: Unique identifier (e.g., `laravel-beginners`)
   - **Position**: Display order
   - **Is Featured**: Highlight important series
   - **Published At**: Series publication date

### Adding Translations

In the **Translations** section, add content for each language:
- **Locale**: Select language (en, fr, es, de)
- **Title**: Series title in that language
- **Description**: Brief description
- **SEO Title & Description**: For search engines

### Assigning Posts to Series

When creating/editing a blog post:
1. Select **Blog Series** from dropdown
2. Set **Position** to define order within series (1, 2, 3...)
3. Save the post

### Frontend Components

#### Series Navigation (Previous/Next)
Automatically displays at the top/bottom of posts in a series:

```blade
<x-blogr::series-navigation :post="$post" />
```

#### Complete Series List
Shows all posts in a series with progress indicator:

```blade
<x-blogr::series-list :series="$series" :currentPost="$post" />
```

#### Series Badge
Compact indicator showing "Part X/Y":

```blade
<x-blogr::series-badge :post="$post" :showTitle="true" />
```

#### Breadcrumb with Series Context
Includes series in navigation path with Schema.org markup:

```blade
<x-blogr::breadcrumb :post="$post" />
```

### Viewing a Series

Access series at: `/blog/series/{series-slug}`

Example: `/blog/series/laravel-for-beginners`

## 🌍 Multilingual Support

Blogr supports multiple languages for your blog content, with automatic translation management.

### Configuration

Edit `config/blogr.php`:

```php
'locales' => [
    'enabled' => false, // Set to true to enable localized routes
    'default' => 'en', // Default language
    'available' => ['en', 'fr', 'es', 'de'], // Supported languages
],
```

### Settings Page

Configure via **Admin Panel > Settings > Multilingual Settings**:
- **Enable Localized Routes**: Activate URL pattern `/{locale}/blog/...`
- **Default Locale**: Fallback language
- **Available Locales**: Comma-separated list

### Translating Content

#### Blog Posts
1. Create/edit a post
2. In **Translations** tab, click **Add Translation**
3. Select locale and enter translated content
4. Each translation has its own:
   - Title, slug, content
   - SEO fields
   - Reading time (auto-calculated)
   - Categories and tags (per translation)

#### Blog Series
Same process in the Series edit form with translations repeater.

#### Categories & Tags
Categories and tags now support translations:
```php
$category = Category::find(1);
$translation = $category->translate('fr'); // Get French translation
echo $translation->name; // Category name in French
```

### Localized Routes

When enabled (`'enabled' => true`), URLs include locale:

- English: `/en/blog/my-post`
- French: `/fr/blog/mon-article`  
- Spanish: `/es/blog/mi-articulo`

Without localized routes, URLs use default patterns and language is managed by translation relationships.

### Frontend Components

#### Language Switcher
Add to your layout:

```blade
<x-blogr::language-switcher 
    current-route="blog.show" 
    :route-parameters="['slug' => $post->slug]" 
/>
```

#### Hreflang Tags (SEO)
Add in `<head>` section:

```blade
<x-blogr::hreflang-tags 
    current-route="blog.show" 
    :route-parameters="['slug' => $post->slug]" 
/>
```

This generates:
```html
<link rel="alternate" hreflang="en" href="https://example.com/en/blog/post" />
<link rel="alternate" hreflang="fr" href="https://example.com/fr/blog/post" />
<link rel="alternate" hreflang="x-default" href="https://example.com/en/blog/post" />
```

### Translation Helpers

```php
use Happytodev\Blogr\Helpers\LocaleHelper;

// Get current locale
$locale = LocaleHelper::currentLocale(); // 'fr'

// Generate localized URL
$url = LocaleHelper::route('blog.show', ['slug' => 'my-post'], 'fr');

// Get all available locales
$locales = LocaleHelper::availableLocales(); // ['en', 'fr', 'es', 'de']

// Get alternate URLs for hreflang
$urls = LocaleHelper::alternateUrls('blog.show', ['slug' => 'my-post']);
// ['en' => '...', 'fr' => '...', ...]
```

## 🎨 Demo Data Seeder

To quickly visualize blog series and multilingual features, run the demo seeder:

```bash
php artisan db:seed --class="Happytodev\Blogr\Database\Seeders\BlogSeriesSeeder"
```

This creates:
- ✅ 2 blog series with en/fr translations
- ✅ 7 blog posts across both series
- ✅ "Laravel for Beginners" series (4 posts, featured)
- ✅ "Vue.js Best Practices" series (3 posts)
- ✅ Categories and tags
- ✅ Proper ordering and relationships

**View the results:**
- Admin: `/admin/blog-series` and `/admin/blog-posts`
- Frontend: `/blog/series/laravel-for-beginners`

See `database/seeders/README_SEEDERS.md` for full documentation.

## 🧪 Testing

Blogr includes comprehensive test coverage to ensure reliability and prevent regressions.

### Running Tests

```bash
# Run all tests
cd /path/to/packages/happytodev/blogr
php vendor/bin/pest

# Run specific test suites
php vendor/bin/pest tests/Feature/BlogSeriesModelTest.php
php vendor/bin/pest tests/Feature/DatabaseSchemaIntegrityTest.php

# Run with coverage
php vendor/bin/pest --coverage
```

### Test Coverage

#### Model Tests (`BlogSeriesModelTest`)
- ✅ Database schema integrity
- ✅ Model creation with all fields
- ✅ Updating fields without SQL errors
- ✅ Toggle `is_featured` safely
- ✅ Fillable array correctness
- ✅ Photo field handling

#### Schema Integrity Tests (`DatabaseSchemaIntegrityTest`)
- ✅ All required tables exist
- ✅ All columns match model fillable
- ✅ Nullable columns are properly configured
- ✅ Unique constraints are in place
- ✅ Default values are correct
- ✅ Migrations are idempotent

### Adding Your Own Tests

When adding new models or features, follow this pattern:

```php
public function test_can_create_model_without_errors(): void
{
    $model = YourModel::create([
        'field1' => 'value1',
        'field2' => 'value2',
    ]);
    
    $this->assertDatabaseHas('your_table', [
        'field1' => 'value1',
        'field2' => 'value2',
    ]);
}
```


## Support

For questions or bug reports, open an issue on GitHub or contact [happytodev](mailto:happytodev@ik.me).

## Sponsor

If you like this project, you can support me via [GitHub Sponsors](https://github.com/sponsors/happytodev).


## License
MIT

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Frédéric Blanc](https://github.com/happytodev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
