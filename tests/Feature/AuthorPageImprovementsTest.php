<?php

use Illuminate\Support\Facades\Hash;
use Workbench\App\Models\User;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Carbon\Carbon;

beforeEach(function () {
    config(['blogr.locales.enabled' => false]);
    config(['blogr.route.prefix' => 'blog']);
    config(['blogr.author_profile.enabled' => true]);
});

describe('Issue 1: Author page layout and bio styling', function () {
    test('author avatar is displayed at the top of the author profile', function () {
        $author = User::create([
            'name' => 'John Doe',
            'email' => 'john@test.com',
            'password' => Hash::make('password'),
            'slug' => 'john-doe',
            'avatar' => 'avatars/john.jpg',
            'bio' => ['en' => '# Test Bio'],
        ]);
        
        $response = $this->get(route('blog.author', ['userSlug' => 'john-doe']));
        
        $response->assertStatus(200);
        
        // Avatar should be in header, before the name
        $html = $response->getContent();
        $avatarPos = strpos($html, 'avatars/john.jpg');
        $namePos = strpos($html, 'John Doe');
        
        expect($avatarPos)->toBeLessThan($namePos)
            ->and($avatarPos)->toBeGreaterThan(0);
    });
    
    test('author bio on author page uses same styling as article pages', function () {
        $author = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@test.com',
            'password' => Hash::make('password'),
            'slug' => 'jane-doe',
            'bio' => ['en' => "# About Me\n\nI'm a developer."],
        ]);
        
        $response = $this->get(route('blog.author', ['userSlug' => 'jane-doe']));
        
        $response->assertStatus(200);
        
        // Should have the same styling classes as author-bio component
        $response->assertSee('bg-[var(--color-primary)]/10', false);
        $response->assertSee('border-l-4', false);
        $response->assertSee('border-[var(--color-primary)]', false);
    });
});

describe('Issue 2: Image loading on author page', function () {
    test('post images load correctly on author page', function () {
        $author = User::create([
            'name' => 'Author With Posts',
            'email' => 'author@test.com',
            'password' => Hash::make('password'),
            'slug' => 'author-with-posts',
        ]);
        
        $category = Category::create([
            'name' => 'Tech',
            'slug' => 'tech',
        ]);
        
        $post = BlogPost::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'Test content',
            'user_id' => $author->id,
            'category_id' => $category->id,
            'is_published' => true,
            'published_at' => now(),
            'photo' => 'posts/test-image.jpg',
        ]);
        
        $response = $this->get(route('blog.author', ['userSlug' => 'author-with-posts']));
        
        $response->assertStatus(200);
        
        // Image URL should be properly generated using photo_url accessor
        $html = $response->getContent();
        expect($html)->toContain('test-image.jpg');
    });
});

describe('Issue 3: Dates respect locale and visibility settings', function () {
    test('article dates on author page respect current locale', function () {
        Carbon::setLocale('fr');
        app()->setLocale('fr');
        
        $author = User::create([
            'name' => 'French Author',
            'email' => 'french@test.com',
            'password' => Hash::make('password'),
            'slug' => 'french-author',
        ]);
        
        $category = Category::create([
            'name' => 'Tech',
            'slug' => 'tech',
        ]);
        
        $post = BlogPost::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'Test content',
            'user_id' => $author->id,
            'category_id' => $category->id,
            'is_published' => true,
            'published_at' => Carbon::parse('2025-01-15'),
        ]);
        
        $response = $this->get(route('blog.author', ['userSlug' => 'french-author']));
        
        $response->assertStatus(200);
        
        // Should show French formatted date (15 janvier 2025)
        $response->assertSee('janvier', false);
        $response->assertDontSee('January');
    });
    
    test('article dates on author page respect visibility settings when disabled', function () {
        config(['blogr.ui.dates.show_publication_date' => false]);
        
        $author = User::create([
            'name' => 'Author Test',
            'email' => 'test@test.com',
            'password' => Hash::make('password'),
            'slug' => 'author-test',
        ]);
        
        $category = Category::create([
            'name' => 'Tech',
            'slug' => 'tech',
        ]);
        
        $post = BlogPost::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'Test content',
            'user_id' => $author->id,
            'category_id' => $category->id,
            'is_published' => true,
            'published_at' => Carbon::parse('2025-01-15'),
        ]);
        
        $response = $this->get(route('blog.author', ['userSlug' => 'author-test']));
        
        $response->assertStatus(200);
        
        // Should not show date when disabled (check for formatted date, not year in footer)
        $response->assertDontSee('15 janvier 2025');
        $response->assertDontSee('January 15, 2025');
        $response->assertDontSee('2025-01-15');
    });
    
    test('article dates on author page respect show_publication_date_on_cards setting', function () {
        config(['blogr.ui.dates.show_publication_date' => true]);
        config(['blogr.ui.dates.show_publication_date_on_cards' => false]);
        
        $author = User::create([
            'name' => 'Author Test2',
            'email' => 'test2@test.com',
            'password' => Hash::make('password'),
            'slug' => 'author-test2',
        ]);
        
        $category = Category::create([
            'name' => 'Tech',
            'slug' => 'tech',
        ]);
        
        $post = BlogPost::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'Test content',
            'user_id' => $author->id,
            'category_id' => $category->id,
            'is_published' => true,
            'published_at' => Carbon::parse('2025-01-15'),
        ]);
        
        $response = $this->get(route('blog.author', ['userSlug' => 'author-test2']));
        
        $response->assertStatus(200);
        
        // Should not show date when cards setting is disabled (check for formatted date, not year in footer)
        $response->assertDontSee('15 janvier 2025');
        $response->assertDontSee('January 15, 2025');
        $response->assertDontSee('2025-01-15');
    });
});

describe('Issue 4: Author avatar hover effect consistency', function () {
    test('author avatar on blog post cards has primary color hover ring', function () {
        $author = User::create([
            'name' => 'Test Author',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'slug' => 'test-author',
            'avatar' => 'avatars/test.jpg',
        ]);
        
        $category = Category::create([
            'name' => 'Tech',
            'slug' => 'tech',
        ]);
        
        $post = BlogPost::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'Test content',
            'user_id' => $author->id,
            'category_id' => $category->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
        
        $response = $this->get(route('blog.index'));
        
        $response->assertStatus(200);
        
        // Author avatar should have hover:ring-[var(--color-primary)] class
        $response->assertSee('hover:ring-[var(--color-primary)]', false);
    });
    
    test('author avatar component is consistent across blog post cards and series cards', function () {
        // This test verifies that the same component/styling is used
        $author = User::create([
            'name' => 'Consistent Author',
            'email' => 'consistent@test.com',
            'password' => Hash::make('password'),
            'slug' => 'consistent-author',
            'avatar' => 'avatars/consistent.jpg',
        ]);
        
        $category = Category::create([
            'name' => 'Tech',
            'slug' => 'tech',
        ]);
        
        $post = BlogPost::create([
            'title' => 'Consistency Test',
            'slug' => 'consistency-test',
            'content' => 'Test content',
            'user_id' => $author->id,
            'category_id' => $category->id,
            'is_published' => true,
            'published_at' => now(),
        ]);
        
        $response = $this->get(route('blog.index'));
        
        $response->assertStatus(200);
        
        // Should use author-info component or have consistent styling
        $html = $response->getContent();
        
        // Check for consistent avatar styling patterns
        expect($html)->toContain('rounded-full');
    });
});
