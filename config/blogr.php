<?php

// config for Happytodev/Blogr
return [
    'tables' => [
        'prefix' => '', // Préfixe pour les tables, configurable
    ],
    'admin_path' => 'admin', // Route d’administration par défaut
    'posts_per_page' => 10,  // Nombre de posts par page
    'route' => [
        'prefix' => 'blog', // Préfixe des routes frontend
        'middleware' => ['web'], // Middleware pour les routes frontend
    ],
];
