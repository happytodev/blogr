<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;

it('can create translations for a blog post', function () {
    // Observer will create a default 'en' translation automatically
    $post = BlogPost::factory()->create();
    
    // Verify the default translation was created
    expect($post->translations()->count())->toBe(1);
    expect($post->translations()->first()->locale)->toBe('en');
    
    // Create an additional French translation
    $translation = BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Mon Article',
        'slug' => 'mon-article',
        'content' => 'Contenu en francais',
        'reading_time' => 1,
    ]);
    
    expect($translation)->not->toBeNull();
    expect($translation->locale)->toBe('fr');
    expect($post->translations()->count())->toBe(2);
});

it('can get translation for specific locale', function () {
    // Observer will create a default 'en' translation automatically
    $post = BlogPost::factory()->create([
        'title' => 'My Post',
        'slug' => 'my-post',
        'content' => 'English content',
    ]);
    
    // The translation should already exist from the Observer
    $translation = $post->getTranslation('en');
    
    expect($translation)->not->toBeNull();
    expect($translation->title)->toBe('My Post');
});

it('can have multiple translations', function () {
    // Observer will create a default 'en' translation automatically
    $post = BlogPost::factory()->create();
    
    // We already have 'en' from the Observer, add 'fr' and 'es'
    foreach (['fr', 'es'] as $locale) {
        BlogPostTranslation::create([
            'blog_post_id' => $post->id,
            'locale' => $locale,
            'title' => "Title {$locale}",
            'slug' => "slug-{$locale}",
            'content' => "Content {$locale}",
            'reading_time' => 1,
        ]);
    }
    
    // Should have 3 translations total (en + fr + es)
    expect($post->translations()->count())->toBe(3);
});
