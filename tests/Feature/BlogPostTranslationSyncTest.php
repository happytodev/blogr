<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\Category;

beforeEach(function () {
    // Create a default category
    Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_default' => true,
    ]);
});

it('automatically creates a default translation when a post is created', function () {
    config(['blogr.locales.default' => 'fr']);
    
    $post = BlogPost::create([
        'user_id' => 1,
        'category_id' => Category::first()->id,
        'is_published' => false,
        'title' => 'Mon Article',
        'slug' => 'mon-article',
        'content' => 'Contenu de mon article',
        'tldr' => 'Résumé rapide',
        'meta_title' => 'Titre Meta',
        'meta_description' => 'Description Meta',
        'default_locale' => 'fr',
    ]);
    
    // Verify the translation was created
    expect($post->translations()->count())->toBe(1);
    
    $translation = $post->translations()->first();
    
    expect($translation)->toBeInstanceOf(BlogPostTranslation::class)
        ->and($translation->locale)->toBe('fr')
        ->and($translation->title)->toBe('Mon Article')
        ->and($translation->slug)->toBe('mon-article')
        ->and($translation->content)->toBe('Contenu de mon article');
});

it('does not create translation when non-translatable fields only', function () {
    config(['blogr.locales.default' => 'en']);
    
    $post = BlogPost::create([
        'user_id' => 1,
        'category_id' => Category::first()->id,
        'is_published' => false,
        'photo' => 'test-photo.jpg',
        'default_locale' => 'en',
    ]);
    
    // No translation should be created since no translatable fields were provided
    expect($post->translations()->count())->toBe(0);
});

it('deletes all translations when post is deleted (cascade)', function () {
    config(['blogr.locales.default' => 'en']);
    
    $post = BlogPost::create([
        'user_id' => 1,
        'category_id' => Category::first()->id,
        'is_published' => false,
        'title' => 'Test Post',
        'slug' => 'test-post-' . uniqid(),
        'content' => 'Test content',
        'default_locale' => 'en',
    ]);
    
    // Create additional translation manually
    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'title' => 'Test FR',
        'slug' => 'test-fr-' . uniqid(),
        'content' => 'Contenu FR',
    ]);
    
    $postId = $post->id;
    
    // Verify translations exist (1 from hook + 1 manual)
    expect(BlogPostTranslation::where('blog_post_id', $postId)->count())->toBe(2);
    
    // Delete the post
    $post->delete();
    
    // Verify all translations were deleted (cascade)
    expect(BlogPostTranslation::where('blog_post_id', $postId)->count())->toBe(0);
});

