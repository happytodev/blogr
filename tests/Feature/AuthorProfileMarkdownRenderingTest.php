<?php

use Happytodev\Blogr\Helpers\MarkdownHelper;
use Illuminate\Support\Facades\Hash;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->author = User::create([
        'name' => 'Markdown Author',
        'email' => 'markdown@example.com',
        'password' => Hash::make('password123'),
        'slug' => 'markdown-author',
        'avatar' => 'avatars/author.jpg',
        'bio' => [
            'en' => <<<'MD'
# About Me

I'm a **passionate** developer who loves:

- Writing clean code
- Learning new technologies
- Sharing knowledge

Check out my [website](https://example.com)!
MD,
            'fr' => <<<'MD'
# À propos de moi

Je suis un développeur **passionné** qui aime :

- Écrire du code propre
- Apprendre de nouvelles technologies
- Partager les connaissances

Visitez mon [site web](https://example.com) !
MD,
        ],
    ]);
});

test('author profile page renders markdown bio as HTML', function () {
    config(['blogr.locales.enabled' => false]);
    config(['blogr.route.prefix' => 'blog']);
    
    $response = $this->get(route('blog.author', ['userSlug' => $this->author->slug]));
    
    $response->assertStatus(200);
    
    // Should contain HTML rendered from markdown
    $response->assertSee('<h1>About Me</h1>', false);
    $response->assertSee('<strong>passionate</strong>', false);
    $response->assertSee('<ul>', false);
    $response->assertSee('<li>Writing clean code</li>', false);
    $response->assertSee('<a href="https://example.com">website</a>', false);
});

test('author profile page does not show raw markdown syntax', function () {
    config(['blogr.locales.enabled' => false]);
    config(['blogr.route.prefix' => 'blog']);
    
    $response = $this->get(route('blog.author', ['userSlug' => $this->author->slug]));
    
    $response->assertStatus(200);
    
    // Should NOT contain raw markdown syntax
    $response->assertDontSee('# About Me');
    $response->assertDontSee('**passionate**');
    $response->assertDontSee('- Writing clean code');
    $response->assertDontSee('[website](https://example.com)');
});

test('author profile page renders markdown in correct locale', function () {
    config(['blogr.locales.enabled' => true]);
    config(['blogr.locales.default' => 'fr']);
    config(['blogr.route.prefix' => 'blog']);
    app()->setLocale('fr');
    
    $response = $this->get(route('blog.author', ['locale' => 'fr', 'userSlug' => $this->author->slug]));
    
    $response->assertStatus(200);
    
    // Should contain French HTML
    $response->assertSee('<h1>À propos de moi</h1>', false);
    $response->assertSee('développeur <strong>passionné</strong>', false);
    $response->assertSee('<li>Écrire du code propre</li>', false);
    $response->assertSee('<a href="https://example.com">site web</a>', false);
});

test('MarkdownHelper converts markdown to safe HTML', function () {
    $markdown = <<<'MD'
# Heading 1
## Heading 2

**Bold text** and *italic text*

- Item 1
- Item 2

[Link](https://example.com)

```php
echo "code block";
```
MD;

    $html = MarkdownHelper::toHtml($markdown);
    
    expect($html)->toContain('<h1>Heading 1</h1>')
        ->and($html)->toContain('<h2>Heading 2</h2>')
        ->and($html)->toContain('<strong>Bold text</strong>')
        ->and($html)->toContain('<em>italic text</em>')
        ->and($html)->toContain('<ul>')
        ->and($html)->toContain('<li>Item 1</li>')
        ->and($html)->toContain('<a href="https://example.com">Link</a>')
        ->and($html)->toContain('<code class="language-php">');
});

test('MarkdownHelper escapes potentially dangerous HTML', function () {
    $markdown = '<script>alert("XSS")</script> Safe text';
    
    $html = MarkdownHelper::toHtml($markdown);
    
    // Should escape the script tag
    expect($html)->not->toContain('<script>')
        ->and($html)->toContain('&lt;script&gt;')
        ->and($html)->toContain('Safe text');
});

test('author profile with empty bio does not break', function () {
    config(['blogr.locales.enabled' => false]);
    config(['blogr.route.prefix' => 'blog']);
    
    $authorWithoutBio = User::create([
        'name' => 'No Bio Author',
        'email' => 'nobio@example.com',
        'password' => Hash::make('password123'),
        'slug' => 'no-bio-author',
        'bio' => ['en' => '', 'fr' => ''],
    ]);
    
    $response = $this->get(route('blog.author', ['userSlug' => $authorWithoutBio->slug]));
    
    $response->assertStatus(200);
});
