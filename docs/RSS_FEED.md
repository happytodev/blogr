# RSS Feed

## Overview

The RSS Feed feature provides automatic RSS 2.0 feeds for your blog, allowing readers to subscribe and stay updated with your latest posts. Feeds are available for all posts, specific categories, and tags, with full multilingual support.

## Features

- ✅ RSS 2.0 compliant XML feeds
- ✅ Multilingual support (separate feeds per locale)
- ✅ Global feed (all posts)
- ✅ Category-specific feeds
- ✅ Tag-specific feeds
- ✅ Configurable items limit
- ✅ Automatic caching (1 hour by default)
- ✅ Only published posts included
- ✅ Respects post publication dates
- ✅ SEO-friendly with proper metadata

## Available Feeds

### Global Feed
Returns the latest posts across all categories.

**URLs:**
- With locales: `/{locale}/feed` (e.g., `/en/feed`, `/fr/feed`)
- Without locales: `/feed`
- Homepage blog with locales: `/{locale}/feed`
- Prefixed blog: `/{locale}/blog/feed`

**Route name:** `blog.feed`

### Category Feed
Returns the latest posts from a specific category.

**URLs:**
- `/{locale}/feed/category/{categorySlug}`
- Example: `/en/feed/category/tech`

**Route name:** `blog.feed.category`

### Tag Feed
Returns the latest posts tagged with a specific tag.

**URLs:**
- `/{locale}/feed/tag/{tagSlug}`
- Example: `/en/feed/tag/laravel`

**Route name:** `blog.feed.tag`

## Configuration

Add these settings to your `config/blogr.php`:

```php
'rss' => [
    'enabled' => true, // Enable/disable RSS feeds
    'items_limit' => 20, // Maximum number of items in the feed
    'description' => 'Latest blog posts', // Default feed description
    'cache_duration' => 3600, // Cache duration in seconds (1 hour)
],
```

## RSS Feed Structure

Each feed includes:

### Channel Information
- **Title**: Site name (+ category/tag name if filtered)
- **Link**: Blog URL
- **Description**: Feed description
- **Language**: Current locale (e.g., `en`, `fr`)
- **lastBuildDate**: Date of the most recent post
- **atom:link**: Self-reference to the feed URL

### Item Information (per post)
- **title**: Post title in the current locale
- **link**: Direct link to the post
- **guid**: Unique identifier (permalink)
- **pubDate**: Publication date (RFC 2822 format)
- **dc:creator**: Author name
- **author**: Author email and name
- **category**: Post category (translated)
- **category**: Post tags (translated, multiple entries)
- **description**: Post TL;DR or first 300 characters of content

## Usage Examples

### HTML Link Tags

```

## Usage Examples

### HTML Link Tags

Add these to your blog layout to help readers discover your feeds:

```html
<!-- Global feed -->
<link rel="alternate" type="application/rss+xml" 
      title="{{ config('app.name') }} RSS Feed" 
      href="{{ route('blog.feed', ['locale' => app()->getLocale()]) }}">

<!-- Category feed -->
<link rel="alternate" type="application/rss+xml" 
      title="{{ config('app.name') }} - {{ $category->name }} RSS Feed" 
      href="{{ route('blog.feed.category', ['locale' => app()->getLocale(), 'categorySlug' => $category->slug]) }}">
```

### Subscribe Links

```html
<!-- Global feed link -->
<a href="{{ route('blog.feed', ['locale' => app()->getLocale()]) }}" 
   class="rss-link">
    Subscribe to RSS
</a>

<!-- Category feed link -->
<a href="{{ route('blog.feed.category', ['locale' => app()->getLocale(), 'categorySlug' => $category->slug]) }}" 
   class="rss-link">
    Subscribe to {{ $category->name }}
</a>
```

### Blade Component Example

```blade
@if(config('blogr.rss.enabled', true))
    <div class="rss-feeds">
        <h3>Subscribe</h3>
        <ul>
            <li>
                <a href="{{ route('blog.feed', ['locale' => app()->getLocale()]) }}">
                    All Posts RSS Feed
                </a>
            </li>
            @foreach($categories as $category)
                <li>
                    <a href="{{ route('blog.feed.category', ['locale' => app()->getLocale(), 'categorySlug' => $category->slug]) }}">
                        {{ $category->name }} RSS Feed
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif
```

## Technical Details

### Controller

The `RssFeedController` handles all RSS feed requests:

```php
namespace Happytodev\Blogr\Http\Controllers;

class RssFeedController
{
    // Main feed
    public function index(string $locale = null): Response
    
    // Category feed
    public function category(string $locale, string $categorySlug): Response
    
    // Tag feed
    public function tag(string $locale, string $tagSlug): Response
}
```

### Query Optimization

Feeds use eager loading to optimize performance:

```php
BlogPost::with(['user', 'category', 'tags', 'translations'])
    ->where('is_published', true)
    ->whereNotNull('published_at')
    ->where('published_at', '<=', now())
    ->orderBy('published_at', 'desc')
    ->limit($limit)
```

### Caching

RSS feeds are automatically cached for 1 hour (configurable):

```php
return response($xml, 200)
    ->header('Content-Type', 'application/rss+xml; charset=UTF-8')
    ->header('Cache-Control', 'public, max-age=3600');
```

## Multilingual Support

Each locale has its own feed with properly translated content:

- **English**: `/en/feed`
- **French**: `/fr/feed`
- **German**: `/de/feed`

Posts appear only if they have a translation in the requested locale. The feed automatically uses:
- Translated post titles
- Translated TL;DR or content excerpts
- Translated category names
- Translated tag names

## RSS Readers

Your RSS feeds work with all major RSS readers:

- **Web**: Feedly, Inoreader, The Old Reader
- **Desktop**: NetNewsWire, Vienna, Reeder
- **Mobile**: Reeder, Unread, Fiery Feeds
- **Browser Extensions**: RSS Feed Reader, Feedbro

## Troubleshooting

### Feed Returns 404

Check that:
1. `blogr.rss.enabled` is set to `true`
2. Routes are registered correctly
3. Locale is valid
4. Blog routing is configured properly

### No Posts Appear in Feed

Verify that:
1. Posts are published (`is_published = true`)
2. Posts have `published_at` date set
3. `published_at` date is not in the future
4. Posts have translations in the requested locale

### Feed Not Updating

Clear the cache:
```bash
php artisan cache:clear
```

Or wait for the cache to expire (1 hour by default).

## SEO Benefits

RSS feeds improve SEO by:
- Providing structured, machine-readable content
- Enabling content syndication
- Improving content discovery
- Supporting social media aggregators
- Helping search engines index content faster

## Future Enhancements

Potential improvements:
- Full content in feeds (optional)
- Cover images as enclosures
- Audio/video enclosures for media posts
- Atom feed format support
- JSON Feed format support
- Custom feed templates
- Per-author feeds
- Per-series feeds

## Related Features

- **Blog Posts**: Main content source for feeds
- **Categories**: Filter feeds by category
- **Tags**: Filter feeds by tag
- **Translations**: Multi-language feed support
- **Publishing**: Control what appears in feeds

## Standards Compliance

Feeds follow:
- **RSS 2.0 Specification**: https://www.rssboard.org/rss-specification
- **Dublin Core**: For author metadata (`dc:creator`)
- **Atom Namespace**: For self-reference links
- **RFC 2822**: For date formatting
