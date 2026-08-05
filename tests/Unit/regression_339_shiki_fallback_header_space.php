<?php

test('shiki-fallback reserves space for the language header bar', function () {
    $blade = file_get_contents(__DIR__.'/../../resources/views/layouts/blog.blade.php');

    // The base fallback block must be positioned (anchor for the absolute header bar).
    expect($blade)->toMatch('/\.shiki-fallback\s*\{[^}]*position:\s*relative[^}]*\}/');

    // When a language label is displayed, top padding must be reserved so the
    // first code lines do not overlap the language/copy bar.
    expect($blade)->toMatch('/\.shiki-fallback\[data-language\]\s*\{[^}]*padding-top:\s*2\.5rem[^}]*\}/');
});

test('copy button is positioned relative to the code block container', function () {
    $blade = file_get_contents(__DIR__.'/../../resources/views/layouts/blog.blade.php');

    expect($blade)->toContain('.copy-button { position: absolute; top: 0.25rem; right: 0.5rem;');
});
