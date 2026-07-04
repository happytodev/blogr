<?php

// config for Happytodev/Blogr
return [
    'posts_per_page' => 10,  // Number of posts per page

    'admin_path' => env('BLOGR_ADMIN_PATH', 'admin'),  // Admin panel path

    'auto_save_interval' => 30, // Auto-save interval in seconds for blog posts and CMS pages (0 = disabled)

    'route' => [
        'frontend' => [
            'enabled' => true,
        ],
        // Prefix for frontend routes, if empty, the blog will be the homepage
        'prefix' => 'blog',
        // Set to true to make blog the homepage (overrides prefix) - DEPRECATED: use homepage.type instead
        'homepage' => false,
        'middleware' => ['web'], // Middleware for frontend routes
    ],

    /*
    |--------------------------------------------------------------------------
    | Homepage Configuration
    |--------------------------------------------------------------------------
    |
    | Choose what appears at the root URL (/).
    | - 'blog': Shows the blog index (list of posts)
    | - 'cms': Shows a CMS page (you need to create one and mark it as homepage)
    |
    */
    'homepage' => [
        'type' => 'blog', // 'blog' or 'cms'
    ],

    /*
    |--------------------------------------------------------------------------
    | CMS (Static Pages) Configuration
    |--------------------------------------------------------------------------
    |
    | Enable the CMS module for creating static pages like About, Contact, etc.
    | Pages can be accessed via /{prefix}/{slug} or /{slug} if prefix is empty.
    | If CMS is set as homepage, one page should be marked as the homepage page.
    |
     */
    'blog_index' => [
        'cards' => [
            'colors' => [
                // Background color with dark mode support (use empty string to remove)
                'background' => 'bg-white dark:bg-gray-800',
            ],
        ],
    ],
    'colors' => [
        'primary' => '#2dfaa1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Author Profile Configuration
    |--------------------------------------------------------------------------
    |
    | Enable or disable author profile pages and author bio display.
    | When enabled, readers can click on author names to view their profile
    | and all posts written by that author.
    |
    */
    /*
    |--------------------------------------------------------------------------
    | Reading Speed Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the estimated reading speed for calculating post read time.
    | Standard reading speeds:
    | - Slow readers: 150-200 words per minute
    | - Average readers: 200-250 words per minute
    | - Fast readers: 250-300 words per minute
    |
    | The calculation includes the post title and content.
    |
    */
    'reading_speed' => [
        'words_per_minute' => 200, // Average reading speed
    ],

    /*
    |--------------------------------------------------------------------------
    | Reading Time Display Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how reading time is displayed on blog posts.
    | You can enable/disable the display and customize the text format.
    |
    | For multilingual sites, you can define translations for each locale:
    | 'text_format' => [
    |     'en' => 'Reading time: {time}',
    |     'fr' => 'Temps de lecture : {time}',
    | ]
    |
    | Or use a simple string for all locales:
    | 'text_format' => 'Reading time: {time}'
    |
    */
    'reading_time' => [
        'enabled' => true, // Enable/disable reading time display
        'text_format' => [
            'en' => 'Reading time: {time}',
            'fr' => 'Temps de lecture : {time}',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Blog Posts Default Image Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default image for blog posts without a featured image.
    | Images are published from vendor/happytodev/blogr/resources/images
    | to public/vendor/blogr/images during installation.
    |
     */
    /*
    |--------------------------------------------------------------------------
    | Blog Series Configuration
    |--------------------------------------------------------------------------
    |
    | Configure blog series settings including default images.
    | Images are published from vendor/happytodev/blogr/resources/images
    | to public/vendor/blogr/images during installation.
    |
    */
    'series' => [
        'enabled' => true,
        'default_image' => '/vendor/blogr/images/default-series.svg', // Default image for series without photo
        'max_visible_posts' => 10, // Max posts shown in series list before "show more" toggle
        'subtitle' => [
            'en' => 'Browse all our blog series and learn step by step.',
            'fr' => 'Parcourez toutes nos séries et apprenez étape par étape.',
            'es' => 'Explore todas nuestras series y aprende paso a paso.',
            'de' => 'Durchstöbern Sie alle unsere Serien und lernen Sie Schritt für Schritt.',
            'pl' => 'Przeglądaj wszystkie nasze serie i ucz się krok po kroku.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Multilingual Configuration
    |--------------------------------------------------------------------------
    |
    | Configure available locales and default locale for multilingual content.
    | Posts, series, categories, and tags can be translated into these locales.
    | The default locale is used when no translation is available.
    |
    | auto_detect: When true, available locales are automatically detected from
    |   published blog posts and CMS pages. When false (default), the 'available'
    |   list is used as-is.
    | restrict: When auto_detect is true, restrict detected locales to this list.
    |   Useful when you want to allow content in any of these locales but not
    |   display all of them in the language switcher.
    |
     */
    /*
    |--------------------------------------------------------------------------
    | Table of Contents Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the table of contents behavior for blog posts.
    | You can enable/disable TOC globally, and control whether individual
    | posts can override this setting.
    |
    */
    'toc' => [
        'enabled' => true, // TOC Globally Enabled (TGE): Enable/disable TOC globally by default
        'strict_mode' => false, // TOC Strict Mode (TSM): If true, individual posts cannot override global setting
        'position' => 'center', // TOC Position: 'center' (inline), 'left' (sticky sidebar), or 'right' (sticky sidebar)
        'collapsible' => true, // TOC Collapsible: Allow users to collapse/expand the entire TOC by clicking the title
    ],

    /*
    |--------------------------------------------------------------------------
    | Heading Permalink Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the heading permalinks (anchor links) appearance.
    | The symbol appears next to headings and allows direct linking.
    |
    */
    'heading_permalink' => [
        'symbol' => '#', // Character to use for the permalink (e.g., '#', '§', '¶', '🔗')
        'spacing' => 'after', // Spacing: 'none', 'before', 'after', 'both'
        'visibility' => 'hover', // Visibility: 'always' or 'hover'
    ],

    /*
    |--------------------------------------------------------------------------
    | Shiki Syntax Highlighting
    |--------------------------------------------------------------------------
    |
    | Configure server-side syntax highlighting for code blocks rendered by Shiki.
    |
    */
    'shiki' => [
        'line_numbers' => true, // Show line numbers on code blocks
        'light_theme' => 'github-light', // Shiki theme name for light mode
        'dark_theme' => 'github-dark',   // Shiki theme name for dark mode
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Configuration
    |--------------------------------------------------------------------------
    |
    | Configure SEO metadata for better search engine optimization and social sharing.
    | These settings are used for listing pages (index, category, tag) and can be
    | overridden by individual post metadata.
    |
    */
    'seo' => [
        'site_name' => [
            'en' => 'The blog',
            'fr' => 'Le blog',
        ],
        'default_title' => [
            'en' => 'Blog',
            'fr' => 'Blog',
        ],
        'default_description' => [
            'en' => 'Discover our latest articles and insights',
            'fr' => 'Découvrez nos derniers articles et analyses',
        ],
        'default_keywords' => [
            'en' => 'blog, articles, news, insights',
            'fr' => 'blog, articles, news, analyses',
        ],
        'twitter_handle' => '@yourhandle', // Your Twitter handle for Twitter Cards
        'facebook_app_id' => '', // Facebook App ID for Open Graph

        // Open Graph defaults
        'og' => [
            'type' => 'website',
            'image' => '/images/blogr.webp', // Default OG image
            'image_width' => 1200,
            'image_height' => 630,
        ],

        // Structured data
        'structured_data' => [
            'enabled' => true,
            'organization' => [
                'name' => env('APP_NAME', 'My Blog'),
                'url' => env('APP_URL', 'https://yourwebsite.com'),
                'logo' => env('APP_URL', 'https://yourwebsite.com').'/images/logo.png',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the user interface elements like navigation, footer, theme switcher, etc.
    |
    */
    'ui' => [
        'navigation' => [
            'enabled' => true, // Show navigation bar
            'sticky' => true, // Make navigation sticky on scroll
            'show_logo' => true, // Show site logo/name
            'logo' => null, // Path to uploaded logo (null = show site name only)
            'logo_display' => 'text', // 'image', 'text', or 'both'
            'show_language_switcher' => true, // Show language switcher in navigation
            'show_theme_switcher' => true, // Show day/night/auto theme switcher
            'auto_add_blog' => false, // Auto-add "Blog" link to menu when CMS is homepage
        ],
        'dates' => [
            'show_publication_date' => true, // Master toggle: Enable publication dates
            'show_publication_date_on_cards' => true, // Show dates on blog post cards (requires master enabled)
            'show_publication_date_on_articles' => true, // Show dates on article detail pages (requires master enabled)
        ],
        'posts' => [
            'tags_position' => 'bottom', // Position of tags on article page: 'top' or 'bottom'
        ],
        'blog_post_card' => [
            'show_publication_date' => true, // Show publication date on blog post cards (DEPRECATED - use ui.dates instead)
        ],
        'footer' => [
            'enabled' => true, // Show footer
            'text' => '© 2025 My Blog. All rights reserved.', // Footer text (supports HTML)
            'show_social_links' => true, // Show social media links
            'social_links' => [
                'twitter' => 'https://twitter.com/happytodev', // Twitter/X URL
                'github' => 'https://github.com/happytodev', // GitHub URL
                'linkedin' => 'https://linkedin.com/company/happytodev', // LinkedIn URL
                'facebook' => 'https://facebook.com/happytodev', // Facebook URL
                'bluesky' => '', // Bluesky URL (e.g., https://bsky.app/profile/username.bsky.social)
                'youtube' => '', // YouTube URL (e.g., https://youtube.com/@username)
                'instagram' => '', // Instagram URL (e.g., https://instagram.com/username)
                'tiktok' => '', // TikTok URL (e.g., https://tiktok.com/@username)
                'mastodon' => '', // Mastodon URL (e.g., https://mastodon.social/@username)
            ],
        ],
        'appearance' => [
            // Blog post card colors
            'blog_card_bg' => '#ffffff', // Blog card background (light mode)
            'blog_card_bg_dark' => '#1f2937', // Blog card background (dark mode)
            // Series card colors
            'series_card_bg' => '#f9fafb', // Series card background (light mode)
            'series_card_bg_dark' => '#1f2937', // Series card background (dark mode)
        ],
        'back_to_top' => [
            'enabled' => true, // Show floating back-to-top button
            'shape' => 'circle', // Button shape: 'circle' or 'square'
            'color' => null, // Custom color (null = use primary theme color)
        ],
        'theme' => [
            'default' => 'dark', // Default theme: 'light', 'dark', or 'auto'
            // Primary colors
            'primary_color' => '#c20be5', // Primary color (violet/magenta)
            'primary_color_dark' => '#e166fa', // Primary color for dark mode
            'primary_color_hover' => '#d946ef', // Primary color on hover
            'primary_color_hover_dark' => '#e49df2', // Primary color on hover (dark mode)
            // Category colors
            'category_bg' => '#e0f2fe', // Category background (light blue)
            'category_bg_dark' => '#0c4a6e', // Category background (dark mode)
            // Tag colors
            'tag_bg' => '#68fc12', // Tag background (light green)
            'tag_bg_dark' => '#48b00d', // Tag background (dark mode)
            // Author colors
            'author_bg' => '#f2e2f9', // Author background (light amber)
            'author_bg_dark' => '#9b0ab8', // Author background (dark mode)
            // Brand colors — background, buttons, text, highlights
            'bg_color' => '#ffffff',
            'bg_color_dark' => '#111827',
            'btn_color' => '#c20be5',
            'btn_color_dark' => '#e166fa',
            'text_color' => '#1f2937',
            'text_color_dark' => '#f3f4f6',
            'highlight_color' => '#c20be5',
            'highlight_color_dark' => '#e166fa',
            // Testimonial card colors
            'testimonial_bg' => '#ffffff',
            'testimonial_bg_dark' => '#1f2937',
            'testimonial_text' => '#374151',
            'testimonial_text_dark' => '#d1d5db',
            // Font settings
            'font_family' => 'Instrument Sans',
            'font_custom_name' => null,
            'font_custom_url' => null,
            'font_custom_google' => null,
            'font_size_base' => 16,
            // Header & Footer colors (null = auto from bg_color)
            'header_bg' => null,
            'header_bg_dark' => null,
            'header_text' => null,
            'header_text_dark' => null,
            'footer_bg' => null,
            'footer_bg_dark' => null,
            'footer_text' => null,
            'footer_text_dark' => null,
            'header_brightness' => 0,
            'header_brightness_dark' => 0,
            'footer_brightness' => 0,
            'footer_brightness_dark' => 0,
        ],
        'presets' => [
            'magenta' => [
                'label' => 'Magenta (default)',
                'primary_color' => '#c20be5',
                'primary_color_dark' => '#e166fa',
                'primary_color_hover' => '#d946ef',
                'primary_color_hover_dark' => '#e49df2',
                'category_bg' => '#e0f2fe',
                'category_bg_dark' => '#0c4a6e',
                'tag_bg' => '#68fc12',
                'tag_bg_dark' => '#48b00d',
                'author_bg' => '#f2e2f9',
                'author_bg_dark' => '#9b0ab8',
                'bg_color' => '#ffffff',
                'bg_color_dark' => '#111827',
                'btn_color' => '#c20be5',
                'btn_color_dark' => '#e166fa',
                'text_color' => '#1f2937',
                'text_color_dark' => '#f3f4f6',
                'highlight_color' => '#c20be5',
                'highlight_color_dark' => '#e166fa',
                'testimonial_bg' => '#ffffff',
                'testimonial_bg_dark' => '#1f2937',
                'testimonial_text' => '#374151',
                'testimonial_text_dark' => '#d1d5db',
                'font_family' => 'Instrument Sans',
                'header_bg' => null, 'header_bg_dark' => null,
                'header_text' => null, 'header_text_dark' => null,
                'footer_bg' => null, 'footer_bg_dark' => null,
                'footer_text' => null, 'footer_text_dark' => null,
                'header_brightness' => 0, 'header_brightness_dark' => 0,
                'footer_brightness' => 0, 'footer_brightness_dark' => 0,
            ],
            'ocean' => [
                'label' => 'Ocean Blue',
                'primary_color' => '#2563eb',
                'primary_color_dark' => '#60a5fa',
                'primary_color_hover' => '#1d4ed8',
                'primary_color_hover_dark' => '#93c5fd',
                'category_bg' => '#dbeafe',
                'category_bg_dark' => '#1e3a5f',
                'tag_bg' => '#fde68a',
                'tag_bg_dark' => '#92400e',
                'author_bg' => '#e0f2fe',
                'author_bg_dark' => '#075985',
                'bg_color' => '#ffffff',
                'bg_color_dark' => '#0f172a',
                'btn_color' => '#2563eb',
                'btn_color_dark' => '#60a5fa',
                'text_color' => '#1e293b',
                'text_color_dark' => '#f1f5f9',
                'highlight_color' => '#2563eb',
                'highlight_color_dark' => '#60a5fa',
                'testimonial_bg' => '#ffffff',
                'testimonial_bg_dark' => '#1e3a5f',
                'testimonial_text' => '#1e293b',
                'testimonial_text_dark' => '#bfdbfe',
                'font_family' => 'Instrument Sans',
                'header_bg' => null, 'header_bg_dark' => null,
                'header_text' => null, 'header_text_dark' => null,
                'footer_bg' => null, 'footer_bg_dark' => null,
                'footer_text' => null, 'footer_text_dark' => null,
                'header_brightness' => 0, 'header_brightness_dark' => 0,
                'footer_brightness' => 0, 'footer_brightness_dark' => 0,
            ],
            'emerald' => [
                'label' => 'Emerald Green',
                'primary_color' => '#059669',
                'primary_color_dark' => '#34d399',
                'primary_color_hover' => '#047857',
                'primary_color_hover_dark' => '#6ee7b7',
                'category_bg' => '#d1fae5',
                'category_bg_dark' => '#064e3b',
                'tag_bg' => '#fef3c7',
                'tag_bg_dark' => '#78350f',
                'author_bg' => '#ecfdf5',
                'author_bg_dark' => '#065f46',
                'bg_color' => '#ffffff',
                'bg_color_dark' => '#022c22',
                'btn_color' => '#059669',
                'btn_color_dark' => '#34d399',
                'text_color' => '#1c1917',
                'text_color_dark' => '#f5f5f4',
                'highlight_color' => '#059669',
                'highlight_color_dark' => '#34d399',
                'testimonial_bg' => '#ffffff',
                'testimonial_bg_dark' => '#064e3b',
                'testimonial_text' => '#1c1917',
                'testimonial_text_dark' => '#a7f3d0',
                'font_family' => 'Instrument Sans',
                'header_bg' => null, 'header_bg_dark' => null,
                'header_text' => null, 'header_text_dark' => null,
                'footer_bg' => null, 'footer_bg_dark' => null,
                'footer_text' => null, 'footer_text_dark' => null,
                'header_brightness' => 0, 'header_brightness_dark' => 0,
                'footer_brightness' => 0, 'footer_brightness_dark' => 0,
            ],
            'sunset' => [
                'label' => 'Sunset Orange',
                'primary_color' => '#ea580c',
                'primary_color_dark' => '#fb923c',
                'primary_color_hover' => '#c2410c',
                'primary_color_hover_dark' => '#fdba74',
                'category_bg' => '#fff7ed',
                'category_bg_dark' => '#7c2d12',
                'tag_bg' => '#fce7f3',
                'tag_bg_dark' => '#831843',
                'author_bg' => '#ffedd5',
                'author_bg_dark' => '#9a3412',
                'bg_color' => '#ffffff',
                'bg_color_dark' => '#1c1917',
                'btn_color' => '#ea580c',
                'btn_color_dark' => '#fb923c',
                'text_color' => '#1c1917',
                'text_color_dark' => '#fafaf9',
                'highlight_color' => '#ea580c',
                'highlight_color_dark' => '#fb923c',
                'testimonial_bg' => '#ffffff',
                'testimonial_bg_dark' => '#7c2d12',
                'testimonial_text' => '#1c1917',
                'testimonial_text_dark' => '#fed7aa',
                'font_family' => 'Instrument Sans',
                'header_bg' => null, 'header_bg_dark' => null,
                'header_text' => null, 'header_text_dark' => null,
                'footer_bg' => null, 'footer_bg_dark' => null,
                'footer_text' => null, 'footer_text_dark' => null,
                'header_brightness' => 0, 'header_brightness_dark' => 0,
                'footer_brightness' => 0, 'footer_brightness_dark' => 0,
            ],
            'slate' => [
                'label' => 'Slate (minimal)',
                'primary_color' => '#475569',
                'primary_color_dark' => '#94a3b8',
                'primary_color_hover' => '#334155',
                'primary_color_hover_dark' => '#cbd5e1',
                'category_bg' => '#f1f5f9',
                'category_bg_dark' => '#1e293b',
                'tag_bg' => '#e2e8f0',
                'tag_bg_dark' => '#334155',
                'author_bg' => '#f8fafc',
                'author_bg_dark' => '#0f172a',
                'bg_color' => '#ffffff',
                'bg_color_dark' => '#0f172a',
                'btn_color' => '#475569',
                'btn_color_dark' => '#94a3b8',
                'text_color' => '#1e293b',
                'text_color_dark' => '#f1f5f9',
                'highlight_color' => '#475569',
                'highlight_color_dark' => '#94a3b8',
                'testimonial_bg' => '#ffffff',
                'testimonial_bg_dark' => '#1e293b',
                'testimonial_text' => '#1e293b',
                'testimonial_text_dark' => '#cbd5e1',
                'font_family' => 'Instrument Sans',
                'header_bg' => null, 'header_bg_dark' => null,
                'header_text' => null, 'header_text_dark' => null,
                'footer_bg' => null, 'footer_bg_dark' => null,
                'footer_text' => null, 'footer_text_dark' => null,
                'header_brightness' => 0, 'header_brightness_dark' => 0,
                'footer_brightness' => 0, 'footer_brightness_dark' => 0,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Locales Configuration
    |--------------------------------------------------------------------------
    |
    | Configure multilingual support for the blog.
    |
    */
    'locales' => [
        'enabled' => true, // Enable/disable multilingual support
        'default' => 'en', // Default locale
        'available' => ['en', 'fr'], // Available locales (fallback when auto_detect=false)
        'auto_detect' => false, // Auto-detect available locales from published content
        'disabled' => [], // Locales disabled by the user (not shown in frontend switcher)
        'restrict' => [], // Restrict auto-detected locales to this list (empty = no restriction)
    ],

    /*
    |--------------------------------------------------------------------------
    | Posts Configuration
    |--------------------------------------------------------------------------
    |
    | Configure post-specific settings like default images, language indicators, etc.
    | Images are published from vendor/happytodev/blogr/resources/images
    | to public/vendor/blogr/images during installation.
    |
    */
    'posts' => [
        'default_image' => '/vendor/blogr/images/default-post.svg', // Default image for posts without photo
        'show_language_switcher' => true, // Show available translations indicator on posts
    ],

    /*
    |--------------------------------------------------------------------------
    | Author Bio Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the author bio display on blog posts.
    | You can choose to display the author information at the top,
    | bottom, or not at all.
    |
    */
    'author_bio' => [
        'enabled' => true, // Enable/disable author bio display
        'position' => 'bottom', // Options: 'top', 'bottom', 'both'
        'compact' => false, // Use compact version (inline) instead of full bio box
    ],

    /*
    |--------------------------------------------------------------------------
    | Display Configuration
    |--------------------------------------------------------------------------
    |
    | Configure what author information is displayed on articles and series.
    | These settings affect article cards, article detail pages, series cards,
    | and series detail pages.
    |
    */
    'display' => [
        'show_author_pseudo' => true, // Show author pseudo/slug instead of full name
        'show_author_avatar' => true, // Show author avatar thumbnail
        'show_series_authors' => true, // Show series authors (avatars with tooltips) on series cards and pages
        'series_authors_limit' => 4, // Maximum number of author avatars to display before showing "+X"
    ],

    /*
    |--------------------------------------------------------------------------
    | Author Profile
    |--------------------------------------------------------------------------
    |
    | Configure the author profile pages. When enabled, each author will have
    | a dedicated profile page accessible at /blog/author/{userId} that lists
    | all their published posts.
    |
    */
    'author_profile' => [
        'enabled' => true, // Enable/disable author profile pages
        'use_slug' => true, // Use slug instead of ID in URLs (requires 'slug' field in users table)
    ],

    /*
    |--------------------------------------------------------------------------
    | RSS Feed Configuration
    |--------------------------------------------------------------------------
    |
    | Configure RSS feed settings including items limit and description.
    | RSS feeds are available at:
    | - Main feed: /feed or /{locale}/feed
    | - Category feed: /{locale}/feed/category/{slug}
    | - Tag feed: /{locale}/feed/tag/{slug}
    |
    */
    'rss' => [
        'enabled' => true, // Enable/disable RSS feeds
        'items_limit' => 20, // Maximum number of items in the feed
        'description' => 'Latest blog posts', // Default feed description
        'cache_duration' => 3600, // Cache duration in seconds (1 hour)
        'show_in_header' => false, // Show RSS icon in navigation header
        'show_in_footer' => false, // Show RSS icon in footer
    ],

    /*
    |--------------------------------------------------------------------------
    | Sitemap Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the XML sitemap generation. The sitemap includes published
    | blog posts, categories, tags, series, and CMS pages.
    |
    | Routes examples:
    | - /sitemap.xml
    | - /{locale}/sitemap.xml
    |
    */
    'sitemap' => [
        'enabled' => true, // Enable/disable XML sitemap
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact Form Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the contact form submission. The contact form sends emails
    | using Laravel's mail system. You must configure MAIL_MAILER in your
    | .env file (e.g., mailgun, postmark, ses, brevo, smtp) for emails to
    | be delivered reliably.
    |
    */
    'contact' => [
        'to_email' => '', // Recipient email address for contact form submissions
    ],

    /*
    |--------------------------------------------------------------------------
    | CMS Pages Configuration
    |--------------------------------------------------------------------------
    |
    | Configure CMS pages system for static content like About, Contact, etc.
    | Similar to blog routing, you can configure a prefix for CMS pages.
    |
    | Routes examples:
    | - prefix = '' (empty): /about, /contact, /{locale}/about
    | - prefix = 'page': /page/about, /page/contact, /{locale}/page/about
    | - prefix = 'pages': /pages/about, /{locale}/pages/about
    |
    | Note: Homepage (is_homepage = true) is always accessible at / or /{locale}
    | regardless of the prefix setting.
    |
    */
    'cms' => [
        'enabled' => true, // Enable/disable CMS pages feature
        'prefix' => '', // URL prefix (backward compat, use route.prefix for new code)

        'route' => [
            'prefix' => '', // URL prefix for CMS pages (empty = no prefix like WordPress)
            // Examples: '', 'page', 'pages', 'p'
        ],

        'templates' => [
            'default' => 'blogr::cms.pages.default',
            'landing' => 'blogr::cms.pages.landing',
            'contact' => 'blogr::cms.pages.contact',
            'about' => 'blogr::cms.pages.about',
            'pricing' => 'blogr::cms.pages.pricing',
            'faq' => 'blogr::cms.pages.faq',
            'custom' => 'blogr::cms.pages.custom',
        ],

        'blocks' => [
            'enabled' => true, // Enable/disable block system (hero, features, testimonials, etc.)
        ],

        'reserved_slugs' => [
            // Blog routes
            'blog',
            'feed',
            'author',
            'category',
            'tag',
            'series',
            'rss',

            // Authentication routes
            'admin',
            'login',
            'logout',
            'register',
            'password',
            'dashboard',

            // Common system routes
            'api',
            'assets',
            'storage',
            'vendor',
            'livewire',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Configure web analytics tracking for your blog.
    | Supported providers: Google Analytics, Plausible, Umami, Matomo
    |
    | Each provider has its own set of configuration options:
    | - Google Analytics: measurement_id (G-XXXXXXXXXX or UA-XXXXX-X)
    | - Plausible: domain, src (optional, for self-hosted)
    | - Umami: website_id (UUID), src (script URL)
    | - Matomo: url (Matomo instance URL), site_id (numeric)
    |
    */
    'analytics' => [
        'enabled' => false, // Enable/disable analytics tracking
        'provider' => null, // 'google', 'plausible', 'umami', or 'matomo'

        // Google Analytics (GA4 or Universal Analytics)
        'google' => [
            'measurement_id' => null, // e.g., 'G-XXXXXXXXXX' or 'UA-XXXXX-X'
        ],

        // Plausible Analytics (privacy-friendly)
        'plausible' => [
            'domain' => null, // Your site domain (e.g., 'yoursite.com')
            'src' => null, // Script URL (null = default Plausible Cloud: https://plausible.io/js/script.js)
        ],

        // Umami Analytics (open-source, privacy-focused)
        'umami' => [
            'website_id' => null, // Your Website ID (UUID format)
            'src' => null, // Script URL (e.g., 'https://cloud.umami.is/script.js')
        ],

        // Matomo Analytics (self-hosted or cloud)
        'matomo' => [
            'url' => null, // Matomo instance URL (e.g., 'https://matomo.yoursite.com')
            'site_id' => null, // Site ID (numeric)
        ],
    ],

    'translation' => [
        'provider' => env('BLOGR_TRANSLATION_PROVIDER', 'none'),

        'libretranslate' => [
            'url' => env('LIBRETRANSLATE_URL', 'http://localhost:5000'),
        ],

        'azure' => [
            'api_key' => env('AZURE_TRANSLATOR_KEY', ''),
            'region' => env('AZURE_TRANSLATOR_REGION', 'westeurope'),
        ],

        'google' => [
            'api_key' => env('GOOGLE_TRANSLATE_KEY', ''),
        ],

        'openai' => [
            'api_key' => env('OPENAI_API_KEY', ''),
            'model' => 'gpt-4o-mini',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Profile Avatar
    |--------------------------------------------------------------------------
    |
    | When Breezy 2FA is installed, users can upload a profile photo from
    | their profile page. Disable this to hide the avatar upload entirely.
    |
    */
    'enable_avatar_upload' => true,

    /*
    |--------------------------------------------------------------------------
    | Dashboard Widgets Configuration
    |--------------------------------------------------------------------------
    |
    | Choose which widgets appear on the admin dashboard.
    | Leave empty to show all available widgets.
    | Set specific widget class names to show only those.
    |
    */
    'dashboard_widgets' => [],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Cache Duration
    |--------------------------------------------------------------------------
    |
    | How long (in seconds) to cache dashboard statistics before refreshing.
    | Default: 300 seconds (5 minutes). Set to 0 to disable caching.
    |
    */
    'dashboard_cache_duration' => 300,
];
