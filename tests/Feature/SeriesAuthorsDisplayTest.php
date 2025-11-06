<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('setting to show/hide series authors exists', function () {
    $config = config('blogr.display.show_series_authors');
    
    expect($config)->not->toBeNull();
    expect($config)->toBeBool();
});

it('series authors limit setting exists and has default value', function () {
    $limit = config('blogr.display.series_authors_limit');
    
    expect($limit)->not->toBeNull();
    expect($limit)->toBeInt();
    expect($limit)->toBe(4);
});

// ============================================================================
// SERIES MODEL - AUTHORS RELATIONSHIP TESTS
// ============================================================================

it('series model has authors method', function () {
    $series = \Happytodev\Blogr\Models\BlogSeries::factory()->create();
    
    expect(method_exists($series, 'authors'))->toBe(true);
    expect($series->authors())->toBeArray();
});

it('series authors returns unique users from posts', function () {
    $series = \Happytodev\Blogr\Models\BlogSeries::factory()->create();
    $category = \Happytodev\Blogr\Models\Category::factory()->create();
    
    // Create 3 users
    $user1 = \Happytodev\Blogr\Models\User::factory()->create(['slug' => 'author-one']);
    $user2 = \Happytodev\Blogr\Models\User::factory()->create(['slug' => 'author-two']);
    $user3 = \Happytodev\Blogr\Models\User::factory()->create(['slug' => 'author-three']);
    
    // Create posts: 2 by user1, 2 by user2, 1 by user3
    \Happytodev\Blogr\Models\BlogPost::factory()->count(2)->create([
        'blog_series_id' => $series->id,
        'category_id' => $category->id,
        'user_id' => $user1->id,
        'is_published' => true,
    ]);
    
    \Happytodev\Blogr\Models\BlogPost::factory()->count(2)->create([
        'blog_series_id' => $series->id,
        'category_id' => $category->id,
        'user_id' => $user2->id,
        'is_published' => true,
    ]);
    
    \Happytodev\Blogr\Models\BlogPost::factory()->create([
        'blog_series_id' => $series->id,
        'category_id' => $category->id,
        'user_id' => $user3->id,
        'is_published' => true,
    ]);
    
    $authors = $series->authors();
    
    expect($authors)->toHaveCount(3);
    $authorIds = array_map(fn($author) => $author['id'], $authors);
    expect($authorIds)->toContain($user1->id, $user2->id, $user3->id);
});

it('series authors are ordered by post count descending', function () {
    $series = \Happytodev\Blogr\Models\BlogSeries::factory()->create();
    $category = \Happytodev\Blogr\Models\Category::factory()->create();
    
    $user1 = \Happytodev\Blogr\Models\User::factory()->create(['slug' => 'author-one']);
    $user2 = \Happytodev\Blogr\Models\User::factory()->create(['slug' => 'author-two']);
    $user3 = \Happytodev\Blogr\Models\User::factory()->create(['slug' => 'author-three']);
    
    // user1: 3 posts, user2: 5 posts, user3: 2 posts
    \Happytodev\Blogr\Models\BlogPost::factory()->count(3)->create([
        'blog_series_id' => $series->id,
        'category_id' => $category->id,
        'user_id' => $user1->id,
        'is_published' => true,
    ]);
    
    \Happytodev\Blogr\Models\BlogPost::factory()->count(5)->create([
        'blog_series_id' => $series->id,
        'category_id' => $category->id,
        'user_id' => $user2->id,
        'is_published' => true,
    ]);
    
    \Happytodev\Blogr\Models\BlogPost::factory()->count(2)->create([
        'blog_series_id' => $series->id,
        'category_id' => $category->id,
        'user_id' => $user3->id,
        'is_published' => true,
    ]);
    
    $authors = $series->authors();
    
    // Should be ordered: user2 (5), user1 (3), user3 (2)
    expect($authors[0]['id'])->toBe($user2->id);
    expect($authors[1]['id'])->toBe($user1->id);
    expect($authors[2]['id'])->toBe($user3->id);
});

it('series without posts returns empty authors array', function () {
    $series = \Happytodev\Blogr\Models\BlogSeries::factory()->create();
    
    $authors = $series->authors();
    
    expect($authors)->toBeArray();
    expect($authors)->toHaveCount(0);
});

// ============================================================================
// COMPONENT RENDERING TESTS
// ============================================================================

it('series authors component renders with avatars', function () {
    config(['blogr.display.show_series_authors' => true]);
    
    $series = \Happytodev\Blogr\Models\BlogSeries::factory()->create();
    $category = \Happytodev\Blogr\Models\Category::factory()->create();
    
    $user1 = \Happytodev\Blogr\Models\User::factory()->create([
        'name' => 'John Doe',
        'slug' => 'john-doe',
        'avatar' => 'avatars/john.jpg',
    ]);
    
    $user2 = \Happytodev\Blogr\Models\User::factory()->create([
        'name' => 'Jane Smith',
        'slug' => 'jane-smith',
        'avatar' => 'avatars/jane.jpg',
    ]);
    
    \Happytodev\Blogr\Models\BlogPost::factory()->create([
        'blog_series_id' => $series->id,
        'category_id' => $category->id,
        'user_id' => $user1->id,
        'is_published' => true,
    ]);
    
    \Happytodev\Blogr\Models\BlogPost::factory()->create([
        'blog_series_id' => $series->id,
        'category_id' => $category->id,
        'user_id' => $user2->id,
        'is_published' => true,
    ]);
    
    $authors = $series->authors();
    $view = view('blogr::components.series-authors', ['authors' => $authors]);
    $html = $view->render();
    
    expect($html)->toContain('avatars/john.jpg');
    expect($html)->toContain('avatars/jane.jpg');
    expect($html)->toContain('john-doe'); // Tooltip
    expect($html)->toContain('jane-smith');
});

it('series authors component shows plus indicator when exceeding limit', function () {
    config(['blogr.display.show_series_authors' => true]);
    config(['blogr.display.series_authors_limit' => 3]);
    
    $series = \Happytodev\Blogr\Models\BlogSeries::factory()->create();
    $category = \Happytodev\Blogr\Models\Category::factory()->create();
    
    // Create 5 authors (exceeds limit of 3)
    for ($i = 1; $i <= 5; $i++) {
        $user = \Happytodev\Blogr\Models\User::factory()->create(['slug' => "author-{$i}"]);
        \Happytodev\Blogr\Models\BlogPost::factory()->create([
            'blog_series_id' => $series->id,
            'category_id' => $category->id,
            'user_id' => $user->id,
            'is_published' => true,
        ]);
    }
    
    $authors = $series->authors();
    $view = view('blogr::components.series-authors', ['authors' => $authors]);
    $html = $view->render();
    
    // Should show "+2" indicator (5 total - 3 visible)
    expect($html)->toContain('+2');
});

it('series authors component hides when setting is disabled', function () {
    config(['blogr.display.show_series_authors' => false]);
    
    $series = \Happytodev\Blogr\Models\BlogSeries::factory()->create();
    $category = \Happytodev\Blogr\Models\Category::factory()->create();
    
    $user = \Happytodev\Blogr\Models\User::factory()->create();
    \Happytodev\Blogr\Models\BlogPost::factory()->create([
        'blog_series_id' => $series->id,
        'category_id' => $category->id,
        'user_id' => $user->id,
        'is_published' => true,
    ]);
    
    $authors = $series->authors();
    $view = view('blogr::components.series-authors', ['authors' => $authors]);
    $html = $view->render();
    
    // Component should not render anything
    expect(trim($html))->toBe('');
});
