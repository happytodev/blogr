<?php

use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

test('renders social links from config by default', function () {
    config(['blogr.ui.footer.social_links' => [
        'twitter' => 'https://twitter.com/test',
        'github' => 'https://github.com/test',
    ]]);

    $html = view('blogr::components.social-links')->render();

    expect($html)
        ->toContain('https://twitter.com/test')
        ->toContain('https://github.com/test');
});

test('renders custom links when provided', function () {
    $customLinks = [
        'twitter' => 'https://twitter.com/custom',
        'instagram' => 'https://instagram.com/custom',
    ];

    $html = view('blogr::components.social-links', ['links' => $customLinks])->render();

    expect($html)
        ->toContain('https://twitter.com/custom')
        ->toContain('https://instagram.com/custom');
});

test('custom links override config', function () {
    config(['blogr.ui.footer.social_links' => [
        'twitter' => 'https://twitter.com/config',
        'github' => 'https://github.com/config',
    ]]);

    $customLinks = [
        'twitter' => 'https://twitter.com/custom',
    ];

    $html = view('blogr::components.social-links', ['links' => $customLinks])->render();

    expect($html)
        ->toContain('https://twitter.com/custom')
        ->not->toContain('https://github.com/config');
});

test('renders nothing when no links configured', function () {
    config(['blogr.ui.footer.social_links' => []]);

    $html = view('blogr::components.social-links')->render();

    expect(trim($html))->toBeEmpty();
});

test('renders all supported platforms', function () {
    $links = [
        'twitter' => 'https://twitter.com/test',
        'github' => 'https://github.com/test',
        'linkedin' => 'https://linkedin.com/in/test',
        'facebook' => 'https://facebook.com/test',
        'bluesky' => 'https://bsky.app/profile/test',
        'youtube' => 'https://youtube.com/@test',
        'instagram' => 'https://instagram.com/test',
        'tiktok' => 'https://tiktok.com/@test',
        'mastodon' => 'https://mastodon.social/@test',
    ];

    $html = view('blogr::components.social-links', ['links' => $links])->render();

    foreach ($links as $url) {
        expect($html)->toContain($url);
    }
});

test('accepts custom size class', function () {
    config(['blogr.ui.footer.social_links' => [
        'twitter' => 'https://twitter.com/test',
    ]]);

    $html = view('blogr::components.social-links', ['size' => 'w-8 h-8'])->render();

    expect($html)->toContain('w-8 h-8');
});

test('defaults to w-5 h-5 size', function () {
    config(['blogr.ui.footer.social_links' => [
        'twitter' => 'https://twitter.com/test',
    ]]);

    $html = view('blogr::components.social-links')->render();

    expect($html)->toContain('w-5 h-5');
});
