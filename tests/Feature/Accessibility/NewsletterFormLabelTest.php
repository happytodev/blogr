<?php

use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Support\Facades\View;

uses(TestCase::class);

test('feature_newsletter_form_has_visible_label', function () {
    $data = [
        'heading' => 'Stay Updated',
        'description' => 'Get the latest news',
        'placeholder' => 'your@email.com',
        'button_text' => 'Subscribe',
    ];

    $html = View::make('blogr::components.blocks.newsletter', ['data' => $data])->render();

    expect($html)
        ->toContain('<label')
        ->toContain('for="')
        ->toContain('Email address')
        ->toContain('id="newsletter-email"');
});

test('feature_newsletter_form_label_is_visually_hidden', function () {
    $data = [
        'heading' => 'Stay Updated',
        'description' => 'Get the latest news',
    ];

    $html = View::make('blogr::components.blocks.newsletter', ['data' => $data])->render();

    // Label should be visually hidden (sr-only) but present for screen readers
    expect($html)
        ->toContain('sr-only');
});

test('feature_newsletter_form_input_has_correct_id', function () {
    $data = [
        'heading' => 'Stay Updated',
    ];

    $html = View::make('blogr::components.blocks.newsletter', ['data' => $data])->render();

    expect($html)
        ->toContain('id="newsletter-email"')
        ->toContain('name="email"')
        ->toContain('type="email"')
        ->toContain('required');
});
