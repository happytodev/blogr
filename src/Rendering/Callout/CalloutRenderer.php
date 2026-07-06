<?php

namespace Happytodev\Blogr\Rendering\Callout;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

class CalloutRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (! $node instanceof CalloutBlock) {
            throw new \InvalidArgumentException('Expected CalloutBlock');
        }

        $type = $node->getType();
        $title = $node->getTitle();

        $innerHtml = $childRenderer->renderNodes($node->children());

        $icon = match ($type) {
            CalloutType::Tip => '<svg class="docs-callout__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>',
            CalloutType::Info => '<svg class="docs-callout__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            CalloutType::Danger => '<svg class="docs-callout__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
            CalloutType::Caution => '<svg class="docs-callout__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        };

        $titleHtml = '';
        if ($title) {
            $titleHtml = '<p class="docs-callout__title">' . $icon . ' ' . htmlspecialchars($title) . '</p>';
        }

        $classes = 'docs-callout docs-callout--' . $type->value;

        return '<aside class="' . $classes . '" aria-label="' . htmlspecialchars($title ?? $type->label()) . '">'
            . $titleHtml
            . '<div class="docs-callout__content">' . $innerHtml . '</div>'
            . '</aside>';
    }
}
