<?php

use Happytodev\Blogr\Extensions\VideoEmbedAdapter;
use Happytodev\Blogr\Helpers\MarkdownHelper;
use League\CommonMark\Extension\Embed\Embed;

// ─── VideoEmbedAdapter unit tests ────────────────────────────────────────────

it('generates a YouTube embed from a standard watch URL', function () {
    $adapter = new VideoEmbedAdapter;
    $embed = new Embed('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

    $adapter->updateEmbeds([$embed]);

    expect($embed->getEmbedCode())
        ->toContain('youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->toContain('<iframe')
        ->toContain('allowfullscreen')
        ->toContain('aspect-video');
});

it('generates a YouTube embed from a short youtu.be URL', function () {
    $adapter = new VideoEmbedAdapter;
    $embed = new Embed('https://youtu.be/dQw4w9WgXcQ');

    $adapter->updateEmbeds([$embed]);

    expect($embed->getEmbedCode())
        ->toContain('youtube-nocookie.com/embed/dQw4w9WgXcQ');
});

it('generates a Vimeo embed', function () {
    $adapter = new VideoEmbedAdapter;
    $embed = new Embed('https://vimeo.com/123456789');

    $adapter->updateEmbeds([$embed]);

    expect($embed->getEmbedCode())
        ->toContain('player.vimeo.com/video/123456789')
        ->toContain('<iframe')
        ->toContain('allowfullscreen');
});

it('generates a Dailymotion embed', function () {
    $adapter = new VideoEmbedAdapter;
    $embed = new Embed('https://www.dailymotion.com/video/x7tgd2g');

    $adapter->updateEmbeds([$embed]);

    expect($embed->getEmbedCode())
        ->toContain('dailymotion.com/embed/video/x7tgd2g')
        ->toContain('<iframe');
});

it('does not set embed code for non-video URLs', function () {
    $adapter = new VideoEmbedAdapter;
    $embed = new Embed('https://example.com/some-page');

    $adapter->updateEmbeds([$embed]);

    expect($embed->getEmbedCode())->toBeNull();
});

// ─── MarkdownHelper integration ──────────────────────────────────────────────

it('converts a standalone YouTube URL in markdown to an iframe embed', function () {
    $markdown = "Some text before\n\nhttps://www.youtube.com/watch?v=dQw4w9WgXcQ\n\nSome text after";

    $html = MarkdownHelper::toHtml($markdown);

    expect($html)
        ->toContain('youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->toContain('<iframe')
        ->toContain('aspect-video');
});

it('converts a standalone Vimeo URL in markdown to an iframe embed', function () {
    $markdown = 'https://vimeo.com/123456789';

    $html = MarkdownHelper::toHtml($markdown);

    expect($html)
        ->toContain('player.vimeo.com/video/123456789')
        ->toContain('<iframe');
});

it('does not convert a YouTube URL that is inline in a paragraph', function () {
    // URL is not alone on its own line – should remain as text/link, not embed
    $markdown = 'Check out this video: https://www.youtube.com/watch?v=dQw4w9WgXcQ and more text.';

    $html = MarkdownHelper::toHtml($markdown);

    expect($html)->not->toContain('<iframe');
});
