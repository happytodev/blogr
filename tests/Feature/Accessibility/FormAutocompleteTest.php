<?php

use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Support\Facades\View;

uses(TestCase::class);

test('feature_contact_form_name_has_autocomplete', function () {
    $data = [
        'heading' => 'Contact Us',
        'to_email' => 'test@example.com',
    ];

    $html = View::make('blogr::components.blocks.contact_form', ['data' => $data])->render();

    expect($html)
        ->toContain('autocomplete="name"');
});

test('feature_contact_form_email_has_autocomplete', function () {
    $data = [
        'heading' => 'Contact Us',
        'to_email' => 'test@example.com',
    ];

    $html = View::make('blogr::components.blocks.contact_form', ['data' => $data])->render();

    expect($html)
        ->toContain('autocomplete="email"');
});

test('feature_contact_form_subject_has_autocomplete', function () {
    $data = [
        'heading' => 'Contact Us',
        'to_email' => 'test@example.com',
    ];

    $html = View::make('blogr::components.blocks.contact_form', ['data' => $data])->render();

    expect($html)
        ->toContain('autocomplete="subject"');
});
