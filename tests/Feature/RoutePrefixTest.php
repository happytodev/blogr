<?php

test("blog routes work with empty prefix (homepage)", function () {
    // Test with empty prefix and locales disabled
    config(["blogr.route.prefix" => ""]);
    config(["blogr.locales.enabled" => false]);

    // The blog should be accessible at the root domain
    $response = $this->get("/");

    // Should not redirect (since locales are disabled)
    $response->assertStatus(200);
});

test("blog routes redirect with empty prefix and locales enabled", function () {
    // Test with empty prefix and locales enabled
    config(["blogr.route.prefix" => ""]);
    config(["blogr.locales.enabled" => true]);
    config(["blogr.locales.default" => "en"]);

    // Should redirect root to /en
    $response = $this->get("/");
    $response->assertRedirect("/en");
});

test("blog routes work with prefix", function () {
    // Test with prefix
    config(["blogr.route.prefix" => "blog"]);
    config(["blogr.locales.enabled" => false]);

    // Should work with /blog prefix
    $response = $this->get("/blog");
    $response->assertStatus(200);
});
