<?php

use Happytodev\Blogr\Tests\CmsTestCase;

uses(\Happytodev\Blogr\Tests\CmsTestCase::class);

function viewPath(string $relative): string
{
    return dirname(__DIR__, 3).'/resources/views/'.ltrim($relative, '/');
}

// ─── BLOG LAYOUT STACKS ────────────────────────────────

it('contains cookie-consent stack in blog layout source', function () {
    $source = file_get_contents(viewPath('layouts/blog.blade.php'));

    expect($source)->toContain("@stack('cookie-consent')");
});

it('contains analytics stacks in blog layout source', function () {
    $source = file_get_contents(viewPath('layouts/blog.blade.php'));

    expect($source)->toContain("@stack('analytics-before')");
    expect($source)->toContain("@stack('analytics-after')");
});

it('contains body-end and scripts stacks in blog layout source', function () {
    $source = file_get_contents(viewPath('layouts/blog.blade.php'));

    expect($source)->toContain("@stack('body-end')");
    expect($source)->toContain("@stack('scripts')");
});

// ─── ANALYTICS TRACKER STACK ───────────────────────────

it('contains analytics-consent stack in analytics tracker source', function () {
    $source = file_get_contents(viewPath('components/analytics-tracker.blade.php'));

    expect($source)->toContain("@stack('analytics-consent')");
});

// ─── CONTACT FORM STACK ────────────────────────────────

it('contains contact-form-consent stack in contact form source', function () {
    $source = file_get_contents(viewPath('components/blocks/contact_form.blade.php'));

    expect($source)->toContain("@stack('contact-form-consent')");
});

// ─── FOOTER STACK ──────────────────────────────────────

it('contains footer-links stack in footer source', function () {
    $source = file_get_contents(viewPath('components/footer.blade.php'));

    expect($source)->toContain("@stack('footer-links')");
});
