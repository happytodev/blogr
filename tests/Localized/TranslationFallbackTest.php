<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\Category;
use Workbench\App\Models\User;

// LocalizedTestCase is automatically used for tests in this folder (see tests/Pest.php)

test('post with only English translation accessed in French shows English version', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Technology', 'slug' => 'technology']);

    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'slug' => 'test-post-en-only-' . uniqid(),
        'title' => 'Test Post in English',
        'content' => '# Test Content in English',
        'tldr' => 'English TLDR',
        'seo_description' => 'English SEO',
    ]);

    $translation = $post->translations()->first();
    $url = '/fr/blog/' . $translation->slug;

    $response = $this->get($url);
    
    $response->assertStatus(200);
    $response->assertSee('Test Post in English');
});

test('post with bilingual translations shows correct language version', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Technology', 'slug' => 'technology']);

    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
    ]);

    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'slug' => 'bilingual-post-' . uniqid(),
        'title' => 'Post in English',
        'content' => '# Content in English',
        'tldr' => 'English TLDR',
        'seo_description' => 'English SEO',
    ]);

    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'fr',
        'slug' => 'bilingual-post-fr-' . uniqid(),
        'title' => 'Article en Français',
        'content' => '# Contenu en Français',
        'tldr' => 'TLDR français',
        'seo_description' => 'SEO français',
    ]);

    // Test English version
    $enTranslation = $post->translations()->where('locale', 'en')->first();
    $responseEn = $this->get('/en/blog/' . $enTranslation->slug);
    $responseEn->assertStatus(200);
    $responseEn->assertSee('Post in English');

    // Test French version - should show French content
    $frTranslation = $post->translations()->where('locale', 'fr')->first();
    $responseFr = $this->get('/fr/blog/' . $frTranslation->slug);
    $responseFr->assertStatus(200);
    $responseFr->assertSee('Article en Français');
});

test('unpublished post returns 404 even with translation', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Technology', 'slug' => 'technology']);

    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => false,
        'published_at' => null,
        'default_locale' => 'en',
    ]);

    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'slug' => 'unpublished-test-' . uniqid(),
        'title' => 'Unpublished Post',
        'content' => '# Draft Content',
        'tldr' => 'Draft TLDR',
        'seo_description' => 'Draft SEO',
    ]);

    $translation = $post->translations()->first();
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => $translation->slug]));

    $response->assertStatus(404);
});

test('future published post returns 404', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Technology', 'slug' => 'technology']);

    $post = BlogPost::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'is_published' => true,
        'published_at' => now()->addDays(7),
        'default_locale' => 'en',
    ]);

    BlogPostTranslation::create([
        'blog_post_id' => $post->id,
        'locale' => 'en',
        'slug' => 'future-test-' . uniqid(),
        'title' => 'Future Post',
        'content' => '# Scheduled Content',
        'tldr' => 'Future TLDR',
        'seo_description' => 'Future SEO',
    ]);

    $translation = $post->translations()->first();
    $response = $this->get(route('blog.show', ['locale' => 'en', 'slug' => $translation->slug]));

    $response->assertStatus(404);
});
