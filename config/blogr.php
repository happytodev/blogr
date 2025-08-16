<?php

// config for Happytodev/Blogr
return [
    'posts_per_page' => 10,  // Nombre de posts par page
    'route' => [
        // Prefix for frontend routes, if empty, the blog will be the homepage
        'prefix' => 'blog', 
        'middleware' => ['web'], // Middleware for frontend routes
    ],
];
