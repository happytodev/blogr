<?php

namespace Happytodev\Blogr\Database\Seeders;

use Illuminate\Database\Seeder;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\BlogSeriesTranslation;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;

class BlogSeriesSeeder extends Seeder
{
    private $user;

    public function run(): void
    {
        // Get the first user or create one
        $this->user = \App\Models\User::first();
        if (!$this->user) {
            $this->user = \App\Models\User::create([
                'name' => 'Blog Author',
                'email' => 'author@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Create categories
        $categoryLaravel = Category::firstOrCreate(['slug' => 'laravel'], ['name' => 'Laravel']);
        $categoryVue = Category::firstOrCreate(['slug' => 'vue-js'], ['name' => 'Vue.js']);
        $categoryTutorial = Category::firstOrCreate(['slug' => 'tutorial'], ['name' => 'Tutorial']);

        // Create tags
        $tagBeginner = Tag::firstOrCreate(['slug' => 'beginner'], ['name' => 'Beginner']);
        $tagAdvanced = Tag::firstOrCreate(['slug' => 'advanced'], ['name' => 'Advanced']);
        $tagBestPractices = Tag::firstOrCreate(['slug' => 'best-practices'], ['name' => 'Best Practices']);
        $tagTips = Tag::firstOrCreate(['slug' => 'tips'], ['name' => 'Tips']);
        $tagTutorial = Tag::firstOrCreate(['slug' => 'tutorial'], ['name' => 'Tutorial']);

        // ====================================
        // SERIES 1: Laravel for Beginners
        // ====================================
        $series1 = BlogSeries::firstOrCreate(
            ['slug' => 'laravel-for-beginners'],
            [
                'position' => 1,
                'is_featured' => true,
                'published_at' => now()->subDays(30),
            ]
        );

        // Translations for Series 1
        BlogSeriesTranslation::updateOrCreate(
            [
                'blog_series_id' => $series1->id,
                'locale' => 'en',
            ],
            [
                'title' => 'Laravel for Beginners',
                'description' => 'A complete guide to getting started with Laravel framework. Learn the fundamentals and build your first application.',
                'seo_title' => 'Laravel for Beginners - Complete Tutorial Series',
                'seo_description' => 'Master Laravel from scratch with this comprehensive tutorial series. Perfect for PHP developers starting their Laravel journey.',
            ]
        );

        BlogSeriesTranslation::updateOrCreate(
            [
                'blog_series_id' => $series1->id,
                'locale' => 'fr',
            ],
            [
                'title' => 'Laravel pour Débutants',
                'description' => 'Un guide complet pour démarrer avec le framework Laravel. Apprenez les fondamentaux et construisez votre première application.',
                'seo_title' => 'Laravel pour Débutants - Série de Tutoriels Complète',
                'seo_description' => 'Maîtrisez Laravel de zéro avec cette série de tutoriels complète. Parfait pour les développeurs PHP débutant avec Laravel.',
            ]
        );

        // Posts for Series 1
        $this->createPost([
            'series' => $series1,
            'position' => 1,
            'slug' => 'introduction-to-laravel',
            'published_at' => now()->subDays(30),
            'category' => $categoryLaravel,
            'tags' => [$tagBeginner, $tagTutorial],
            'translations' => [
                'en' => [
                    'title' => 'Introduction to Laravel: What and Why?',
                    'content' => $this->getLaravelIntroContent(),
                    'seo_description' => 'Discover what Laravel is and why it has become the most popular PHP framework for web development.',
                ],
                'fr' => [
                    'title' => 'Introduction à Laravel : Quoi et Pourquoi ?',
                    'content' => $this->getLaravelIntroContentFr(),
                    'seo_description' => 'Découvrez ce qu\'est Laravel et pourquoi il est devenu le framework PHP le plus populaire pour le développement web.',
                ],
            ],
        ]);

        $this->createPost([
            'series' => $series1,
            'position' => 2,
            'slug' => 'setting-up-laravel-environment',
            'published_at' => now()->subDays(28),
            'category' => $categoryLaravel,
            'tags' => [$tagBeginner, $tagTutorial],
            'translations' => [
                'en' => [
                    'title' => 'Setting Up Your Laravel Development Environment',
                    'content' => $this->getEnvironmentSetupContent(),
                    'seo_description' => 'Learn how to install and configure Laravel on your local machine. Complete setup guide for beginners.',
                ],
                'fr' => [
                    'title' => 'Configuration de votre Environnement de Développement Laravel',
                    'content' => $this->getEnvironmentSetupContentFr(),
                    'seo_description' => 'Apprenez à installer et configurer Laravel sur votre machine locale. Guide complet pour débutants.',
                ],
            ],
        ]);

        $this->createPost([
            'series' => $series1,
            'position' => 3,
            'slug' => 'laravel-routing-basics',
            'published_at' => now()->subDays(26),
            'category' => $categoryLaravel,
            'tags' => [$tagBeginner],
            'translations' => [
                'en' => [
                    'title' => 'Laravel Routing Basics: Your First Routes',
                    'content' => $this->getRoutingBasicsContent(),
                    'seo_description' => 'Master Laravel routing fundamentals. Learn how to create and manage routes in your Laravel application.',
                ],
                'fr' => [
                    'title' => 'Les Bases du Routage Laravel : Vos Premières Routes',
                    'content' => $this->getRoutingBasicsContentFr(),
                    'seo_description' => 'Maîtrisez les fondamentaux du routage Laravel. Apprenez à créer et gérer les routes dans votre application.',
                ],
            ],
        ]);

        $this->createPost([
            'series' => $series1,
            'position' => 4,
            'slug' => 'laravel-controllers-and-views',
            'published_at' => now()->subDays(24),
            'category' => $categoryLaravel,
            'tags' => [$tagBeginner],
            'translations' => [
                'en' => [
                    'title' => 'Controllers and Views: Building Your UI',
                    'content' => $this->getControllersViewsContent(),
                    'seo_description' => 'Learn how to use controllers and Blade views to build dynamic user interfaces in Laravel.',
                ],
                'fr' => [
                    'title' => 'Contrôleurs et Vues : Construire votre Interface',
                    'content' => $this->getControllersViewsContentFr(),
                    'seo_description' => 'Apprenez à utiliser les contrôleurs et les vues Blade pour créer des interfaces utilisateur dynamiques.',
                ],
            ],
        ]);

        // ====================================
        // SERIES 2: Vue.js Best Practices
        // ====================================
        $series2 = BlogSeries::firstOrCreate(
            ['slug' => 'vue-best-practices'],
            [
                'position' => 2,
                'is_featured' => false,
                'published_at' => now()->subDays(20),
            ]
        );

        // Translations for Series 2
        BlogSeriesTranslation::updateOrCreate(
            [
                'blog_series_id' => $series2->id,
                'locale' => 'en',
            ],
            [
                'title' => 'Vue.js Best Practices',
                'description' => 'Level up your Vue.js skills with proven best practices and patterns for building scalable applications.',
                'seo_title' => 'Vue.js Best Practices - Advanced Techniques',
                'seo_description' => 'Learn advanced Vue.js patterns and best practices for building maintainable, scalable applications.',
            ]
        );

        BlogSeriesTranslation::updateOrCreate(
            [
                'blog_series_id' => $series2->id,
                'locale' => 'fr',
            ],
            [
                'title' => 'Meilleures Pratiques Vue.js',
                'description' => 'Améliorez vos compétences Vue.js avec des pratiques éprouvées et des modèles pour créer des applications évolutives.',
                'seo_title' => 'Meilleures Pratiques Vue.js - Techniques Avancées',
                'seo_description' => 'Apprenez les modèles avancés et meilleures pratiques Vue.js pour créer des applications maintenables et évolutives.',
            ]
        );

        // Posts for Series 2
        $this->createPost([
            'series' => $series2,
            'position' => 1,
            'slug' => 'vue-component-architecture',
            'published_at' => now()->subDays(20),
            'category' => $categoryVue,
            'tags' => [$tagAdvanced, $tagBestPractices],
            'translations' => [
                'en' => [
                    'title' => 'Vue Component Architecture: Design Patterns',
                    'content' => $this->getVueComponentContent(),
                    'seo_description' => 'Master Vue.js component architecture with proven design patterns for scalable applications.',
                ],
                'fr' => [
                    'title' => 'Architecture des Composants Vue : Modèles de Conception',
                    'content' => $this->getVueComponentContentFr(),
                    'seo_description' => 'Maîtrisez l\'architecture des composants Vue.js avec des modèles de conception éprouvés.',
                ],
            ],
        ]);

        $this->createPost([
            'series' => $series2,
            'position' => 2,
            'slug' => 'vue-state-management',
            'published_at' => now()->subDays(18),
            'category' => $categoryVue,
            'tags' => [$tagAdvanced, $tagBestPractices],
            'translations' => [
                'en' => [
                    'title' => 'State Management: Pinia vs Vuex',
                    'content' => $this->getVueStateContent(),
                    'seo_description' => 'Compare Pinia and Vuex for Vue.js state management. Learn which solution fits your needs.',
                ],
                'fr' => [
                    'title' => 'Gestion d\'État : Pinia vs Vuex',
                    'content' => $this->getVueStateContentFr(),
                    'seo_description' => 'Comparez Pinia et Vuex pour la gestion d\'état Vue.js. Apprenez quelle solution convient à vos besoins.',
                ],
            ],
        ]);

        $this->createPost([
            'series' => $series2,
            'position' => 3,
            'slug' => 'vue-performance-optimization',
            'published_at' => now()->subDays(16),
            'category' => $categoryVue,
            'tags' => [$tagAdvanced, $tagTips],
            'translations' => [
                'en' => [
                    'title' => 'Performance Optimization Tips for Vue.js',
                    'content' => $this->getVuePerformanceContent(),
                    'seo_description' => 'Boost your Vue.js application performance with these proven optimization techniques.',
                ],
                'fr' => [
                    'title' => 'Conseils d\'Optimisation des Performances pour Vue.js',
                    'content' => $this->getVuePerformanceContentFr(),
                    'seo_description' => 'Améliorez les performances de votre application Vue.js avec ces techniques d\'optimisation éprouvées.',
                ],
            ],
        ]);

        $this->info('✅ Created 2 blog series with 7 posts total');
        $this->info('��� Series 1: Laravel for Beginners (4 posts, featured)');
        $this->info('��� Series 2: Vue.js Best Practices (3 posts)');
    }

    private function createPost(array $data): void
    {
        $post = BlogPost::create([
            'blog_series_id' => $data['series']->id,
            'series_position' => $data['position'],
            'category_id' => $data['category']->id,
            'user_id' => $this->user->id,
            'is_published' => true,
            'published_at' => $data['published_at'],
        ]);

        // Create or update translations
        foreach ($data['translations'] as $locale => $translation) {
            $postTranslation = BlogPostTranslation::updateOrCreate(
                [
                    'blog_post_id' => $post->id,
                    'locale' => $locale,
                ],
                [
                    'title' => $translation['title'],
                    'slug' => $data['slug'] . ($locale !== 'en' ? '-' . $locale : ''),
                    'content' => $translation['content'],
                    'seo_title' => $translation['title'],
                    'seo_description' => $translation['seo_description'],
                ]
            );

            // Sync tags to translation (avoid duplicates)
            $tagIds = collect($data['tags'])->pluck('id')->toArray();
            $postTranslation->tags()->sync($tagIds);
        }
    }

    private function info(string $message): void
    {
        if (isset($this->command) && method_exists($this->command, 'info')) {
            $this->command->info($message);
        } else {
            echo $message . PHP_EOL;
        }
    }

    // Content generators
    private function getLaravelIntroContent(): string
    {
        return <<<'MARKDOWN'
# What is Laravel?

Laravel is a **powerful PHP framework** designed to make web development enjoyable and creative. Created by Taylor Otwell in 2011, it has become the most popular PHP framework in the world.

## Why Choose Laravel?

### 1. Elegant Syntax
Laravel provides an expressive, elegant syntax that makes coding a pleasure. The framework follows the MVC (Model-View-Controller) pattern and emphasizes clean, readable code.

### 2. Rich Ecosystem
- **Eloquent ORM**: Beautiful database interactions
- **Blade Templates**: Powerful templating engine
- **Artisan CLI**: Command-line tools for productivity
- **Laravel Mix**: Asset compilation made simple

### 3. Developer Experience
Laravel prioritizes developer happiness with:
- Comprehensive documentation
- Active community support
- Regular updates and LTS versions
- Built-in testing capabilities

## Getting Started

In the next posts, we'll dive into:
1. Setting up your development environment
2. Understanding Laravel's routing system
3. Building your first controller and view
4. Working with databases using Eloquent

Stay tuned for an exciting journey into Laravel!
MARKDOWN;
    }

    private function getLaravelIntroContentFr(): string
    {
        return <<<'MARKDOWN'
# Qu'est-ce que Laravel ?

Laravel est un **framework PHP puissant** conçu pour rendre le développement web agréable et créatif. Créé par Taylor Otwell en 2011, il est devenu le framework PHP le plus populaire au monde.

## Pourquoi Choisir Laravel ?

### 1. Syntaxe Élégante
Laravel offre une syntaxe expressive et élégante qui rend le codage un plaisir. Le framework suit le modèle MVC (Modèle-Vue-Contrôleur) et met l'accent sur un code propre et lisible.

### 2. Écosystème Riche
- **Eloquent ORM**: Interactions de base de données élégantes
- **Templates Blade**: Moteur de templates puissant
- **CLI Artisan**: Outils en ligne de commande pour la productivité
- **Laravel Mix**: Compilation d'assets simplifiée

### 3. Expérience Développeur
Laravel privilégie le bonheur des développeurs avec :
- Documentation complète
- Support communautaire actif
- Mises à jour régulières et versions LTS
- Capacités de test intégrées

## Pour Commencer

Dans les prochains articles, nous explorerons :
1. Configuration de votre environnement de développement
2. Comprendre le système de routage de Laravel
3. Créer votre premier contrôleur et vue
4. Travailler avec les bases de données via Eloquent

Restez à l'écoute pour un voyage passionnant dans Laravel !
MARKDOWN;
    }

    private function getEnvironmentSetupContent(): string
    {
        return <<<'MARKDOWN'
# Setting Up Laravel

Let's get your Laravel development environment ready! This guide covers everything you need to start building Laravel applications.

## Prerequisites

Before installing Laravel, ensure you have:
- PHP 8.2 or higher
- Composer (dependency manager)
- A database (MySQL, PostgreSQL, or SQLite)
- A code editor (VS Code, PhpStorm, etc.)

## Installation Steps

### 1. Install Composer
```bash
# macOS/Linux
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Windows: Download from getcomposer.org
```

### 2. Create a New Laravel Project
```bash
composer create-project laravel/laravel my-app
cd my-app
```

### 3. Start the Development Server
```bash
php artisan serve
```

Visit `http://localhost:8000` to see your Laravel welcome page!

## Configuration

### Database Setup
Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=my_database
DB_USERNAME=root
DB_PASSWORD=
```

### Run Migrations
```bash
php artisan migrate
```

## Next Steps

Now that your environment is ready, we'll explore Laravel's routing system in the next post!
MARKDOWN;
    }

    private function getEnvironmentSetupContentFr(): string
    {
        return <<<'MARKDOWN'
# Configuration de Laravel

Préparons votre environnement de développement Laravel ! Ce guide couvre tout ce dont vous avez besoin pour commencer à créer des applications Laravel.

## Prérequis

Avant d'installer Laravel, assurez-vous d'avoir :
- PHP 8.2 ou supérieur
- Composer (gestionnaire de dépendances)
- Une base de données (MySQL, PostgreSQL ou SQLite)
- Un éditeur de code (VS Code, PhpStorm, etc.)

## Étapes d'Installation

### 1. Installer Composer
```bash
# macOS/Linux
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Windows : Télécharger depuis getcomposer.org
```

### 2. Créer un Nouveau Projet Laravel
```bash
composer create-project laravel/laravel mon-app
cd mon-app
```

### 3. Démarrer le Serveur de Développement
```bash
php artisan serve
```

Visitez `http://localhost:8000` pour voir votre page d'accueil Laravel !

## Configuration

### Configuration de la Base de Données
Éditez le fichier `.env` :
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ma_base
DB_USERNAME=root
DB_PASSWORD=
```

### Exécuter les Migrations
```bash
php artisan migrate
```

## Prochaines Étapes

Maintenant que votre environnement est prêt, nous explorerons le système de routage de Laravel dans le prochain article !
MARKDOWN;
    }

    private function getRoutingBasicsContent(): string
    {
        return <<<'MARKDOWN'
# Laravel Routing Basics

Routing is the foundation of any web application. Laravel makes routing incredibly simple and expressive.

## Basic Routes

### Simple GET Route
```php
Route::get('/hello', function () {
    return 'Hello World!';
});
```

### Route Parameters
```php
Route::get('/user/{id}', function ($id) {
    return "User ID: $id";
});
```

### Optional Parameters
```php
Route::get('/user/{name?}', function ($name = 'Guest') {
    return "Hello, $name!";
});
```

## Named Routes

Named routes make it easy to generate URLs:

```php
Route::get('/profile', [ProfileController::class, 'show'])
    ->name('profile');

// Generate URL
$url = route('profile');
```

## Route Groups

Organize related routes efficiently:

```php
Route::prefix('admin')->group(function () {
    Route::get('/users', [AdminController::class, 'users']);
    Route::get('/posts', [AdminController::class, 'posts']);
});
```

## HTTP Methods

Laravel supports all HTTP verbs:

```php
Route::get('/posts', [PostController::class, 'index']);
Route::post('/posts', [PostController::class, 'store']);
Route::put('/posts/{id}', [PostController::class, 'update']);
Route::delete('/posts/{id}', [PostController::class, 'destroy']);
```

## Coming Up

In the next post, we'll learn how to create controllers and views to handle these routes!
MARKDOWN;
    }

    private function getRoutingBasicsContentFr(): string
    {
        return <<<'MARKDOWN'
# Les Bases du Routage Laravel

Le routage est la fondation de toute application web. Laravel rend le routage incroyablement simple et expressif.

## Routes de Base

### Route GET Simple
```php
Route::get('/hello', function () {
    return 'Bonjour le Monde !';
});
```

### Paramètres de Route
```php
Route::get('/utilisateur/{id}', function ($id) {
    return "ID Utilisateur : $id";
});
```

### Paramètres Optionnels
```php
Route::get('/utilisateur/{nom?}', function ($nom = 'Invité') {
    return "Bonjour, $nom !";
});
```

## Routes Nommées

Les routes nommées facilitent la génération d'URLs :

```php
Route::get('/profil', [ProfilController::class, 'show'])
    ->name('profil');

// Générer l'URL
$url = route('profil');
```

## Groupes de Routes

Organisez efficacement les routes liées :

```php
Route::prefix('admin')->group(function () {
    Route::get('/utilisateurs', [AdminController::class, 'users']);
    Route::get('/articles', [AdminController::class, 'posts']);
});
```

## Méthodes HTTP

Laravel supporte tous les verbes HTTP :

```php
Route::get('/posts', [PostController::class, 'index']);
Route::post('/posts', [PostController::class, 'store']);
Route::put('/posts/{id}', [PostController::class, 'update']);
Route::delete('/posts/{id}', [PostController::class, 'destroy']);
```

## À Venir

Dans le prochain article, nous apprendrons à créer des contrôleurs et des vues pour gérer ces routes !
MARKDOWN;
    }

    private function getControllersViewsContent(): string
    {
        return <<<'MARKDOWN'
# Controllers and Views in Laravel

Now that you understand routing, let's learn how to build dynamic UIs with controllers and Blade views.

## Creating a Controller

Generate a controller using Artisan:

```bash
php artisan make:controller PostController
```

### Basic Controller Example

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();
        return view('posts.index', ['posts' => $posts]);
    }
    
    public function show($id)
    {
        $post = Post::findOrFail($id);
        return view('posts.show', compact('post'));
    }
}
```

## Blade Templates

Blade is Laravel's powerful templating engine.

### Layout File (`resources/views/layouts/app.blade.php`)

```blade
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>
</head>
<body>
    @yield('content')
</body>
</html>
```

### View File (`resources/views/posts/index.blade.php`)

```blade
@extends('layouts.app')

@section('title', 'All Posts')

@section('content')
    <h1>Blog Posts</h1>
    @foreach($posts as $post)
        <article>
            <h2>{{ $post->title }}</h2>
            <p>{{ $post->excerpt }}</p>
        </article>
    @endforeach
@endsection
```

## Blade Directives

### Conditionals
```blade
@if($user->isAdmin())
    <p>Welcome, Admin!</p>
@else
    <p>Welcome, User!</p>
@endif
```

### Loops
```blade
@foreach($items as $item)
    <li>{{ $item->name }}</li>
@endforeach
```

### Components
```blade
<x-alert type="success">
    Operation completed successfully!
</x-alert>
```

## Wrapping Up

You now know how to:
- Create controllers
- Pass data to views
- Use Blade templates
- Work with directives

This completes our Laravel for Beginners series. You're now ready to build your own Laravel applications!
MARKDOWN;
    }

    private function getControllersViewsContentFr(): string
    {
        return <<<'MARKDOWN'
# Contrôleurs et Vues dans Laravel

Maintenant que vous comprenez le routage, apprenons à créer des interfaces dynamiques avec les contrôleurs et les vues Blade.

## Créer un Contrôleur

Générez un contrôleur avec Artisan :

```bash
php artisan make:controller PostController
```

### Exemple de Contrôleur Basique

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();
        return view('posts.index', ['posts' => $posts]);
    }
    
    public function show($id)
    {
        $post = Post::findOrFail($id);
        return view('posts.show', compact('post'));
    }
}
```

## Templates Blade

Blade est le moteur de templates puissant de Laravel.

### Fichier de Layout (`resources/views/layouts/app.blade.php`)

```blade
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>
</head>
<body>
    @yield('content')
</body>
</html>
```

### Fichier de Vue (`resources/views/posts/index.blade.php`)

```blade
@extends('layouts.app')

@section('title', 'Tous les Articles')

@section('content')
    <h1>Articles du Blog</h1>
    @foreach($posts as $post)
        <article>
            <h2>{{ $post->title }}</h2>
            <p>{{ $post->excerpt }}</p>
        </article>
    @endforeach
@endsection
```

## Directives Blade

### Conditions
```blade
@if($user->isAdmin())
    <p>Bienvenue, Admin !</p>
@else
    <p>Bienvenue, Utilisateur !</p>
@endif
```

### Boucles
```blade
@foreach($items as $item)
    <li>{{ $item->name }}</li>
@endforeach
```

### Composants
```blade
<x-alert type="success">
    Opération terminée avec succès !
</x-alert>
```

## Conclusion

Vous savez maintenant comment :
- Créer des contrôleurs
- Passer des données aux vues
- Utiliser les templates Blade
- Travailler avec les directives

Ceci conclut notre série Laravel pour Débutants. Vous êtes maintenant prêt à créer vos propres applications Laravel !
MARKDOWN;
    }

    private function getVueComponentContent(): string
    {
        return <<<'MARKDOWN'
# Vue Component Architecture

Building scalable Vue applications requires thoughtful component architecture. Let's explore proven patterns.

## Component Design Principles

### 1. Single Responsibility
Each component should have one clear purpose:

```vue
<!-- Good: Focused component -->
<template>
  <button @click="handleClick" :disabled="loading">
    {{ label }}
  </button>
</template>

<!-- Bad: Too many responsibilities -->
<template>
  <div>
    <header>...</header>
    <nav>...</nav>
    <main>...</main>
    <footer>...</footer>
  </div>
</template>
```

### 2. Props Down, Events Up
```vue
<script setup>
// Parent
const items = ref([])
const handleAdd = (item) => items.value.push(item)
</script>

<template>
  <ItemList :items="items" @add="handleAdd" />
</template>
```

## Component Categories

### Presentational Components
Focus on UI, receive data via props:

```vue
<script setup>
defineProps({
  user: Object,
  theme: String
})
</script>

<template>
  <div :class="`card card-${theme}`">
    <h3>{{ user.name }}</h3>
    <p>{{ user.email }}</p>
  </div>
</template>
```

### Container Components
Handle logic and state:

```vue
<script setup>
import { ref, onMounted } from 'vue'
import UserCard from './UserCard.vue'

const users = ref([])

onMounted(async () => {
  users.value = await fetchUsers()
})
</script>

<template>
  <div>
    <UserCard 
      v-for="user in users" 
      :key="user.id" 
      :user="user" 
    />
  </div>
</template>
```

## Composition API Patterns

### Composables for Reusability
```javascript
// useAuth.js
export function useAuth() {
  const user = ref(null)
  const isAuthenticated = computed(() => !!user.value)
  
  const login = async (credentials) => {
    user.value = await api.login(credentials)
  }
  
  return { user, isAuthenticated, login }
}
```

## Next Steps

In the next post, we'll dive into state management with Pinia and Vuex!
MARKDOWN;
    }

    private function getVueComponentContentFr(): string
    {
        return <<<'MARKDOWN'
# Architecture des Composants Vue

Construire des applications Vue évolutives nécessite une architecture de composants réfléchie. Explorons les modèles éprouvés.

## Principes de Conception des Composants

### 1. Responsabilité Unique
Chaque composant doit avoir un but clair :

```vue
<!-- Bon : Composant focalisé -->
<template>
  <button @click="handleClick" :disabled="loading">
    {{ label }}
  </button>
</template>

<!-- Mauvais : Trop de responsabilités -->
<template>
  <div>
    <header>...</header>
    <nav>...</nav>
    <main>...</main>
    <footer>...</footer>
  </div>
</template>
```

### 2. Props Descendantes, Événements Ascendants
```vue
<script setup>
// Parent
const items = ref([])
const handleAdd = (item) => items.value.push(item)
</script>

<template>
  <ItemList :items="items" @add="handleAdd" />
</template>
```

## Catégories de Composants

### Composants de Présentation
Focus sur l'UI, reçoivent les données via props :

```vue
<script setup>
defineProps({
  user: Object,
  theme: String
})
</script>

<template>
  <div :class="`card card-${theme}`">
    <h3>{{ user.name }}</h3>
    <p>{{ user.email }}</p>
  </div>
</template>
```

### Composants Conteneurs
Gèrent la logique et l'état :

```vue
<script setup>
import { ref, onMounted } from 'vue'
import UserCard from './UserCard.vue'

const users = ref([])

onMounted(async () => {
  users.value = await fetchUsers()
})
</script>

<template>
  <div>
    <UserCard 
      v-for="user in users" 
      :key="user.id" 
      :user="user" 
    />
  </div>
</template>
```

## Modèles de l'API Composition

### Composables pour la Réutilisabilité
```javascript
// useAuth.js
export function useAuth() {
  const user = ref(null)
  const isAuthenticated = computed(() => !!user.value)
  
  const login = async (credentials) => {
    user.value = await api.login(credentials)
  }
  
  return { user, isAuthenticated, login }
}
```

## Prochaines Étapes

Dans le prochain article, nous plongerons dans la gestion d'état avec Pinia et Vuex !
MARKDOWN;
    }

    private function getVueStateContent(): string
    {
        return <<<'MARKDOWN'
# State Management: Pinia vs Vuex

Managing application state is crucial for complex Vue applications. Let's compare two popular solutions.

## Pinia: The New Standard

Pinia is now the recommended state management library for Vue 3.

### Basic Store
```javascript
// stores/user.js
import { defineStore } from 'pinia'

export const useUserStore = defineStore('user', {
  state: () => ({
    user: null,
    isLoading: false
  }),
  
  getters: {
    isAuthenticated: (state) => !!state.user,
    displayName: (state) => state.user?.name || 'Guest'
  },
  
  actions: {
    async login(credentials) {
      this.isLoading = true
      try {
        this.user = await api.login(credentials)
      } finally {
        this.isLoading = false
      }
    },
    
    logout() {
      this.user = null
    }
  }
})
```

### Using the Store
```vue
<script setup>
import { useUserStore } from '@/stores/user'

const userStore = useUserStore()
</script>

<template>
  <div>
    <p v-if="userStore.isAuthenticated">
      Welcome, {{ userStore.displayName }}!
    </p>
    <button @click="userStore.logout">Logout</button>
  </div>
</template>
```

## Vuex: The Classic Approach

Vuex follows a more traditional Flux pattern.

### Basic Store
```javascript
// store/index.js
export default createStore({
  state: {
    user: null
  },
  
  mutations: {
    SET_USER(state, user) {
      state.user = user
    }
  },
  
  actions: {
    async login({ commit }, credentials) {
      const user = await api.login(credentials)
      commit('SET_USER', user)
    }
  },
  
  getters: {
    isAuthenticated: state => !!state.user
  }
})
```

## Pinia vs Vuex Comparison

| Feature | Pinia | Vuex |
|---------|-------|------|
| TypeScript Support | ✅ Excellent | ⚠️ Requires extra setup |
| Devtools | ✅ Full support | ✅ Full support |
| Module Structure | ✅ Automatic | ❌ Manual |
| Mutations | ❌ Not needed | ✅ Required |
| Actions | ✅ Can be sync/async | ✅ Always async |

## Recommendation

For new Vue 3 projects, **use Pinia**. It's simpler, has better TypeScript support, and is officially recommended.

## Next Post

Learn performance optimization techniques in our final post!
MARKDOWN;
    }

    private function getVueStateContentFr(): string
    {
        return <<<'MARKDOWN'
# Gestion d'État : Pinia vs Vuex

Gérer l'état de l'application est crucial pour les applications Vue complexes. Comparons deux solutions populaires.

## Pinia : Le Nouveau Standard

Pinia est maintenant la bibliothèque de gestion d'état recommandée pour Vue 3.

### Store Basique
```javascript
// stores/user.js
import { defineStore } from 'pinia'

export const useUserStore = defineStore('user', {
  state: () => ({
    user: null,
    isLoading: false
  }),
  
  getters: {
    isAuthenticated: (state) => !!state.user,
    displayName: (state) => state.user?.name || 'Invité'
  },
  
  actions: {
    async login(credentials) {
      this.isLoading = true
      try {
        this.user = await api.login(credentials)
      } finally {
        this.isLoading = false
      }
    },
    
    logout() {
      this.user = null
    }
  }
})
```

### Utiliser le Store
```vue
<script setup>
import { useUserStore } from '@/stores/user'

const userStore = useUserStore()
</script>

<template>
  <div>
    <p v-if="userStore.isAuthenticated">
      Bienvenue, {{ userStore.displayName }} !
    </p>
    <button @click="userStore.logout">Déconnexion</button>
  </div>
</template>
```

## Vuex : L'Approche Classique

Vuex suit un modèle Flux plus traditionnel.

### Store Basique
```javascript
// store/index.js
export default createStore({
  state: {
    user: null
  },
  
  mutations: {
    SET_USER(state, user) {
      state.user = user
    }
  },
  
  actions: {
    async login({ commit }, credentials) {
      const user = await api.login(credentials)
      commit('SET_USER', user)
    }
  },
  
  getters: {
    isAuthenticated: state => !!state.user
  }
})
```

## Comparaison Pinia vs Vuex

| Fonctionnalité | Pinia | Vuex |
|---------------|-------|------|
| Support TypeScript | ✅ Excellent | ⚠️ Configuration supplémentaire |
| Devtools | ✅ Support complet | ✅ Support complet |
| Structure Modulaire | ✅ Automatique | ❌ Manuel |
| Mutations | ❌ Pas nécessaire | ✅ Requis |
| Actions | ✅ Sync/async | ✅ Toujours async |

## Recommandation

Pour les nouveaux projets Vue 3, **utilisez Pinia**. C'est plus simple, avec un meilleur support TypeScript, et officiellement recommandé.

## Prochain Article

Découvrez les techniques d'optimisation des performances dans notre dernier article !
MARKDOWN;
    }

    private function getVuePerformanceContent(): string
    {
        return <<<'MARKDOWN'
# Vue.js Performance Optimization

Let's explore proven techniques to make your Vue applications blazingly fast!

## 1. Component Lazy Loading

Load components only when needed:

```javascript
// Instead of
import HeavyComponent from './HeavyComponent.vue'

// Use
const HeavyComponent = defineAsyncComponent(() =>
  import('./HeavyComponent.vue')
)
```

### Route-Based Code Splitting
```javascript
const routes = [
  {
    path: '/admin',
    component: () => import('./views/Admin.vue')
  }
]
```

## 2. Virtual Scrolling

For long lists, render only visible items:

```vue
<script setup>
import { VirtualList } from 'vue-virtual-scroll'

const items = ref(Array.from({ length: 10000 }))
</script>

<template>
  <VirtualList :items="items" :item-height="50">
    <template #default="{ item }">
      <div>{{ item }}</div>
    </template>
  </VirtualList>
</template>
```

## 3. Computed vs Methods

Use computed for derived state:

```vue
<script setup>
// ✅ Good: Cached
const fullName = computed(() => 
  `${firstName.value} ${lastName.value}`
)

// ❌ Bad: Recalculates on every render
const fullName = () => `${firstName.value} ${lastName.value}`
</script>
```

## 4. V-once and V-memo

### V-once: Render Once
```vue
<template>
  <div v-once>
    <h1>{{ staticTitle }}</h1>
    <p>{{ staticContent }}</p>
  </div>
</template>
```

### V-memo: Conditional Caching
```vue
<template>
  <div v-for="item in list" v-memo="[item.id]">
    <p>{{ item.name }}</p>
  </div>
</template>
```

## 5. Keep-Alive for Cached Components

```vue
<template>
  <KeepAlive :max="10">
    <component :is="currentView" />
  </KeepAlive>
</template>
```

## 6. Debounce Expensive Operations

```javascript
import { debounce } from 'lodash-es'

const search = debounce(async (query) => {
  results.value = await api.search(query)
}, 300)
```

## 7. Tree-Shaking

Import only what you need:

```javascript
// ❌ Bad: Imports entire library
import _ from 'lodash'

// ✅ Good: Imports only needed function
import { debounce } from 'lodash-es'
```

## Performance Checklist

- [ ] Lazy load routes and heavy components
- [ ] Use virtual scrolling for long lists
- [ ] Prefer computed over methods for derived state
- [ ] Implement v-once for static content
- [ ] Use KeepAlive for frequently toggled components
- [ ] Debounce expensive operations
- [ ] Enable production mode in build
- [ ] Use Vue DevTools Performance tab

## Conclusion

This concludes our Vue.js Best Practices series! You now have the knowledge to build performant, maintainable Vue applications.

Happy coding! ���
MARKDOWN;
    }

    private function getVuePerformanceContentFr(): string
    {
        return <<<'MARKDOWN'
# Optimisation des Performances Vue.js

Explorons des techniques éprouvées pour rendre vos applications Vue ultra-rapides !

## 1. Chargement Différé des Composants

Chargez les composants uniquement quand nécessaire :

```javascript
// Au lieu de
import HeavyComponent from './HeavyComponent.vue'

// Utilisez
const HeavyComponent = defineAsyncComponent(() =>
  import('./HeavyComponent.vue')
)
```

### Découpage de Code par Routes
```javascript
const routes = [
  {
    path: '/admin',
    component: () => import('./views/Admin.vue')
  }
]
```

## 2. Défilement Virtuel

Pour les longues listes, ne rendre que les éléments visibles :

```vue
<script setup>
import { VirtualList } from 'vue-virtual-scroll'

const items = ref(Array.from({ length: 10000 }))
</script>

<template>
  <VirtualList :items="items" :item-height="50">
    <template #default="{ item }">
      <div>{{ item }}</div>
    </template>
  </VirtualList>
</template>
```

## 3. Computed vs Méthodes

Utilisez computed pour l'état dérivé :

```vue
<script setup>
// ✅ Bon : En cache
const fullName = computed(() => 
  `${firstName.value} ${lastName.value}`
)

// ❌ Mauvais : Recalcule à chaque rendu
const fullName = () => `${firstName.value} ${lastName.value}`
</script>
```

## 4. V-once et V-memo

### V-once : Rendu Unique
```vue
<template>
  <div v-once>
    <h1>{{ staticTitle }}</h1>
    <p>{{ staticContent }}</p>
  </div>
</template>
```

### V-memo : Mise en Cache Conditionnelle
```vue
<template>
  <div v-for="item in list" v-memo="[item.id]">
    <p>{{ item.name }}</p>
  </div>
</template>
```

## 5. Keep-Alive pour Composants en Cache

```vue
<template>
  <KeepAlive :max="10">
    <component :is="currentView" />
  </KeepAlive>
</template>
```

## 6. Debounce des Opérations Coûteuses

```javascript
import { debounce } from 'lodash-es'

const search = debounce(async (query) => {
  results.value = await api.search(query)
}, 300)
```

## 7. Tree-Shaking

N'importez que ce dont vous avez besoin :

```javascript
// ❌ Mauvais : Importe toute la bibliothèque
import _ from 'lodash'

// ✅ Bon : Importe seulement la fonction nécessaire
import { debounce } from 'lodash-es'
```

## Liste de Vérification des Performances

- [ ] Charger différemment les routes et composants lourds
- [ ] Utiliser le défilement virtuel pour les longues listes
- [ ] Préférer computed aux méthodes pour l'état dérivé
- [ ] Implémenter v-once pour le contenu statique
- [ ] Utiliser KeepAlive pour les composants fréquemment basculés
- [ ] Debouncer les opérations coûteuses
- [ ] Activer le mode production en build
- [ ] Utiliser l'onglet Performance de Vue DevTools

## Conclusion

Ceci conclut notre série Meilleures Pratiques Vue.js ! Vous avez maintenant les connaissances pour créer des applications Vue performantes et maintenables.

Bon codage ! ���
MARKDOWN;
    }
}
