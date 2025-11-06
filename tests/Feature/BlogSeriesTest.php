<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a default category for tests
    Category::create([
        'name' => 'Test Category',
        'slug' => 'test-category',
        'is_default' => true,
    ]);
});

it('can create a blog series', function () {
    $series = BlogSeries::create([
        'slug' => 'learn-laravel',
        'position' => 1,
        'published_at' => now(),
    ]);

    expect($series)->toBeInstanceOf(BlogSeries::class)
        ->and($series->slug)->toBe('learn-laravel')
        ->and($series->position)->toBe(1)
        ->and($series->published_at)->not->toBeNull();
});

it('can add translations to a series', function () {
    $series = BlogSeries::create([
        'slug' => 'learn-laravel',
        'position' => 1,
    ]);

    $series->translations()->create([
        'locale' => 'en',
        'title' => 'Learn Laravel',
        'description' => 'A complete Laravel tutorial series',
    ]);

    $series->translations()->create([
        'locale' => 'fr',
        'title' => 'Apprendre Laravel',
        'description' => 'Une série complète de tutoriels Laravel',
    ]);

    expect($series->translations)->toHaveCount(2)
        ->and($series->translate('en')->title)->toBe('Learn Laravel')
        ->and($series->translate('fr')->title)->toBe('Apprendre Laravel');
});

it('can list articles in a series ordered by position', function () {
    $series = BlogSeries::create(['slug' => 'learn-laravel']);
    
    $post1 = BlogPost::create([
        'title' => 'First Post',
        'slug' => 'first-post',
        'content' => 'Content',
        'blog_series_id' => $series->id,
        'series_position' => 1,
        'default_locale' => 'en',
        'user_id' => 1,
        'category_id' => 1,
    ]);

    $post3 = BlogPost::create([
        'title' => 'Third Post',
        'slug' => 'third-post',
        'content' => 'Content',
        'blog_series_id' => $series->id,
        'series_position' => 3,
        'default_locale' => 'en',
        'user_id' => 1,
        'category_id' => 1,
    ]);

    $post2 = BlogPost::create([
        'title' => 'Second Post',
        'slug' => 'second-post',
        'content' => 'Content',
        'blog_series_id' => $series->id,
        'series_position' => 2,
        'default_locale' => 'en',
        'user_id' => 1,
        'category_id' => 1,
    ]);

    $orderedPosts = $series->posts()->orderBy('series_position')->get();

    expect($orderedPosts)->toHaveCount(3)
        ->and($orderedPosts->first()->series_position)->toBe(1)
        ->and($orderedPosts->get(1)->series_position)->toBe(2)
        ->and($orderedPosts->last()->series_position)->toBe(3);
});

it('allows gaps in series positions', function () {
    $series = BlogSeries::create(['slug' => 'learn-laravel']);
    
    $post1 = BlogPost::create([
        'blog_series_id' => $series->id,
        'series_position' => 1,
        'default_locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post-' . uniqid(),
        'content' => 'Test content',
        'user_id' => 1,
        'category_id' => 1,
    ]);

    $post5 = BlogPost::create([
        'blog_series_id' => $series->id,
        'series_position' => 5,
        'default_locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post-' . uniqid(),
        'content' => 'Test content',
        'user_id' => 1,
        'category_id' => 1,
    ]);

    expect($series->posts)->toHaveCount(2)
        ->and($series->posts->pluck('series_position')->toArray())->toBe([1, 5]);
});

it('can get the next article in a series', function () {
    $series = BlogSeries::create(['slug' => 'learn-laravel']);
    
    $post1 = BlogPost::create([
        'blog_series_id' => $series->id,
        'series_position' => 1,
        'default_locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post-' . uniqid(),
        'content' => 'Test content',
        'user_id' => 1,
        'category_id' => 1,
    ]);

    $post2 = BlogPost::create([
        'blog_series_id' => $series->id,
        'series_position' => 2,
        'default_locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post-' . uniqid(),
        'content' => 'Test content',
        'user_id' => 1,
        'category_id' => 1,
    ]);

    $nextPost = $post1->nextInSeries();

    expect($nextPost)->not->toBeNull()
        ->and($nextPost->id)->toBe($post2->id)
        ->and($nextPost->series_position)->toBe(2);
});

it('can get the previous article in a series', function () {
    $series = BlogSeries::create(['slug' => 'learn-laravel']);
    
    $post1 = BlogPost::create([
        'blog_series_id' => $series->id,
        'series_position' => 1,
        'default_locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post-' . uniqid(),
        'content' => 'Test content',
        'user_id' => 1,
        'category_id' => 1,
    ]);

    $post2 = BlogPost::create([
        'blog_series_id' => $series->id,
        'series_position' => 2,
        'default_locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post-' . uniqid(),
        'content' => 'Test content',
        'user_id' => 1,
        'category_id' => 1,
    ]);

    $previousPost = $post2->previousInSeries();

    expect($previousPost)->not->toBeNull()
        ->and($previousPost->id)->toBe($post1->id)
        ->and($previousPost->series_position)->toBe(1);
});

it('returns null when there is no next article in series', function () {
    $series = BlogSeries::create(['slug' => 'learn-laravel']);
    
    $post = BlogPost::create([
        'blog_series_id' => $series->id,
        'series_position' => 1,
        'default_locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post-' . uniqid(),
        'content' => 'Test content',
        'user_id' => 1,
        'category_id' => 1,
    ]);

    $nextPost = $post->nextInSeries();

    expect($nextPost)->toBeNull();
});

it('returns null when there is no previous article in series', function () {
    $series = BlogSeries::create(['slug' => 'learn-laravel']);
    
    $post = BlogPost::create([
        'blog_series_id' => $series->id,
        'series_position' => 1,
        'default_locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post-' . uniqid(),
        'content' => 'Test content',
        'user_id' => 1,
        'category_id' => 1,
    ]);

    $previousPost = $post->previousInSeries();

    expect($previousPost)->toBeNull();
});

it('can get series navigation for a post', function () {
    $series = BlogSeries::create(['slug' => 'learn-laravel']);
    
    $post1 = BlogPost::create([
        'blog_series_id' => $series->id,
        'series_position' => 1,
        'default_locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post-' . uniqid(),
        'content' => 'Test content',
        'user_id' => 1,
        'category_id' => 1,
    ]);

    $post2 = BlogPost::create([
        'blog_series_id' => $series->id,
        'series_position' => 2,
        'default_locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post-' . uniqid(),
        'content' => 'Test content',
        'user_id' => 1,
        'category_id' => 1,
    ]);

    $post3 = BlogPost::create([
        'blog_series_id' => $series->id,
        'series_position' => 3,
        'default_locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post-' . uniqid(),
        'content' => 'Test content',
        'user_id' => 1,
        'category_id' => 1,
    ]);

    $navigation = $post2->getSeriesNavigation();

    expect($navigation)->toHaveKeys(['previous', 'current', 'next', 'all'])
        ->and($navigation['previous']->id)->toBe($post1->id)
        ->and($navigation['current']->id)->toBe($post2->id)
        ->and($navigation['next']->id)->toBe($post3->id)
        ->and($navigation['all'])->toHaveCount(3);
});

it('series is optional for blog posts', function () {
    $post = BlogPost::create([
        'blog_series_id' => null,
        'series_position' => null,
        'default_locale' => 'en',
        'title' => 'Test Post',
        'slug' => 'test-post-' . uniqid(),
        'content' => 'Test content',
        'user_id' => 1,
        'category_id' => 1,
    ]);

    expect($post->blog_series_id)->toBeNull()
        ->and($post->series_position)->toBeNull()
        ->and($post->series)->toBeNull();
});

it('auto-generates slug when not provided for blog series', function () {
    $series = BlogSeries::create([
        'position' => 1,
    ]);
    
    expect($series->slug)
        ->not()->toBeNull()
        ->and($series->slug)->toStartWith('series-');
});

it('requires unique slug for blog series', function () {
    BlogSeries::create(['slug' => 'learn-laravel']);

    expect(fn () => BlogSeries::create([
        'slug' => 'learn-laravel',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

it('can scope to published series only', function () {
    // Create published series
    $published1 = BlogSeries::create([
        'slug' => 'published-1',
        'published_at' => now()->subDay(),
    ]);

    $published2 = BlogSeries::create([
        'slug' => 'published-2',
        'published_at' => now(),
    ]);

    // Create draft series (null published_at)
    $draft = BlogSeries::create([
        'slug' => 'draft',
        'published_at' => null,
    ]);

    // Create future series
    $future = BlogSeries::create([
        'slug' => 'future',
        'published_at' => now()->addWeek(),
    ]);

    $publishedSeries = BlogSeries::published()->get();

    expect($publishedSeries)->toHaveCount(2)
        ->and($publishedSeries->pluck('slug')->toArray())->toContain('published-1', 'published-2')
        ->and($publishedSeries->pluck('slug')->toArray())->not->toContain('draft', 'future');
});

it('can scope to featured series only', function () {
    $featured1 = BlogSeries::create([
        'slug' => 'featured-1',
        'is_featured' => true,
    ]);

    $featured2 = BlogSeries::create([
        'slug' => 'featured-2',
        'is_featured' => true,
    ]);

    $regular = BlogSeries::create([
        'slug' => 'regular',
        'is_featured' => false,
    ]);

    $featuredSeries = BlogSeries::featured()->get();

    expect($featuredSeries)->toHaveCount(2)
        ->and($featuredSeries->pluck('slug')->toArray())->toContain('featured-1', 'featured-2')
        ->and($featuredSeries->pluck('slug')->toArray())->not->toContain('regular');
});

it('can combine published and featured scopes', function () {
    $publishedFeatured = BlogSeries::create([
        'slug' => 'published-featured',
        'is_featured' => true,
        'published_at' => now(),
    ]);

    $publishedRegular = BlogSeries::create([
        'slug' => 'published-regular',
        'is_featured' => false,
        'published_at' => now(),
    ]);

    $draftFeatured = BlogSeries::create([
        'slug' => 'draft-featured',
        'is_featured' => true,
        'published_at' => null,
    ]);

    $series = BlogSeries::published()->featured()->get();

    expect($series)->toHaveCount(1)
        ->and($series->first()->slug)->toBe('published-featured');
});

it('can check if series is published', function () {
    $published = BlogSeries::create([
        'slug' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $alwaysPublished = BlogSeries::create([
        'slug' => 'always-published',
        'published_at' => null, // null = always published
    ]);

    $future = BlogSeries::create([
        'slug' => 'future',
        'published_at' => now()->addWeek(),
    ]);

    expect($published->isPublished())->toBeTrue()
        ->and($alwaysPublished->isPublished())->toBeTrue() // null = always published
        ->and($future->isPublished())->toBeFalse();
});

it('can check if series is featured', function () {
    $featured = BlogSeries::create([
        'slug' => 'featured',
        'is_featured' => true,
    ]);

    $regular = BlogSeries::create([
        'slug' => 'regular',
        'is_featured' => false,
    ]);

    expect($featured->isFeatured())->toBeTrue()
        ->and($regular->isFeatured())->toBeFalse();
});

it('only shows published series on frontend', function () {
    $publishedSeries = BlogSeries::create([
        'slug' => 'published-series',
        'published_at' => now(),
    ]);

    $publishedSeries->translations()->create([
        'locale' => 'en',
        'title' => 'Published Series',
        'description' => 'This is published',
    ]);

    $draftSeries = BlogSeries::create([
        'slug' => 'draft-series',
        'published_at' => null,
    ]);

    $draftSeries->translations()->create([
        'locale' => 'en',
        'title' => 'Draft Series',
        'description' => 'This is a draft',
    ]);

    // Test that published series can be retrieved
    $publishedResult = BlogSeries::published()->where('slug', 'published-series')->first();
    expect($publishedResult)->not->toBeNull();

    // Test that draft series is not in published scope
    $draftResult = BlogSeries::published()->where('slug', 'draft-series')->first();
    expect($draftResult)->toBeNull();
});

// ============================================================================
// SERIES TRANSLATION TESTS
// ============================================================================

test('series can have translations', function () {
    $series = BlogSeries::create([
        'slug' => 'my-series',
        'published_at' => now(),
    ]);
    
    $series->translations()->create([
        'locale' => 'en',
        'title' => 'My Series',
        'description' => 'English description',
    ]);
    
    $series->translations()->create([
        'locale' => 'fr',
        'title' => 'Ma Série',
        'description' => 'Description française',
    ]);
    
    expect($series->translations()->count())->toBe(2);
    expect($series->translations()->where('locale', 'fr')->first()->title)->toBe('Ma Série');
});

test('series translation can be retrieved by locale', function () {
    $series = BlogSeries::create([
        'slug' => 'test-series',
        'published_at' => now(),
    ]);
    
    $series->translations()->create([
        'locale' => 'en',
        'title' => 'Test Series',
        'description' => 'Test description',
    ]);
    
    $series->translations()->create([
        'locale' => 'es',
        'title' => 'Serie de Prueba',
        'description' => 'Descripción de prueba',
    ]);
    
    $translation = $series->translations()->where('locale', 'es')->first();
    
    expect($translation)->not->toBeNull();
    expect($translation->title)->toBe('Serie de Prueba');
    expect($translation->description)->toBe('Descripción de prueba');
});

test('series can have multiple language translations', function () {
    $series = BlogSeries::create([
        'slug' => 'multilingual-series',
        'published_at' => now(),
    ]);
    
    $locales = ['en', 'fr', 'es', 'de'];
    
    foreach ($locales as $locale) {
        $series->translations()->create([
            'locale' => $locale,
            'title' => "Title in {$locale}",
            'description' => "Description in {$locale}",
        ]);
    }
    
    expect($series->translations()->count())->toBe(4);
    $storedLocales = $series->translations()->pluck('locale')->sort()->values()->toArray();
    $expectedLocales = collect($locales)->sort()->values()->toArray();
    expect($storedLocales)->toEqual($expectedLocales);
});

test('blog controller eager loads series translations', function () {
    $post = BlogPost::factory()->create([
        'is_published' => true,
        'published_at' => now(),
    ]);
    
    $series = BlogSeries::create([
        'slug' => 'test-series',
        'published_at' => now(),
    ]);
    
    $series->translations()->create([
        'locale' => 'en',
        'title' => 'Test Series',
        'description' => 'Test description',
    ]);
    
    $series->translations()->create([
        'locale' => 'fr',
        'title' => 'Série de Test',
        'description' => 'Description de test',
    ]);
    
    $post->update([
        'blog_series_id' => $series->id,
        'series_position' => 1,
    ]);
    
    // Get the post's default translation
    $translation = $post->translations()->where('locale', 'en')->first();
    
    // Verify series translations are accessible
    $freshPost = BlogPost::with(['series.translations'])->find($post->id);
    
    expect($freshPost->series)->not->toBeNull();
    expect($freshPost->series->translations)->toHaveCount(2);
    expect($freshPost->series->translations->firstWhere('locale', 'fr')->title)->toBe('Série de Test');
});

