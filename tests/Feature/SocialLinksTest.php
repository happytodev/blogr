<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['blogr.ui.footer.enabled' => true]);
    config(['blogr.ui.footer.show_social_links' => true]);
});

test('footer displays bluesky social link when configured', function () {
    config(['blogr.ui.footer.social_links.bluesky' => 'https://bsky.app/profile/happytodev.bsky.social']);
    
    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    $response->assertSee('https://bsky.app/profile/happytodev.bsky.social', false);
    $response->assertSee('Bluesky');
});

test('footer displays youtube social link when configured', function () {
    config(['blogr.ui.footer.social_links.youtube' => 'https://youtube.com/@happytodev']);
    
    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    $response->assertSee('https://youtube.com/@happytodev', false);
    $response->assertSee('YouTube');
});

test('footer displays instagram social link when configured', function () {
    config(['blogr.ui.footer.social_links.instagram' => 'https://instagram.com/happytodev']);
    
    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    $response->assertSee('https://instagram.com/happytodev', false);
    $response->assertSee('Instagram');
});

test('footer displays tiktok social link when configured', function () {
    config(['blogr.ui.footer.social_links.tiktok' => 'https://tiktok.com/@happytodev']);
    
    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    $response->assertSee('https://tiktok.com/@happytodev', false);
    $response->assertSee('TikTok');
});

test('footer displays mastodon social link when configured', function () {
    config(['blogr.ui.footer.social_links.mastodon' => 'https://mastodon.social/@happytodev']);
    
    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    $response->assertSee('https://mastodon.social/@happytodev', false);
    $response->assertSee('Mastodon');
});

test('footer does not display social links when not configured', function () {
    config(['blogr.ui.footer.social_links.bluesky' => null]);
    config(['blogr.ui.footer.social_links.youtube' => null]);
    config(['blogr.ui.footer.social_links.instagram' => null]);
    config(['blogr.ui.footer.social_links.tiktok' => null]);
    config(['blogr.ui.footer.social_links.mastodon' => null]);
    
    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    $response->assertDontSee('bsky.app');
    $response->assertDontSee('youtube.com/@');
    $response->assertDontSee('instagram.com');
    $response->assertDontSee('tiktok.com/@');
    $response->assertDontSee('mastodon.social/@');
});

test('all new social links can be configured together', function () {
    config([
        'blogr.ui.footer.social_links.bluesky' => 'https://bsky.app/profile/happytodev.bsky.social',
        'blogr.ui.footer.social_links.youtube' => 'https://youtube.com/@happytodev',
        'blogr.ui.footer.social_links.instagram' => 'https://instagram.com/happytodev',
        'blogr.ui.footer.social_links.tiktok' => 'https://tiktok.com/@happytodev',
        'blogr.ui.footer.social_links.mastodon' => 'https://mastodon.social/@happytodev',
    ]);
    
    $response = $this->get(route('blog.index', ['locale' => 'en']));
    
    $response->assertStatus(200);
    $response->assertSee('Bluesky');
    $response->assertSee('YouTube');
    $response->assertSee('Instagram');
    $response->assertSee('TikTok');
    $response->assertSee('Mastodon');
});
