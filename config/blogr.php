<?php

use Filament\Support\Colors\Color;


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
        'primary' => Color::Orange

    ]
];
