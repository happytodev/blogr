<?php

// config for Happytodev/Blogr
return [
    'posts_per_page' => 10,  // Number of posts per page
    'route' => [
        'frontend' => [
            'enabled' => true,
        ],
        // Prefix for frontend routes, if empty, the blog will be the homepage
        'prefix' => 'blog',
        // Set to true to make blog the homepage (overrides prefix)
        'homepage' => false,
        'middleware' => ['web'], // Middleware for frontend routes
    ],
    'blog_index' => [
        'cards' => [
            'colors' => [
                // Background color with dark mode support (use empty string to remove)
                'background' => 'bg-white dark:bg-gray-800',
            ]
        ]
    ],
    'colors' => [
        'primary' => '#2dfaa1'
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
    'author_profile' => [
        'enabled' => true, // Enable author profile pages
        'use_slug' => true, // Use slug instead of ID in URLs (requires 'slug' field in users table)
    ],
    
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
    |
    */
    'posts' => [
        'default_image' => '/vendor/blogr/images/default-post.svg', // Default image for posts without photo
        'show_language_switcher' => true, // Show available languages on post page
    ],

    /*
    |--------------------------------------------------------------------------
    | Blog Series Configuration
    |--------------------------------------------------------------------------
    |
    | Configure blog series settings including default images.
    |
    */
    'series' => [
        'enabled' => true,
        'default_image' => '/vendor/blogr/images/default-series.svg', // Default image for series without photo
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
    */
    'locales' => [
        'enabled' => false, // Enable localized routes (/{locale}/blog/...)
        'default' => 'en', // Default locale
        'available' => ['en', 'fr'], // Available locales for translations
    ],

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
                'logo' => env('APP_URL', 'https://yourwebsite.com') . '/images/logo.png',
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
            'show_language_switcher' => true, // Show language switcher in navigation
            'show_theme_switcher' => true, // Show day/night/auto theme switcher
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
        'available' => ['en', 'fr'], // Available locales
    ],

    /*
    |--------------------------------------------------------------------------
    | Posts Configuration
    |--------------------------------------------------------------------------
    |
    | Configure post-specific settings like default images, language indicators, etc.
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
    ],
];
