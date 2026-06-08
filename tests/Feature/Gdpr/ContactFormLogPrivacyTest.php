<?php

use Happytodev\Blogr\Tests\CmsTestCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

uses(CmsTestCase::class);

it('does not log personal data on contact form failure', function () {
    Mail::shouldReceive('raw')
        ->once()
        ->andThrow(new Exception('Simulated SMTP error'));

    Log::shouldReceive('error')
        ->once()
        ->withArgs(function ($message, $context) {
            // The log message should be generic, no personal data
            expect($message)->toBe('Blogr contact form failed');

            // The context should NOT contain personal data
            $loggable = json_encode($context);
            expect($loggable)->not->toContain('john@example.com');
            expect($loggable)->not->toContain('John Doe');
            expect($loggable)->not->toContain('Test Subject');
            expect($loggable)->not->toContain('Test message');

            return true;
        });

    $this->postJson(route('blogr.cms.contact.submit'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'subject' => 'Test Subject',
        'message' => 'Test message',
    ]);
});

it('does not log mail credentials on contact form failure', function () {
    Mail::shouldReceive('raw')
        ->once()
        ->andThrow(new Exception('Simulated SMTP error'));

    Log::shouldReceive('error')
        ->once()
        ->withArgs(function ($message, $context) {
            $loggable = json_encode($context);

            // The context should NOT contain mail server credentials
            expect($loggable)->not->toContain('mail_host');
            expect($loggable)->not->toContain('MAIL_HOST');
            expect($loggable)->not->toContain('mail_username');
            expect($loggable)->not->toContain('MAIL_USERNAME');
            expect($loggable)->not->toContain('smtp-relay.brevo.com');
            expect($loggable)->not->toContain('to_email');
            expect($loggable)->not->toContain('mail_from');

            return true;
        });

    $this->postJson(route('blogr.cms.contact.submit'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'subject' => 'Issue',
        'message' => 'Help',
    ]);
});

it('returns 500 with generic message on contact form failure', function () {
    Mail::shouldReceive('raw')
        ->once()
        ->andThrow(new Exception('Something went wrong'));

    $response = $this->postJson(route('blogr.cms.contact.submit'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'subject' => 'Test',
        'message' => 'Test message body.',
    ]);

    $response->assertStatus(500);
    $response->assertJson([
        'success' => false,
    ]);
    // The error message should be generic, not exposing the internal error
    $response->assertJsonMissing(['message' => 'Something went wrong']);
});
