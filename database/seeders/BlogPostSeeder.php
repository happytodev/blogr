<?php



namespace Happytodev\Blogr\Database\Seeders;namespace Happytodev\Blogr\Database\Seeders;



use Illuminate\Database\Seeder;class BlogPostSeeder

use Happytodev\Blogr\Models\BlogPost;{

use Happytodev\Blogr\Models\BlogPostTranslation;}

use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;

class BlogPostSeeder extends Seeder
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
        $categoryProductivity = Category::firstOrCreate(['slug' => 'productivity'], ['name' => 'Productivity']);

        // Create tags
        $tagBeginner = Tag::firstOrCreate(['slug' => 'beginner'], ['name' => 'Beginner']);
        $tagAdvanced = Tag::firstOrCreate(['slug' => 'advanced'], ['name' => 'Advanced']);
        $tagBestPractices = Tag::firstOrCreate(['slug' => 'best-practices'], ['name' => 'Best Practices']);
        $tagTips = Tag::firstOrCreate(['slug' => 'tips'], ['name' => 'Tips']);
        $tagPerformance = Tag::firstOrCreate(['slug' => 'performance'], ['name' => 'Performance']);

        // Create standalone posts (not in series)
        $this->createPost([
            'slug' => 'getting-started-with-laravel-eloquent',
            'published_at' => now()->subDays(15),
            'category' => $categoryLaravel,
            'tags' => [$tagBeginner, $tagTutorial],
            'translations' => [
                'en' => [
                    'title' => 'Getting Started with Laravel Eloquent ORM',
                    'tldr' => 'Learn how to use Laravel\'s Eloquent ORM to interact with your database in an elegant and intuitive way.',
                    'content' => $this->getEloquentContent(),
                    'seo_description' => 'Master Laravel Eloquent ORM basics with this comprehensive guide for beginners.',
                ],
                'fr' => [
                    'title' => 'Démarrer avec Laravel Eloquent ORM',
                    'tldr' => 'Apprenez à utiliser l\'ORM Eloquent de Laravel pour interagir avec votre base de données de manière élégante et intuitive.',
                    'content' => $this->getEloquentContentFr(),
                    'seo_description' => 'Maîtrisez les bases de Laravel Eloquent ORM avec ce guide complet pour débutants.',
                ],
            ],
        ]);

        $this->createPost([
            'slug' => 'laravel-best-practices-2025',
            'published_at' => now()->subDays(10),
            'category' => $categoryLaravel,
            'tags' => [$tagBestPractices, $tagAdvanced],
            'translations' => [
                'en' => [
                    'title' => 'Laravel Best Practices in 2025',
                    'tldr' => 'Discover the most important Laravel best practices to write clean, maintainable, and efficient code.',
                    'content' => $this->getBestPracticesContent(),
                    'seo_description' => 'Learn the latest Laravel best practices for 2025 to improve your development workflow.',
                ],
                'fr' => [
                    'title' => 'Meilleures Pratiques Laravel en 2025',
                    'tldr' => 'Découvrez les meilleures pratiques Laravel les plus importantes pour écrire du code propre, maintenable et efficace.',
                    'content' => $this->getBestPracticesContentFr(),
                    'seo_description' => 'Apprenez les dernières meilleures pratiques Laravel pour 2025 afin d\'améliorer votre workflow de développement.',
                ],
            ],
        ]);

        $this->createPost([
            'slug' => 'vue-3-composition-api-guide',
            'published_at' => now()->subDays(7),
            'category' => $categoryVue,
            'tags' => [$tagBeginner, $tagTips],
            'translations' => [
                'en' => [
                    'title' => 'Vue 3 Composition API: A Practical Guide',
                    'tldr' => 'Understand Vue 3\'s Composition API and how it can make your code more reusable and maintainable.',
                    'content' => $this->getVueCompositionContent(),
                    'seo_description' => 'Learn Vue 3 Composition API with practical examples and best practices.',
                ],
                'fr' => [
                    'title' => 'API de Composition Vue 3 : Un Guide Pratique',
                    'tldr' => 'Comprenez l\'API de Composition de Vue 3 et comment elle peut rendre votre code plus réutilisable et maintenable.',
                    'content' => $this->getVueCompositionContentFr(),
                    'seo_description' => 'Apprenez l\'API de Composition Vue 3 avec des exemples pratiques et des meilleures pratiques.',
                ],
            ],
        ]);

        $this->createPost([
            'slug' => 'optimizing-laravel-performance',
            'published_at' => now()->subDays(3),
            'category' => $categoryLaravel,
            'tags' => [$tagPerformance, $tagAdvanced, $tagTips],
            'translations' => [
                'en' => [
                    'title' => 'Optimizing Laravel Application Performance',
                    'tldr' => 'Learn proven techniques to dramatically improve your Laravel application\'s speed and efficiency.',
                    'content' => $this->getPerformanceContent(),
                    'seo_description' => 'Boost your Laravel application performance with these optimization techniques.',
                ],
                'fr' => [
                    'title' => 'Optimiser les Performances d\'une Application Laravel',
                    'tldr' => 'Apprenez des techniques éprouvées pour améliorer considérablement la vitesse et l\'efficacité de votre application Laravel.',
                    'content' => $this->getPerformanceContentFr(),
                    'seo_description' => 'Boostez les performances de votre application Laravel avec ces techniques d\'optimisation.',
                ],
            ],
        ]);

        $this->createPost([
            'slug' => 'developer-productivity-tips',
            'published_at' => now()->subDay(),
            'category' => $categoryProductivity,
            'tags' => [$tagTips],
            'translations' => [
                'en' => [
                    'title' => '10 Productivity Tips for Developers',
                    'tldr' => 'Simple yet effective tips to boost your productivity as a developer and get more done in less time.',
                    'content' => $this->getProductivityContent(),
                    'seo_description' => 'Increase your productivity with these 10 essential tips for developers.',
                ],
                'fr' => [
                    'title' => '10 Conseils de Productivité pour Développeurs',
                    'tldr' => 'Des conseils simples mais efficaces pour augmenter votre productivité en tant que développeur et accomplir plus en moins de temps.',
                    'content' => $this->getProductivityContentFr(),
                    'seo_description' => 'Augmentez votre productivité avec ces 10 conseils essentiels pour développeurs.',
                ],
            ],
        ]);

        $this->command->info('✓ Created 5 standalone blog posts with translations');
    }

    private function createPost(array $data): BlogPost
    {
        // Create the post without content (new system uses translations)
        $post = BlogPost::create([
            'user_id' => $this->user->id,
            'category_id' => $data['category']->id,
            'published_at' => $data['published_at'],
            'default_locale' => 'en',
        ]);

        // Create translations
        foreach ($data['translations'] as $locale => $translation) {
            $readingTime = $this->calculateReadingTime($translation['content']);
            
            BlogPostTranslation::create([
                'blog_post_id' => $post->id,
                'locale' => $locale,
                'slug' => $data['slug'],
                'title' => $translation['title'],
                'tldr' => $translation['tldr'] ?? null,
                'content' => $translation['content'],
                'seo_description' => $translation['seo_description'],
                'reading_time' => $readingTime,
            ]);
        }

        // Attach tags
        $post->tags()->attach($data['tags']);

        return $post;
    }

    private function calculateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        return max(1, (int) ceil($wordCount / 200));
    }

    // ==================== CONTENT METHODS ====================

    private function getEloquentContent(): string
    {
        return <<<'MARKDOWN'
# Getting Started with Laravel Eloquent ORM

Laravel's Eloquent ORM is one of the most elegant and powerful features of the framework. It provides a beautiful, simple ActiveRecord implementation for working with your database.

## What is Eloquent?

Eloquent is Laravel's Object-Relational Mapper (ORM) that makes it incredibly easy to interact with your database. Each database table has a corresponding "Model" that is used to interact with that table.

## Creating Your First Model

You can create a model using the Artisan command:

```bash
php artisan make:model Post
```

This creates a new model in the `app/Models` directory.

## Basic Usage

### Retrieving Records

```php
// Get all posts
$posts = Post::all();

// Get a specific post
$post = Post::find(1);

// Query with conditions
$posts = Post::where('published', true)->get();
```

### Creating Records

```php
$post = new Post;
$post->title = 'My First Post';
$post->content = 'This is the content';
$post->save();

// Or use mass assignment
Post::create([
    'title' => 'My First Post',
    'content' => 'This is the content'
]);
```

### Updating Records

```php
$post = Post::find(1);
$post->title = 'Updated Title';
$post->save();

// Or update multiple records
Post::where('published', false)->update(['published' => true]);
```

### Deleting Records

```php
$post = Post::find(1);
$post->delete();

// Delete by ID
Post::destroy(1);
Post::destroy([1, 2, 3]);
```

## Relationships

Eloquent makes it easy to define relationships between models:

### One to Many

```php
class Post extends Model
{
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

class Comment extends Model
{
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
```

### Accessing Relationships

```php
$post = Post::find(1);
$comments = $post->comments;

$comment = Comment::find(1);
$post = $comment->post;
```

## Query Scopes

Scopes allow you to define common query constraints:

```php
class Post extends Model
{
    public function scopePublished($query)
    {
        return $query->where('published', true);
    }
    
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}

// Usage
$posts = Post::published()->recent()->get();
```

## Conclusion

Eloquent ORM is a powerful tool that makes database interactions in Laravel a breeze. This is just the beginning - there's so much more to explore!
MARKDOWN;
    }

    private function getEloquentContentFr(): string
    {
        return <<<'MARKDOWN'
# Démarrer avec Laravel Eloquent ORM

L'ORM Eloquent de Laravel est l'une des fonctionnalités les plus élégantes et puissantes du framework. Il fournit une belle et simple implémentation ActiveRecord pour travailler avec votre base de données.

## Qu'est-ce qu'Eloquent ?

Eloquent est l'Object-Relational Mapper (ORM) de Laravel qui facilite incroyablement l'interaction avec votre base de données. Chaque table de base de données a un "Modèle" correspondant qui est utilisé pour interagir avec cette table.

## Créer Votre Premier Modèle

Vous pouvez créer un modèle en utilisant la commande Artisan :

```bash
php artisan make:model Post
```

Cela crée un nouveau modèle dans le répertoire `app/Models`.

## Utilisation de Base

### Récupérer des Enregistrements

```php
// Obtenir tous les posts
$posts = Post::all();

// Obtenir un post spécifique
$post = Post::find(1);

// Requête avec conditions
$posts = Post::where('published', true)->get();
```

### Créer des Enregistrements

```php
$post = new Post;
$post->title = 'Mon Premier Post';
$post->content = 'Ceci est le contenu';
$post->save();

// Ou utiliser l'assignation de masse
Post::create([
    'title' => 'Mon Premier Post',
    'content' => 'Ceci est le contenu'
]);
```

## Conclusion

L'ORM Eloquent est un outil puissant qui rend les interactions avec la base de données dans Laravel un jeu d'enfant !
MARKDOWN;
    }

    private function getBestPracticesContent(): string
    {
        return <<<'MARKDOWN'
# Laravel Best Practices in 2025

Writing clean, maintainable Laravel code is essential for long-term project success. Here are the most important best practices to follow in 2025.

## 1. Follow PSR Standards

Always follow PSR-12 coding standards for consistency:

```php
// Good
public function getUserPosts(User $user): Collection
{
    return $user->posts()
        ->published()
        ->latest()
        ->get();
}

// Bad - inconsistent formatting
public function getUserPosts(User $user):Collection{
    return $user->posts()->published()->latest()->get();
}
```

## 2. Use Type Hints

Always use type hints for better code clarity and IDE support:

```php
// Good
public function createPost(string $title, string $content, User $author): Post
{
    return Post::create([
        'title' => $title,
        'content' => $content,
        'user_id' => $author->id,
    ]);
}

// Bad - no type hints
public function createPost($title, $content, $author)
{
    // ...
}
```

## 3. Use Form Requests

Keep your controllers thin by using Form Requests for validation:

```php
// Good
public function store(StorePostRequest $request)
{
    $post = Post::create($request->validated());
    return redirect()->route('posts.show', $post);
}

// Bad - validation in controller
public function store(Request $request)
{
    $request->validate([
        'title' => 'required|max:255',
        'content' => 'required',
    ]);
    // ...
}
```

## 4. Use Eloquent Relationships

Leverage Eloquent relationships instead of manual joins:

```php
// Good
$user->posts()->with('comments')->get();

// Bad
Post::join('users', 'posts.user_id', '=', 'users.id')
    ->where('users.id', $userId)
    ->get();
```

## 5. Use Query Scopes

Create reusable query logic with scopes:

```php
class Post extends Model
{
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }
    
    public function scopePopular($query)
    {
        return $query->where('views', '>', 1000);
    }
}

// Usage
Post::published()->popular()->get();
```

## Conclusion

Following these best practices will help you write better Laravel applications that are easier to maintain and scale.
MARKDOWN;
    }

    private function getBestPracticesContentFr(): string
    {
        return <<<'MARKDOWN'
# Meilleures Pratiques Laravel en 2025

Écrire du code Laravel propre et maintenable est essentiel pour le succès à long terme d'un projet.

## 1. Suivre les Standards PSR

Suivez toujours les standards de codage PSR-12 pour la cohérence.

## 2. Utiliser les Type Hints

Utilisez toujours les type hints pour une meilleure clarté du code et un meilleur support IDE.

## Conclusion

Suivre ces meilleures pratiques vous aidera à écrire de meilleures applications Laravel plus faciles à maintenir et à faire évoluer.
MARKDOWN;
    }

    private function getVueCompositionContent(): string
    {
        return <<<'MARKDOWN'
# Vue 3 Composition API: A Practical Guide

The Composition API is one of the most significant changes in Vue 3. It provides a more flexible way to organize component logic.

## Why Composition API?

The Options API works well for simple components, but as components grow, it becomes harder to maintain. The Composition API solves this by allowing you to organize code by logical concerns.

## Basic Setup

```javascript
import { ref, computed, onMounted } from 'vue'

export default {
  setup() {
    // Reactive state
    const count = ref(0)
    
    // Computed property
    const doubleCount = computed(() => count.value * 2)
    
    // Method
    function increment() {
      count.value++
    }
    
    // Lifecycle hook
    onMounted(() => {
      console.log('Component mounted')
    })
    
    return {
      count,
      doubleCount,
      increment
    }
  }
}
```

## Reactive References

```javascript
// ref for primitives
const name = ref('John')
console.log(name.value) // 'John'

// reactive for objects
const user = reactive({
  name: 'John',
  age: 30
})
console.log(user.name) // 'John'
```

## Composables

Create reusable logic with composables:

```javascript
// useCounter.js
export function useCounter(initialValue = 0) {
  const count = ref(initialValue)
  
  function increment() {
    count.value++
  }
  
  function decrement() {
    count.value--
  }
  
  return {
    count,
    increment,
    decrement
  }
}

// In component
import { useCounter } from './useCounter'

export default {
  setup() {
    const { count, increment, decrement } = useCounter(10)
    
    return { count, increment, decrement }
  }
}
```

## Conclusion

The Composition API provides better code organization and reusability. It's the future of Vue development!
MARKDOWN;
    }

    private function getVueCompositionContentFr(): string
    {
        return <<<'MARKDOWN'
# API de Composition Vue 3 : Un Guide Pratique

L'API de Composition est l'un des changements les plus significatifs de Vue 3. Elle offre une façon plus flexible d'organiser la logique des composants.

## Conclusion

L'API de Composition offre une meilleure organisation du code et une meilleure réutilisabilité. C'est l'avenir du développement Vue !
MARKDOWN;
    }

    private function getPerformanceContent(): string
    {
        return <<<'MARKDOWN'
# Optimizing Laravel Application Performance

Performance is crucial for user experience. Here are proven techniques to optimize your Laravel application.

## 1. Enable Caching

```php
// Config caching
php artisan config:cache

// Route caching
php artisan route:cache

// View caching
php artisan view:cache
```

## 2. Eager Loading

Avoid N+1 queries with eager loading:

```php
// Bad - N+1 problem
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name; // Queries database for each post
}

// Good - eager loading
$posts = Post::with('author')->get();
foreach ($posts as $post) {
    echo $post->author->name; // No extra queries
}
```

## 3. Use Database Indexing

```php
Schema::table('posts', function (Blueprint $table) {
    $table->index('user_id');
    $table->index('published_at');
    $table->index(['category_id', 'published_at']);
});
```

## 4. Queue Long-Running Tasks

```php
// Instead of processing immediately
Mail::to($user)->send(new WelcomeEmail());

// Queue it
Mail::to($user)->queue(new WelcomeEmail());
```

## 5. Use Redis for Caching

```php
// Cache database queries
$posts = Cache::remember('posts.recent', 3600, function () {
    return Post::published()->recent()->limit(10)->get();
});
```

## Conclusion

These optimization techniques can dramatically improve your application's performance. Start with the biggest bottlenecks first!
MARKDOWN;
    }

    private function getPerformanceContentFr(): string
    {
        return <<<'MARKDOWN'
# Optimiser les Performances d'une Application Laravel

Les performances sont cruciales pour l'expérience utilisateur. Voici des techniques éprouvées pour optimiser votre application Laravel.

## Conclusion

Ces techniques d'optimisation peuvent considérablement améliorer les performances de votre application !
MARKDOWN;
    }

    private function getProductivityContent(): string
    {
        return <<<'MARKDOWN'
# 10 Productivity Tips for Developers

Boost your productivity with these simple yet effective tips.

## 1. Use a Task Manager

Keep track of your tasks and prioritize them. Use tools like Todoist, Notion, or even a simple text file.

## 2. Learn Keyboard Shortcuts

Master your IDE's keyboard shortcuts. It saves tremendous time in the long run.

## 3. Automate Repetitive Tasks

Write scripts for tasks you do frequently. Time invested in automation pays off quickly.

## 4. Take Regular Breaks

Use the Pomodoro Technique: 25 minutes of focused work, followed by a 5-minute break.

## 5. Keep a Developer Journal

Document solutions to problems you encounter. Your future self will thank you!

## 6. Use Code Snippets

Create snippets for code you write frequently. Most IDEs support this feature.

## 7. Read Documentation

Before searching Stack Overflow, check the official documentation. It's often faster and more accurate.

## 8. Write Tests

Tests save time by catching bugs early. They also serve as documentation for your code.

## 9. Refactor Regularly

Don't let technical debt accumulate. Refactor code as you go.

## 10. Stay Healthy

Exercise regularly, get enough sleep, and eat well. A healthy body supports a productive mind.

## Conclusion

Productivity isn't about working harder—it's about working smarter. Implement these tips gradually and see the difference!
MARKDOWN;
    }

    private function getProductivityContentFr(): string
    {
        return <<<'MARKDOWN'
# 10 Conseils de Productivité pour Développeurs

Augmentez votre productivité avec ces conseils simples mais efficaces.

## Conclusion

La productivité ne consiste pas à travailler plus dur, mais à travailler plus intelligemment !
MARKDOWN;
    }
}
