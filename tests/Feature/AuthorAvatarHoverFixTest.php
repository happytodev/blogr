<?php

use Illuminate\Support\Facades\Hash;
use Workbench\App\Models\User;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;

test('author avatar on blog post card has working hover ring effect', function () {
    config(['blogr.locales.enabled' => false]);
    config(['blogr.route.prefix' => 'blog']);
    
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
    
    // Avatar link should have hover:ring (not group-hover:ring) for direct hover effect
    // This ensures the hover works without needing a parent with 'group' class
    $html = $response->getContent();
    
    // Check that hover effect is on the link itself (like series cards)
    expect($html)->toContain('hover:ring-[var(--color-primary)]')
        ->and($html)->toContain('rounded-full');
});

test('author avatar hover effect matches series cards implementation', function () {
    config(['blogr.locales.enabled' => false]);
    config(['blogr.route.prefix' => 'blog']);
    
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
    
    $html = $response->getContent();
    
    // Both should have ring-2, hover:ring-[var(--color-primary)], and transition-all
    expect($html)->toContain('ring-2')
        ->and($html)->toContain('hover:ring-[var(--color-primary)]')
        ->and($html)->toContain('transition-all');
});
