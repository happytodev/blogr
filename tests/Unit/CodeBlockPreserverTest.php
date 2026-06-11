<?php

use Happytodev\Blogr\Services\Translation\CodeBlockPreserver;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('blogr.translation', [
        'provider' => 'libretranslate',
        'libretranslate' => ['url' => 'http://localhost:5000'],
    ]);
});

it('preserves fenced code blocks during translation', function () {
    Http::fake([
        'http://localhost:5000/*' => function ($request) {
            $body = json_decode($request->body(), true);
            $q = $body['q'] ?? '';

            $block = "```php\necho 'hello';\n```";
            $hash = md5($block);
            $q = str_replace($block, "___BLOGR_CODE_{$hash}___", $q);

            return Http::response(['translatedText' => "Texte traduit. {$q} Suite."]);
        },
    ]);

    $content = "Some text\n```php\necho 'hello';\n```\nMore text";

    $provider = app(\Happytodev\Blogr\Services\Translation\TranslationProviderFactory::class)->make();
    $preserver = new CodeBlockPreserver;

    $result = $preserver->translateContent($provider, $content, 'en', 'fr');

    expect($result)->toContain("echo 'hello';")
        ->and($result)->toContain('Texte traduit');
});

it('preserves inline code blocks', function () {
    Http::fake([
        'http://localhost:5000/*' => function ($request) {
            $body = json_decode($request->body(), true);
            $q = $body['q'] ?? '';

            $inline = '`$var`';
            $hash = md5($inline);
            $q = str_replace($inline, "___BLOGR_CODE_{$hash}___", $q);

            return Http::response(['translatedText' => "Utilisez la fonction {$q} ici"]);
        },
    ]);

    $content = "Use the `\$var` function here";

    $provider = app(\Happytodev\Blogr\Services\Translation\TranslationProviderFactory::class)->make();
    $preserver = new CodeBlockPreserver;

    $result = $preserver->translateContent($provider, $content, 'en', 'fr');

    expect($result)->toContain('`$var`')
        ->and($result)->toContain('Utilisez la fonction');
});

it('preserves multiple code blocks', function () {
    Http::fake([
        'http://localhost:5000/*' => function ($request) {
            $body = json_decode($request->body(), true);
            $q = $body['q'] ?? '';

            $block1 = "```\ncode1\n```";
            $block2 = "```\ncode2\n```";
            $p1 = "___BLOGR_CODE_" . md5($block1) . "___";
            $p2 = "___BLOGR_CODE_" . md5($block2) . "___";
            $q = str_replace([$block1, $block2], [$p1, $p2], $q);

            return Http::response(['translatedText' => "Debut\n{$q}\nFin"]);
        },
    ]);

    $content = "Start\n```\ncode1\n```\nMiddle\n```\ncode2\n```\nEnd";

    $provider = app(\Happytodev\Blogr\Services\Translation\TranslationProviderFactory::class)->make();
    $preserver = new CodeBlockPreserver;

    $result = $preserver->translateContent($provider, $content, 'en', 'fr');

    expect($result)->toContain('code1')
        ->and($result)->toContain('code2');
});

it('handles empty content', function () {
    $preserver = new CodeBlockPreserver;
    $provider = app(\Happytodev\Blogr\Services\Translation\TranslationProviderFactory::class)->make();

    $result = $preserver->translateContent($provider, '', 'en', 'fr');

    expect($result)->toBe('');
});

it('handles content without code blocks', function () {
    Http::fake([
        'http://localhost:5000/*' => Http::response(['translatedText' => 'Bonjour le monde']),
    ]);

    $provider = app(\Happytodev\Blogr\Services\Translation\TranslationProviderFactory::class)->make();
    $preserver = new CodeBlockPreserver;

    $result = $preserver->translateContent($provider, 'Hello world', 'en', 'fr');

    expect($result)->toBe('Bonjour le monde');
});

it('preserves code blocks in nested markdown', function () {
    Http::fake([
        'http://localhost:5000/*' => function ($request) {
            $body = json_decode($request->body(), true);
            $q = $body['q'] ?? '';

            $block = "```\nfunction test() {\n    return true;\n}\n```";
            $inline = '`$code`';
            $pBlock = "___BLOGR_CODE_" . md5($block) . "___";
            $pInline = "___BLOGR_CODE_" . md5($inline) . "___";

            $q = str_replace([$block, $inline], [$pBlock, $pInline], $q);

            return Http::response(['translatedText' => "## Titre\n\nParagraphe avec **gras** et {$pInline}.\n\n{$pBlock}\n\nFin."]);
        },
    ]);

    $content = "## Title\n\nParagraph with **bold** and `\$code`.\n\n```\nfunction test() {\n    return true;\n}\n```\n\nEnd.";

    $provider = app(\Happytodev\Blogr\Services\Translation\TranslationProviderFactory::class)->make();
    $preserver = new CodeBlockPreserver;

    $result = $preserver->translateContent($provider, $content, 'en', 'fr');

    expect($result)->toContain('function test()')
        ->and($result)->toContain('`$code`')
        ->and($result)->toContain('## Titre')
        ->and($result)->toContain('**gras**');
});

it('preserves code block when provider translates its content', function () {
    Http::fake([
        '*/translate' => Http::response(['translatedText' => "D\xe9but\n___BLOGR_CODE_" . md5("```php\necho 1;\n```") . "___\nFin"]),
    ]);

    $content = "Debut\n```php\necho 1;\n```\nFin";

    $provider = app(\Happytodev\Blogr\Services\Translation\TranslationProviderFactory::class)->make();
    $preserver = new CodeBlockPreserver;

    $result = $preserver->translateContent($provider, $content, 'en', 'fr');

    expect($result)->toContain('echo 1;');
});

it('preserves code block with protected method directly', function () {
    $preserver = new CodeBlockPreserver;
    $text = "Hello `\$x = 1;` world\n```php\necho 'hi';\n```";

    $protected = $preserver->protect($text);
    $restored = $preserver->restore($protected);

    expect($restored)->toBe($text);
});
