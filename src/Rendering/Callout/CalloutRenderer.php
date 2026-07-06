<?php

namespace Happytodev\Blogr\Rendering\Callout;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

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
            CalloutType::Tip => '<svg class="docs-callout__icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>',
            CalloutType::Info => '<svg class="docs-callout__icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>',
            CalloutType::Caution => '<svg class="docs-callout__icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>',
            CalloutType::Danger => '<svg class="docs-callout__icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 9l6 6m0-6l-6 6M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>',
        };

        $titleHtml = $title
            ? '<p class="docs-callout__title">'.$icon.' '.htmlspecialchars($title).'</p>'
            : '<p class="docs-callout__title docs-callout__title--icon-only">'.$icon.'</p>';

        $classes = 'docs-callout docs-callout--'.$type->value;

        return '<aside class="'.$classes.'" aria-label="'.htmlspecialchars($title ?? $type->label()).'">'
            .$titleHtml
            .'<div class="docs-callout__content">'.$innerHtml.'</div>'
            .'</aside>';
    }
}
