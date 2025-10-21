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
    
    $this->series->translations()->create([
        'locale' => 'fr',
        'title' => 'Série de test',
        'description' => 'Description de la série de test',
        'slug' => 'serie-de-test',
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
    
    $this->post->translations()->create([
        'locale' => 'fr',
        'title' => 'Article de test',
        'slug' => 'article-de-test',
        'content' => 'Contenu du test',
        'reading_time' => 5,
    ]);
});

it('includes language parameter in author links on series pages', function () {
    // Visit the series page
    $response = $this->get(route('blog.series', ['locale' => 'en', 'seriesSlug' => 'test-series']));
    
    // Ensure the page loads
    $response->assertSee('Test Series');
    
    // Check that author links include the language parameter
    // The link should be something like /en/blog/author/{slug} not just /blog/author/{slug}
    $response->assertSee('/en/blog/author/');
    
    // Ensure all links have language prefix for authors
    $content = $response->getContent();
    preg_match_all('/href="([^"]*blog\/author\/[^"]*)"/', $content, $matches);
    
    foreach ($matches[1] as $link) {
        // Each author link should contain /en/blog/author/ (current locale)
        expect($link)->toContain('/en/blog/author/');
    }
});

it('works with different locales', function () {
    // Test with French locale
    $response = $this->get(route('blog.series', ['locale' => 'fr', 'seriesSlug' => 'serie-de-test']));
    
    $response->assertSee('Série de test');
    
    // Check that author links include the French language parameter
    $response->assertSee('/fr/blog/author/');
    
    $content = $response->getContent();
    preg_match_all('/href="([^"]*blog\/author\/[^"]*)"/', $content, $matches);
    
    foreach ($matches[1] as $link) {
        expect($link)->toContain('/fr/blog/author/');
    }
});
