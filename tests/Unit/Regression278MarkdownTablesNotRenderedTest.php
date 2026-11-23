<?php

use Happytodev\Blogr\Helpers\MarkdownHelper;

it('renders markdown tables as html', function () {
    $markdown = <<<'MD'
| Champ | Description |
|-------|-------------|
| `version` | Version du package |
| `extensions` | Données des extensions |
MD;

    $html = MarkdownHelper::toHtml($markdown);

    expect($html)
        ->toContain('<table>')
        ->toContain('<th>')
        ->toContain('<td>')
        ->toContain('</tr>')
        ->toContain('</table>')
        ->toContain('Version du package')
        ->toContain('Données des extensions');
});

it('renders table with alignment', function () {
    $markdown = <<<'MD'
| Left | Center | Right |
|:-----|:------:|------:|
| a    | b      | c     |
MD;

    $html = MarkdownHelper::toHtml($markdown);

    expect($html)
        ->toContain('<table>')
        ->toContain('<th')
        ->toContain('<td');
});

it('does not break existing markdown features', function () {
    $markdown = <<<'MD'
# Title

**bold** and *italic*

- list item

> blockquote
MD;

    $html = MarkdownHelper::toHtml($markdown);

    expect($html)
        ->toContain('<h1>')
        ->toContain('<strong>')
        ->toContain('<em>')
        ->toContain('<ul>')
        ->toContain('<blockquote>');
});
