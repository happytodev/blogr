<?php

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Tests\LocalizedTestCase;
use Illuminate\Support\Facades\Storage;
use Workbench\App\Models\User;


beforeEach(function () {
    Storage::fake('public');
    
    $this->user = User::factory()->create([
        'name' => 'John Doe',
        'slug' => 'johndoe',
    ]);
    
    $this->category = Category::factory()->create();
    
    $this->series = BlogSeries::create([
        'slug' => 'test-series',
        'position' => 1,
        'is_featured' => true,
        'published_at' => now(),
    ]);
    
    // Create series translation
    $this->series->translations()->create([
        'locale' => 'en',
        'title' => 'Test Series',
        'description' => 'Test series description',
        'slug' => 'test-series',
    ]);
    
    $this->post = BlogPost::create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'blog_series_id' => $this->series->id,
        'series_position' => 1,
        'is_published' => true,
        'published_at' => now(),
        'default_locale' => 'en',
        'photo' => null,
    ]);
    
    // Create post translation
    $this->post->translations()->create([
        'locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'Test content',
        'reading_time' => 5,
    ]);
});

it('includes locale prefix in author links on series detail page when locales are enabled', function () {
    $response = $this->get(route('blog.series', [
        'locale' => 'en',
        'seriesSlug' => 'test-series'
    ]));
    
    $response->assertStatus(200);
    
    // Check that the author link in the HTML contains the locale
    $content = $response->getContent();
    
    // Find all author links
    preg_match_all('/href="([^"]*author[^"]*)"/i', $content, $matches);
    
    // The link should contain /en/blog/author/johndoe or /en/author/johndoe
    $hasLocaleInPath = false;
    foreach ($matches[1] as $url) {
        if (str_contains($url, '/en/blog/author/johndoe') || str_contains($url, '/en/author/johndoe')) {
            $hasLocaleInPath = true;
            break;
        }
    }
    
    expect($hasLocaleInPath)->toBeTrue('Author links should include locale in path');
});

it('works with french locale', function () {
    // Create French translation
    $this->series->translations()->create([
        'locale' => 'fr',
        'title' => 'Série de test',
        'description' => 'Description de la série de test',
        'slug' => 'serie-de-test',
    ]);
    
    $this->post->translations()->create([
        'locale' => 'fr',
        'title' => 'Article de test',
        'slug' => 'article-de-test',
        'content' => 'Contenu du test',
        'reading_time' => 5,
    ]);
    
    $response = $this->get(route('blog.series', [
        'locale' => 'fr',
        'seriesSlug' => 'serie-de-test'
    ]));
    
    $response->assertStatus(200);
    
    $content = $response->getContent();
    preg_match_all('/href="([^"]*author[^"]*)"/i', $content, $matches);
    
    $hasLocaleInPath = false;
    foreach ($matches[1] as $url) {
        if (str_contains($url, '/fr/blog/author/johndoe') || str_contains($url, '/fr/author/johndoe')) {
            $hasLocaleInPath = true;
            break;
        }
    }
    
    expect($hasLocaleInPath)->toBeTrue('Author links should include french locale in path');
});
