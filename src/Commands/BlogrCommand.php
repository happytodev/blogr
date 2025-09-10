<?php

namespace Happytodev\Blogr\Commands;

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BlogrCommand extends Command
{
    public $signature = 'blogr {action?} {--force : Skip confirmation prompts}';

    public $description = 'Manage Blogr default content and tutorials';

    public function handle(): int
    {
        $action = $this->argument('action');

        match ($action) {
            'install-tutorials' => $this->installTutorials(),
            'remove-tutorials' => $this->removeTutorials(),
            'list-tutorials' => $this->listTutorials(),
            default => $this->showHelp(),
        };

        return self::SUCCESS;
    }

    protected function showHelp(): void
    {
        $this->info('Blogr Content Management');
        $this->line('Available actions:');
        $this->line('  install-tutorials  Install default tutorial content');
        $this->line('  remove-tutorials   Remove tutorial content');
        $this->line('  list-tutorials     List tutorial content');
        $this->line('');
        $this->line('Usage: php artisan blogr <action>');
        $this->line('');
        $this->line('📋 CURRENT FEATURES:');
        $this->line('  ✅ Blog Posts Management');
        $this->line('  ✅ Categories & Tags');
        $this->line('  ✅ Filament Admin Interface');
        $this->line('  ✅ Dashboard Widgets (Stats, Charts, Recent Posts)');
        $this->line('  ✅ SEO Optimization Helper');
        $this->line('  ✅ Reading Time Calculation');
        $this->line('  ✅ Table of Contents (TOC) Support');
        $this->line('  ✅ Tutorial Content Management');
        $this->line('');
        $this->line('🚀 FUTURE WISHLIST:');
        $this->line('  🔄 User Management');
        $this->line('  🔄 Application Monitoring');
        $this->line('  🔄 Automated Backups');
        $this->line('  🔄 Advanced Debugging Tools');
        $this->line('  🔄 Log Aggregation & Error Tracking');
        $this->line('  🔄 Disaster Recovery Systems');
        $this->line('  🔄 Performance Profiling Tools');
    }

    protected function installTutorials(): void
    {
        $this->info('Installing Blogr tutorial content...');

        if (!$this->option('force') && !$this->confirm('This will create tutorial posts. Continue?')) {
            $this->warn('Installation cancelled.');
            return;
        }

        DB::beginTransaction();
        try {
            // Create tutorial category
            $category = Category::firstOrCreate(
                ['slug' => 'blogr-tutorial'],
                [
                    'name' => 'Blogr Tutorial',
                    'description' => 'Official Blogr tutorial and documentation',
                    'is_active' => true,
                ]
            );

            // Create tutorial user if needed
            $user = User::firstOrCreate(
                ['email' => 'tutorial@blogr.dev'],
                [
                    'name' => 'Blogr Tutorial',
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]
            );

            $tutorialPosts = $this->getTutorialPosts();
            $count = 0;

            foreach ($tutorialPosts as $postData) {
                $post = BlogPost::firstOrCreate(
                    ['slug' => $postData['slug']],
                    array_merge($postData, [
                        'user_id' => $user->id,
                        'category_id' => $category->id,
                        'published_at' => now(),
                    ])
                );

                if ($post->wasRecentlyCreated) {
                    $count++;
                }
            }

            DB::commit();
            $this->info("Successfully installed {$count} tutorial posts.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to install tutorials: ' . $e->getMessage());
        }
    }

    protected function removeTutorials(): void
    {
        $this->info('Removing Blogr tutorial content...');

        if (!$this->option('force') && !$this->confirm('This will permanently delete tutorial posts. Continue?')) {
            $this->warn('Removal cancelled.');
            return;
        }

        $category = Category::where('slug', 'blogr-tutorial')->first();
        if (!$category) {
            $this->warn('No tutorial category found.');
            return;
        }

        $count = BlogPost::where('category_id', $category->id)->delete();
        $category->delete();

        // Remove tutorial user if no other posts
        $user = User::where('email', 'tutorial@blogr.dev')->first();
        if ($user && $user->blogPosts()->count() === 0) {
            $user->delete();
        }

        $this->info("Successfully removed {$count} tutorial posts.");
    }

    protected function listTutorials(): void
    {
        $category = Category::where('slug', 'blogr-tutorial')->first();
        if (!$category) {
            $this->warn('No tutorial category found.');
            return;
        }

        $posts = BlogPost::where('category_id', $category->id)
            ->orderBy('created_at')
            ->get();

        if ($posts->isEmpty()) {
            $this->warn('No tutorial posts found.');
            return;
        }

        $this->info('Blogr Tutorial Posts:');
        $this->line('');

        foreach ($posts as $post) {
            $status = $post->is_published ? 'Published' : 'Draft';
            $this->line("• {$post->title} ({$status})");
        }

        $this->line('');
        $this->info("Total: {$posts->count()} posts");
    }

    protected function getTutorialPosts(): array
    {
        return [
            [
                'title' => 'Welcome to Blogr - Getting Started',
                'slug' => 'welcome-to-blogr-getting-started',
                'content' => $this->getWelcomeContent(),
                'meta_title' => 'Welcome to Blogr - Your Laravel Blogging Platform',
                'meta_description' => 'Get started with Blogr, a powerful Laravel blogging platform with Filament admin panel.',
                'tldr' => 'Welcome to Blogr! This guide will help you get started with your new blogging platform.',
                'is_published' => true,
            ],
            [
                'title' => 'Installing and Configuring Blogr',
                'slug' => 'installing-and-configuring-blogr',
                'content' => $this->getInstallationContent(),
                'meta_title' => 'How to Install and Configure Blogr',
                'meta_description' => 'Step-by-step guide to install and configure Blogr for your Laravel application.',
                'tldr' => 'Learn how to properly install and configure Blogr in your Laravel project.',
                'is_published' => true,
            ],
            [
                'title' => 'Creating Your First Blog Post',
                'slug' => 'creating-your-first-blog-post',
                'content' => $this->getFirstPostContent(),
                'meta_title' => 'Creating Your First Blog Post with Blogr',
                'meta_description' => 'Learn how to create and publish your first blog post using Blogr\'s admin interface.',
                'tldr' => 'Step-by-step guide to create your first blog post with Blogr.',
                'is_published' => true,
            ],
            [
                'title' => 'Understanding Blogr Widgets',
                'slug' => 'understanding-blogr-widgets',
                'content' => $this->getWidgetsContent(),
                'meta_title' => 'Blogr Widgets - Enhance Your Blog',
                'meta_description' => 'Discover how to use Blogr widgets to enhance your blog with dynamic content.',
                'tldr' => 'Learn about Blogr widgets and how they can improve your blog\'s functionality.',
                'is_published' => true,
            ],
            [
                'title' => 'Blogr Settings and Configuration',
                'slug' => 'blogr-settings-and-configuration',
                'content' => $this->getSettingsContent(),
                'meta_title' => 'Configuring Blogr Settings',
                'meta_description' => 'Complete guide to Blogr settings and how to customize your blog\'s behavior.',
                'tldr' => 'Master Blogr settings to customize your blog according to your needs.',
                'is_published' => true,
            ],
            [
                'title' => 'SEO Optimization with Blogr',
                'slug' => 'seo-optimization-with-blogr',
                'content' => $this->getSEOContent(),
                'meta_title' => 'SEO Optimization Guide for Blogr',
                'meta_description' => 'Learn how to optimize your Blogr-powered blog for search engines.',
                'tldr' => 'Improve your blog\'s SEO with Blogr\'s built-in optimization features.',
                'is_published' => true,
            ],
            [
                'title' => 'Advanced Features and Customization',
                'slug' => 'advanced-features-and-customization',
                'content' => $this->getAdvancedContent(),
                'meta_title' => 'Advanced Blogr Features and Customization',
                'meta_description' => 'Explore advanced features and learn how to customize Blogr for your specific needs.',
                'tldr' => 'Discover advanced Blogr features and customization options.',
                'is_published' => true,
            ],
        ];
    }

    protected function getWelcomeContent(): string
    {
        return <<<'MARKDOWN'
# Welcome to Blogr! 🎉

Congratulations on choosing Blogr as your blogging platform! Blogr is a powerful, modern Laravel-based blogging solution that combines the flexibility of Laravel with the beautiful admin interface of Filament.

## What is Blogr?

Blogr is designed to be:
- **Easy to use** - Clean, intuitive interface for content creation
- **SEO-friendly** - Built-in SEO optimization tools
- **Customizable** - Extensive configuration options
- **Scalable** - Handles high-traffic blogs with ease
- **Modern** - Uses the latest Laravel and Filament technologies

## Quick Start

1. **Install Blogr** - Follow our installation guide
2. **Create your first post** - Use the admin panel to publish content
3. **Customize settings** - Configure Blogr to match your needs
4. **Explore widgets** - Enhance your blog with dynamic content

## Need Help?

- Check out our comprehensive documentation
- Join our community forums
- Contact support for personalized assistance

Welcome aboard! 🚀

## Next 

[Installing and Configuring Blogr](installing-and-configuring-blogr)

MARKDOWN;
    }

    protected function getInstallationContent(): string
    {
        return <<<'MARKDOWN'
# Installing and Configuring Blogr

This guide will walk you through the complete installation and configuration process for Blogr.

## Prerequisites

Before installing Blogr, ensure you have:
- PHP 8.1 or higher
- Composer
- Node.js and npm
- A Laravel application

## Installation Steps

The easiest way to install Blogr is using the automated installation command. This command will guide you through the entire installation process and handle most configuration automatically.

### **Install the package via Composer**

```bash
composer require happytodev/blogr
```

### **Run the automated installation**

```bash
php artisan blogr:install
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
   - Publishes migration files
   - Publishes views and assets

2. **🗄️ Runs database migrations**
   - Creates necessary database tables
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

## Troubleshooting

If you encounter issues:
1. Clear all caches: `php artisan optimize:clear`
2. Re-discover packages: `php artisan package:discover`
3. Check file permissions
4. Review Laravel logs for error details

## Next Steps

Once installed, you can:
- Access the admin panel at `/admin`
- Create your first [blog post](creating-your-first-blog-post)
- [Configure settings and preferences](blogr-settings-and-configuration)
- [Explore available widgets](understanding-blogr-widgets)


MARKDOWN;
    }

    protected function getFirstPostContent(): string
    {
        return <<<'MARKDOWN'
# Creating Your First Blog Post

Welcome! This tutorial will guide you through creating and publishing your first blog post with Blogr.

## Accessing the Admin Panel

1. Navigate to your Laravel application's admin URL (usually `/admin`)
2. Log in with your administrator credentials
3. Look for "Blog Posts" in the navigation menu

## Creating a New Post

### Step 1: Start New Post
- Click "New Blog Post" 
- You'll see the post creation form

### Step 2: Basic Information
Fill in the essential details:
- **Title** - Make it descriptive and engaging
- **Featured images** - Eye-catching post thumbnails
- **Category** - Choose a category
- **Tags** - Add or create relevant tags for organization
- **Slug** - URL-friendly version (auto-generated but customizable)

### Step 3: Content Creation
Use the rich text editor to write your content:
- **Markdown support** - Write in Markdown for easy formatting
- **Media uploads** - Insert images and other media
- **Code blocks** - Add syntax-highlighted code
- **Links and formatting** - Full rich text capabilities

### Step 4: SEO Optimization
Optimize for search engines:
- **Meta Title** - Appears in search results
- **Meta Description** - Summary for search engines
- **Meta Keywords** - Target specific search terms

### Step 5: Publishing
Before to save, set the publication status:
- **Draft** - Work in progress
- **Publish** - Make it live
- **Schedule** - Set a future publish date

## Best Practices

### Writing Tips
- **Start with a hook** - Grab readers' attention immediately
- **Use headings** - Break up content for readability
- **Add images** - Visual content engages readers
- **Keep it concise** - Quality over quantity

### SEO Tips
- **Research keywords** - Use tools to find relevant terms
- **Natural language** - Write for humans, not search engines
- **Internal linking** - Link to other posts on your site
- **Mobile-friendly** - Ensure content works on all devices

## Next Steps

After publishing your first post:
1. Share it on social media
2. Check analytics for performance
3. Create more content consistently
4. Engage with your readers

Remember, consistency is key to building an audience. Keep creating valuable content! 📝

Next, you can [explore available widgets](understanding-blogr-widgets)

MARKDOWN;
    }

    protected function getWidgetsContent(): string
    {
        return <<<'MARKDOWN'
# Understanding Blogr Widgets

Widgets are powerful tools that add dynamic content and functionality to your Blogr-powered blog. They provide insights, shortcuts, and enhanced user experiences.

## Available Widgets

### Dashboard Widgets

Currently here are the available widgets included with Blogr:

- Total number of posts
- Total number of published posts
- Number of draft posts
- Number of scheduled posts
- Number of categories
- Number of tags
- Recent blog posts
- List of scheduled posts
- Graphs of posts over time
- Average reading time
- Number of short reading time posts
- Number of medium reading time posts
- Number of long reading time posts

Widgets are powerful tools that can significantly enhance your blog's functionality and user experience. Experiment with different combinations to find what works best for your audience! 🎨

Next, you can [explore Blogr settings](blogr-settings-and-configuration)

MARKDOWN;
    }

    protected function getSettingsContent(): string
    {
        return <<<'MARKDOWN'
# Blogr Settings and Configuration

Blogr offers extensive configuration options to customize your blogging platform according to your specific needs. This guide covers all available settings and how to use them effectively.

## Accessing Settings

### Admin Panel Settings
1. Log in to your admin panel
2. Navigate to **Blogsr > Settings** in the main menu
3. Make your changes and save

### Configuration Files
Advanced users can also modify:
- `config/blogr.php` - Main configuration file
- `.env` file - Environment-specific settings
- Database settings - Through admin interface

## Core Settings

### General Settings

#### Content Settings
- **Posts Per Page** - Number of posts on listing pages
- **Route Prefix** - URL prefix for blog routes

### Appearance Settings

#### Theme Configuration
- **Primary Color** - Main brand color
- **Card Background Color** - Background for cards defined by a Tailwind color (e.g., bg-green-50)
- **Card Border Color** - Border color for cards defined by a Tailwind color (e.g., border-green-600)

### Reading Time Settings

- **Enable Reading Time** - Toggle reading time display
- **Reading Speed** - Words per minute for calculation
- **Text format** - Customize reading time text (e.g., "Reading time: {time}")

### SEO Settings

#### Meta Tags
- **Site Name** - Blog name for meta tags
- **Default Title** - Fallback title for pages
- **Default Description** - Fallback description
- **Twitter Handle** - Twitter username for cards
- **Facebook App ID** - Facebook app integration

#### Structured Data
- **Enable JSON-LD** - Toggle structured data
- **Organization Name** - For structured data
- **Organization URL** - Website URL for structured data
- **Organization Logo URL** - Logo for structured data

#### Table of Contents (TOC) Settings
- **Enable TOC by default** - Enable TOC globally. Individual posts can override this unless strict mode is enabled.
- **Strict Mode** - Force TOC settings from admin panel, ignoring individual post settings.

## Troubleshooting Settings

### Common Issues

#### Configuration Problems
- **Settings Not Saving** - Check file permissions
- **Changes Not Applying** - Clear all caches
- **Database Connection** - Verify database credentials
- **File Upload Issues** - Check upload directory permissions

#### Performance Issues
- **Slow Loading** - Enable caching and optimization
- **Memory Issues** - Increase PHP memory limits
- **Database Slowdown** - Optimize database queries
- **CDN Problems** - Verify CDN configuration

### Getting Help

If you need assistance with settings:
1. Check the documentation
2. Search community forums
3. Review configuration examples
4. Contact me

Remember to backup your settings before making major changes, and test new configurations in a development environment first! ⚙️

Next, you can [explore Blogr SEO optimizations](seo-optimization-with-blogr)


MARKDOWN;
    }

    protected function getSEOContent(): string
    {
        return <<<'MARKDOWN'
# SEO Optimization with Blogr

Search Engine Optimization (SEO) is crucial for driving organic traffic to your blog. Blogr provides comprehensive SEO tools and features to help you optimize your content and improve your search engine rankings.

## SEO Fundamentals

### On-Page SEO

#### Title Optimization
- **Unique Titles** - Each post should have a unique, descriptive title
- **Keyword Placement** - Include target keywords naturally
- **Length Optimization** - Keep titles under 60 characters
- **Branding** - Include your brand name when appropriate

#### Meta Descriptions
- **Compelling Copy** - Write engaging descriptions that encourage clicks
- **Keyword Inclusion** - Include primary keywords naturally
- **Length Guidelines** - Keep under 160 characters
- **Call-to-Action** - Include subtle CTAs when appropriate

#### URL Structure
- **Clean URLs** - Use descriptive, keyword-rich URLs
- **Hyphen Separation** - Use hyphens to separate words
- **Avoid Stop Words** - Remove unnecessary words like "the", "and"
- **Canonical URLs** - Prevent duplicate content issues

### Content Optimization

#### Keyword Research
- **Primary Keywords** - Main topic keywords
- **Secondary Keywords** - Related and supporting keywords
- **Long-tail Keywords** - Specific, longer keyword phrases
- **Search Intent** - Understand what users are looking for

#### Content Structure
- **H1 Tags** - One per page, matches main keyword
- **H2/H3 Tags** - Organize content hierarchy
- **Internal Links** - Link to other relevant posts
- **External Links** - Link to authoritative sources

#### Image Optimization
- **Alt Text** - Descriptive alternative text for images
- **File Names** - Keyword-rich image file names
- **Compression** - Optimize file sizes for faster loading
- **Lazy Loading** - Improve page speed performance

## Blogr SEO Features

### Built-in SEO Tools

- **SEO Fields** - Title, meta description, keywords for posts

## SEO Strategy Implementation

### Content Strategy

#### Keyword Mapping
- **Topic Clusters** - Group related content around topics
- **Content Calendar** - Plan content around target keywords
- **Silo Structure** - Organize content hierarchically
- **User Intent** - Create content that matches search intent

#### Content Optimization Checklist
- [ ] Research target keywords
- [ ] Optimize title and meta description
- [ ] Include keywords in first paragraph
- [ ] Use internal and external links
- [ ] Optimize images with alt text
- [ ] Ensure mobile-friendly design
- [ ] Test page loading speed

### Technical Implementation

#### Site Structure
- **URL Structure** - Clean, descriptive URLs
- **Navigation** - Logical site navigation
- **Internal Linking** - Strategic internal link structure
- **Breadcrumb Navigation** - Easy navigation path

#### Performance Optimization
- **Image Compression** - Reduce image file sizes
- **Caching** - Implement browser and server caching
- **Minification** - Minify CSS, JavaScript, and HTML
- **CDN Usage** - Use Content Delivery Networks

### Monitoring and Analytics

#### SEO Monitoring
- **Rank Tracking** - Monitor keyword rankings
- **Traffic Analysis** - Track organic traffic growth
- **Conversion Tracking** - Monitor goal completions
- **Backlink Analysis** - Track inbound links

#### Tools Integration
- **Google Analytics** - Comprehensive traffic analysis
- **Google Search Console** - Search performance insights
- **Ahrefs/Moz** - Backlink and competitor analysis
- **SEMrush** - Comprehensive SEO analytics

## Advanced SEO Techniques

### Link Building

#### Internal Linking Strategy
- **Contextual Links** - Relevant anchor text
- **Navigation Links** - Include in main navigation
- **Footer Links** - Consistent footer links
- **Related Content** - Link to similar posts

#### External Link Building
- **Guest Posting** - Write for other authoritative sites
- **Resource Pages** - Get listed on resource pages
- **Broken Link Building** - Find and fix broken links
- **Social Media** - Build links through social sharing

### Local SEO (if applicable)

#### Local Optimization
- **Google My Business** - Claim and optimize listing
- **Local Keywords** - Include location-based keywords
- **Local Citations** - Consistent NAP across directories
- **Local Content** - Create location-specific content

### Voice Search Optimization

#### Conversational Content
- **Natural Language** - Write conversationally
- **Question-Based Content** - Answer common questions
- **Featured Snippets** - Optimize for position zero
- **Mobile Optimization** - Voice search is mobile-driven

## SEO Best Practices

### Content Quality
- **Original Content** - Create unique, valuable content
- **Comprehensive Coverage** - Cover topics thoroughly
- **Regular Updates** - Keep content current and fresh
- **User Experience** - Focus on user needs and experience

### Ethical SEO
- **White Hat Techniques** - Follow search engine guidelines
- **Avoid Black Hat** - Don't use manipulative tactics
- **Quality Over Quantity** - Focus on quality content
- **Long-term Strategy** - Build sustainable SEO

### Algorithm Updates
- **Stay Informed** - Follow SEO news and updates
- **Adapt Strategies** - Adjust to algorithm changes
- **Core Updates** - Monitor for major algorithm updates
- **Recovery Planning** - Have contingency plans

## Common SEO Mistakes to Avoid

### Technical Issues
- **Duplicate Content** - Avoid duplicate page content
- **Broken Links** - Fix all broken internal/external links
- **Slow Loading** - Optimize page loading speed
- **Mobile Issues** - Ensure mobile compatibility

### Content Issues
- **Keyword Stuffing** - Don't overuse keywords
- **Thin Content** - Avoid low-quality, thin content
- **Missing Meta Tags** - Always include meta descriptions
- **Poor User Experience** - Don't sacrifice UX for SEO

### Strategy Issues
- **Ignoring Analytics** - Always monitor performance
- **Not Tracking Competitors** - Keep up with competitors
- **Neglecting Mobile** - Mobile is crucial for SEO
- **Ignoring Voice Search** - Prepare for voice search growth

## Measuring SEO Success

### Key Performance Indicators

#### Traffic Metrics
- **Organic Traffic** - Visitors from search engines
- **Keyword Rankings** - Position for target keywords
- **Click-through Rates** - CTR from search results
- **Impressions** - How often content appears in search

#### Engagement Metrics
- **Bounce Rate** - Percentage of single-page visits
- **Time on Page** - Average time spent on pages
- **Pages per Session** - Average pages viewed
- **Conversion Rate** - Goal completion rate

#### Authority Metrics
- **Domain Authority** - Overall domain strength
- **Backlinks** - Number and quality of inbound links
- **Social Signals** - Social media engagement
- **Brand Mentions** - Unlinked brand references

### SEO Reporting

#### Regular Reporting
- **Weekly Reports** - Track short-term changes
- **Monthly Reports** - Analyze monthly performance
- **Quarterly Reviews** - Assess long-term strategy
- **Annual Audits** - Comprehensive SEO health check

#### Tools for Reporting
- **Google Analytics** - Traffic and conversion data
- **Google Search Console** - Search performance data
- **Ahrefs** - Backlink and keyword data
- **SEMrush** - Comprehensive SEO analytics

## Getting Help with SEO

If you need assistance with SEO:
1. **Documentation** - Check Blogr SEO documentation
2. **Community Forums** - Ask the SEO community
3. **Professional Services** - Hire SEO agencies if needed
4. **Courses and Training** - Learn from SEO experts

Remember, SEO is a long-term strategy that requires consistent effort. Focus on creating valuable content for your audience, and the search engine rankings will follow! 🔍

Finally, you can [give a look to advanced content of Blogr](advanced-features-and-customization)

MARKDOWN;
    }

    protected function getAdvancedContent(): string
    {
        return <<<'MARKDOWN'
# Advanced Features and Customization

Blogr provides several advanced features to enhance your blogging experience. Here are the key advanced capabilities available in the current version:

## Content Management Features

### Table of Contents (TOC)
- **Per-post TOC control** - Enable/disable TOC for individual posts via frontmatter
- **Global TOC settings** - Configure default TOC behavior for all posts
- **TOC strict mode** - Prevent individual posts from overriding global settings
- **Automatic generation** - TOC is generated from heading structure (H2-H6)

### SEO Optimization
- **Meta title & description** - Custom SEO fields for each post
- **Open Graph support** - Social media sharing optimization
- **Structured data** - JSON-LD schema markup for search engines
- **Canonical URLs** - Prevent duplicate content issues

### Reading Experience
- **Reading time calculation** - Automatic estimation based on word count
- **TLDR summaries** - Quick content previews
- **Responsive design** - Mobile-optimized reading experience
- **Clean typography** - Optimized for readability

## Dashboard & Analytics

### Available Widgets
- **Blog Stats Overview** - Key metrics and statistics
- **Recent Blog Posts** - Latest published content
- **Scheduled Posts** - Upcoming publications
- **Blog Posts Chart** - Publication trends visualization

### Admin Features
- **Filament integration** - Modern admin interface
- **Bulk operations** - Manage multiple posts efficiently
- **Advanced filtering** - Find content quickly
- **Draft management** - Save and publish workflow


## Future Enhancements (Wishlist)

The following features are planned for future releases:

### 🔄 Advanced Content Features

- **Series** - Group related posts together
- **Custom fields** - Add structured data
- **Comments** - Enable reader interaction
- **Multi-language support** - Create content in multiple languages
- **Improved SEO tools** - Advanced optimization features
- **User roles and permissions** - Fine-grained access control

### 🔄 Backup & Recovery
- **Automated backups** - Scheduled system backups
- **Disaster recovery** - Comprehensive recovery strategies
- **Data replication** - Real-time data synchronization

### 🔄 Image Optimization
- **Automatic Compression** - Reduce image file sizes
- **Format Conversion** - Convert to modern formats (WebP)
- **Responsive Images** - Serve appropriate sizes
- **Lazy Loading** - Load images on demand

### 🔄 Advanced Authentication
- **Multi-factor Authentication** - Enhanced security
- **Social Login** - OAuth integration
- **Password Policies** - Enforce strong passwords

### 🔄 Backup Strategies
- **Full Backups** - Complete system backup
- **Incremental Backups** - Backup only changes
- **Offsite Storage** - Cloud backup storage
- **Backup Encryption** - Secure backup files

---



MARKDOWN;
    }
}
