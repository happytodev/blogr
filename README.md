# Blogr – FilamentPHP Plugin

[![Latest Version on Packagist](https://img.shields.io/packagist/v/happytodev/blogr.svg?style=flat-square)](https://packagist.org/packages/happytodev/blogr)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/happytodev/blogr/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/happytodev/blogr/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/happytodev/blogr/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/happytodev/blogr/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/happytodev/blogr.svg?style=flat-square)](https://packagist.org/packages/happytodev/blogr)

![alt text](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/blogr.webp)

Blogr is a FilamentPHP plugin that adds a powerful blog system to your Laravel application.

## Features

- [x] Create, edit, and delete blog posts
- [x] Edit post in markdown
- [x] Table of contents is automatically generated 
- [x] A post can have a TL;DR
- [x] Support code (currently very simple)
- [x] A blog post can have a category
- [x] A blog post can have tags
- [x] A blog post can be published or not
- [x] Schedule posts for future publication with automatic publishing
- [x] Publication status indicator (draft/scheduled/published) with color coding
- [x] The slug of blog post is automatically generated but can be customized
- [x] Posts per category page
- [x] Posts per tags page
- [x] Image upload and editing
- [x] Automatic author assignment
- [x] Backend color customizable
- [x] Add a reading time information for blog post
- [x] Integrate meta fields 

## Screenshots

### Blog post view

![Blog post view](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/image-1.png)


### Backend - List of posts

![Backend - List of posts](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/image-2.png)

### Backend - Edit post

![Backend - Edit post](https://raw.githubusercontent.com/happytodev/blogr/main/.github/images/image-3.png)

## Roadmap

### Beta 2

- [x] SEO fields (meta title, description, keywords) ✅ **Completed**
- [x] Scheduled publishing ✅ **Completed**
- [x] In the admin in the list of posts, display the toggle for is_published to quickly publish or unpublish ✅ **Completed**
- [x] Add a table of content for blog post ✅ **Completed**
- [x] When no post is published, display a message to user ✅ **Completed**
- [ ] TOC could be deactivate for a post
- [ ] User could define if TOC is activated by default or not for every post
- [x] Add a reading time information for blog post ✅ **Completed**
- [x] Integrate meta fields ✅ **Completed**
- [ ] Add a RSS feed for the blog posts
- [x] Create widgets to display on dashboard ✅ **Completed**
- [ ] Add a settings page to easily manage settings set in config/blogr.php



## Requirements

- **Laravel 12.x**
- **FilamentPHP v4.x**

You have to start with a fresh install of Laravel and Filament v4 or add this package on existing app with these requirements.

## Installation


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
