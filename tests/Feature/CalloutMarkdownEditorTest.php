<?php

use Filament\Forms\Components\MarkdownEditor;
use Happytodev\Blogr\Filament\Components\CalloutMarkdownEditor;
use Happytodev\Blogr\Tests\TestCase;

uses(TestCase::class);

test('extends markdown editor', function () {
    $field = CalloutMarkdownEditor::make('content');
    expect($field)->toBeInstanceOf(MarkdownEditor::class);
});

test('uses custom view', function () {
    $field = CalloutMarkdownEditor::make('content');
    expect($field->getView())->toBe('blogr::components.callout-markdown-editor');
});

test('view contains all 4 callout buttons script', function () {
    $path = __DIR__.'/../../resources/views/components/callout-markdown-editor.blade.php';
    expect(file_exists($path))->toBeTrue();
    $content = file_get_contents($path);
    expect($content)
        ->toContain('aria-label')
        ->toContain('replaceSelection')
        ->toContain(':::tip')
        ->toContain(':::info')
        ->toContain(':::caution')
        ->toContain(':::danger')
        ->toContain('callout-btn');
});
