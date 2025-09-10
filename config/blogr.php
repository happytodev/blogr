<?php

// config for Happytodev/Blogr
return [
    'posts_per_page' => 10,  // Number of posts per page
    'route' => [
        // Prefix for frontend routes, if empty, the blog will be the homepage
        'prefix' => 'blog',
        'middleware' => ['web'], // Middleware for frontend routes
    ],
    'blog_index' => [
        'cards' => [
            'colors' => [
                'background' => 'bg-green-50', // Background color of blog index cards
                'top_border' => 'border-green-600', // Border color of blog index cards
            ]
        ]
    ],
    'colors' => [
        'primary' => '#2dfaa1'
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
    */
    'reading_time' => [
        'enabled' => true, // Enable/disable reading time display
        'text_format' => 'Reading time: {time}', // Text format with {time} placeholder
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
        'site_name' => env('APP_NAME', 'My Blog'), // Your site name
        'default_title' => 'Blog', // Default title for listing pages
        'default_description' => 'Discover our latest articles and insights', // Default description
        'default_keywords' => 'blog, articles, news, insights', // Default keywords
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
];
