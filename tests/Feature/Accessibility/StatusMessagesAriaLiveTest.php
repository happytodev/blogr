<?php

use Happytodev\Blogr\Tests\TestCase;
use Illuminate\Support\Facades\View;

uses(TestCase::class);

test('feature_contact_form_status_message_has_role_status', function () {
    $data = ['heading' => 'Contact', 'to_email' => 'test@example.com'];
    $html = View::make('blogr::components.blocks.contact_form', ['data' => $data])->render();
    expect($html)->toContain('role="status"');
});

test('feature_newsletter_status_message_has_role_status', function () {
    $data = ['heading' => 'Newsletter'];
    $html = View::make('blogr::components.blocks.newsletter', ['data' => $data])->render();
    expect($html)->toContain('role="status"');
});
