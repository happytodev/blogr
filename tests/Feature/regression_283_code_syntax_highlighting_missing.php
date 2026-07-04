<?php

use Happytodev\Blogr\Helpers\MarkdownHelper;
use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

test('MarkdownHelper produces Shiki-highlighted code blocks in HTML output', function () {
    $markdown = <<<'MD'
# My Code Example

```php
<?php

function greet(string $name): string
{
    return "Hello, " . $name;
}

echo greet('World');
```
MD;

    $html = MarkdownHelper::toHtml($markdown);

    // Should contain the Shiki code block wrapper
    expect($html)->toContain('<pre class="shiki')
        ->and($html)->toContain('<code>');

    // Should contain the code content
    expect($html)->toContain('greet')
        ->and($html)->toContain('Hello');

    // Should contain syntax-highlighted spans with color styles
    // (Shiki uses inline styles for token coloring)
    expect($html)->toMatch('/<span\s+style="[^"]*color:/');
});

test('MarkdownHelper highlights fenced code blocks with language', function () {
    $markdown = "```javascript\nconst x = 42;\n```";

    $html = MarkdownHelper::toHtml($markdown);

    // Should have shiki class
    expect($html)->toContain('class="shiki');
});

test('MarkdownHelper highlights indented code blocks', function () {
    $markdown = "    echo 'hello';";

    $html = MarkdownHelper::toHtml($markdown);

    // Indented code should also get Shiki highlighting
    expect($html)->toContain('class="shiki');
});

test('Shiki output uses dual themes for dark mode support', function () {
    $markdown = "```php\necho 1;\n```";

    $html = MarkdownHelper::toHtml($markdown);

    // Shiki with dual themes outputs CSS custom properties for dark mode
    expect($html)->toContain('--shiki-dark');
});
