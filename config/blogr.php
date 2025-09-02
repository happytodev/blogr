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
        'primary' => '#FA2C36'
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
];
