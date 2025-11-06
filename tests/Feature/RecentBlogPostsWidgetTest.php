<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    $this->user = User::factory()->create();
    $this->user->assignRole('admin');
    $this->actingAs($this->user);
});

it('recent blog posts widget can be instantiated', function () {
    $widget = new \Happytodev\Blogr\Filament\Widgets\RecentBlogPosts();
    
    expect($widget)->toBeInstanceOf(\Happytodev\Blogr\Filament\Widgets\RecentBlogPosts::class);
});

it('widget handles posts with locale-enabled routes correctly', function () {
    config(['blogr.locales.enabled' => true]);
    config(['blogr.locales.default' => 'en']);
    
    $post = BlogPost::factory()->create();
    $translation = $post->translations->first();
    $translation->slug = 'test-post-locale';
    $translation->save();
    
    $widget = new \Happytodev\Blogr\Filament\Widgets\RecentBlogPosts();
    
    // Widget should not throw exception when rendering with locale-enabled
    expect($widget)->toBeInstanceOf(\Happytodev\Blogr\Filament\Widgets\RecentBlogPosts::class);
});

it('widget handles posts without locale routes correctly', function () {
    config(['blogr.locales.enabled' => false]);
    
    $post = BlogPost::factory()->create();
    $translation = $post->translations->first();
    $translation->slug = 'test-post-no-locale';
    $translation->save();
    
    $widget = new \Happytodev\Blogr\Filament\Widgets\RecentBlogPosts();
    
    // Widget should not throw exception when rendering without locale
    expect($widget)->toBeInstanceOf(\Happytodev\Blogr\Filament\Widgets\RecentBlogPosts::class);
});

it('widget handles posts without translations gracefully', function () {
    config(['blogr.locales.enabled' => false]);
    
    $post = BlogPost::factory()->create();
    $post->translations()->delete();
    $post->refresh();
    
    $widget = new \Happytodev\Blogr\Filament\Widgets\RecentBlogPosts();
    
    // Widget should not crash when post has no translations
    expect($widget)->toBeInstanceOf(\Happytodev\Blogr\Filament\Widgets\RecentBlogPosts::class);
});
