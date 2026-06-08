<?php

use Happytodev\Blogr\Events\ContactFormSubmitted;
use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;

uses(CmsTestCase::class);

it('dispatches ContactFormSubmitted when contact form is submitted', function () {
    Event::fake();
    Mail::fake();

    $this->postJson(route('blogr.cms.contact.submit'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'subject' => 'Test Subject',
        'message' => 'This is a test message.',
    ])->assertOk();

    Event::assertDispatched(ContactFormSubmitted::class);
});

it('ContactFormSubmitted contains submitted data', function () {
    Event::fake();
    Mail::fake();

    $this->postJson(route('blogr.cms.contact.submit'), [
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'subject' => 'Question',
        'message' => 'Hello, I have a question.',
    ])->assertOk();

    Event::assertDispatched(ContactFormSubmitted::class, function ($event) {
        return $event->name === 'Jane Smith'
            && $event->email === 'jane@example.com'
            && $event->subject === 'Question'
            && $event->message === 'Hello, I have a question.';
    });
});

it('contact form submission returns success response', function () {
    Mail::fake();

    $response = $this->postJson(route('blogr.cms.contact.submit'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'subject' => 'Hello',
        'message' => 'Test message body.',
    ]);

    $response->assertOk();
    $response->assertJson(['success' => true]);
});

it('contact form validation fails when fields are missing', function () {
    $response = $this->postJson(route('blogr.cms.contact.submit'), []);

    $response->assertStatus(422);
});

it('contact form validation fails with invalid email', function () {
    $response = $this->postJson(route('blogr.cms.contact.submit'), [
        'name' => 'John',
        'email' => 'not-an-email',
        'subject' => 'Test',
        'message' => 'Message',
    ]);

    $response->assertStatus(422);
});
