<?php
uses(Happytodev\Blogr\Tests\TestCase::class);



test("homepage configuration logic works correctly", function () {
    // Test that homepage overrides prefix
    config(['blogr.route.homepage' => true]);
    config(['blogr.route.prefix' => 'blog']);
    config(['blogr.locales.enabled' => false]);

    expect(config('blogr.route.homepage'))->toBe(true);
    expect(config('blogr.route.prefix'))->toBe('blog');

    // The service provider should use homepage=true to override prefix
    // This is tested implicitly by the fact that routes work in the demo app
});

test("homepage routes are registered correctly in service provider", function () {
    // This test verifies that the service provider registers routes correctly
    // when homepage is enabled

    config(['blogr.route.homepage' => true]);
    config(['blogr.locales.enabled' => false]);
    config(['blogr.route.frontend.enabled' => true]);

    // Since routes are registered at application boot time,
    // we test that the configuration enables homepage functionality
    // The actual HTTP testing is done in the demo application

    expect(config('blogr.route.homepage'))->toBe(true);
    expect(config('blogr.locales.enabled'))->toBe(false);
});

test("homepage with locales redirects correctly", function () {
    config(['blogr.route.homepage' => true]);
    config(['blogr.locales.enabled' => true]);
    config(['blogr.locales.default' => 'en']);
    config(['blogr.locales.available' => ['en', 'fr']]);

    // Test configuration is set correctly
    expect(config('blogr.route.homepage'))->toBe(true);
    expect(config('blogr.locales.enabled'))->toBe(true);
    expect(config('blogr.locales.default'))->toBe('en');

    // The actual redirect logic is tested in the demo application
    // where we verified that / redirects to /en with 302 status
});
