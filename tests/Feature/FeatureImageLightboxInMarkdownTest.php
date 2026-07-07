<?php

use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

use Happytodev\Blogr\Helpers\MarkdownHelper;

test('markdown image is wrapped in lightbox trigger anchor', function () {
    $html = MarkdownHelper::toHtml('![My Alt](https://example.com/image.jpg)');

    expect($html)
        ->toContain('<a href="https://example.com/image.jpg" class="blogr-lightbox-trigger"')
        ->and($html)->toContain('<img src="https://example.com/image.jpg" alt="My Alt"');
});

test('markdown image with title sets data-caption attribute', function () {
    $html = MarkdownHelper::toHtml('![My Alt](https://example.com/image.jpg "Photo caption")');

    expect($html)
        ->toContain('data-caption="Photo caption"');
});

test('markdown image inside a link is not wrapped in lightbox trigger', function () {
    $html = MarkdownHelper::toHtml('[![Clickable](https://example.com/img.jpg)](https://example.com/page)');

    // The image should not have the lightbox trigger class on its parent
    expect($html)->not->toContain('blogr-lightbox-trigger');

    // The image should be directly inside an anchor pointing to the link URL
    expect($html)->toMatch('/<a href="https:\\/\\/example\\.com\\/page">\\s*<img src="https:\\/\\/example\\.com\\/img\\.jpg" alt="Clickable" \\/>\\s*<\\/a>/');
});

test('markdown without images is not affected', function () {
    $html = MarkdownHelper::toHtml('# Just a heading');

    expect($html)->toContain('<h1>Just a heading</h1>')
        ->and($html)->not->toContain('blogr-lightbox-trigger');
});

test('multiple images in markdown all get lightbox wrappers', function () {
    $md = <<<'MD'
First image: ![One](https://example.com/1.jpg)

Second image: ![Two](https://example.com/2.jpg)
MD;

    $html = MarkdownHelper::toHtml($md);

    expect($html)
        ->toContain('<a href="https://example.com/1.jpg" class="blogr-lightbox-trigger"')
        ->and($html)->toContain('<a href="https://example.com/2.jpg" class="blogr-lightbox-trigger"');
});
