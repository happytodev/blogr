<?php

namespace Happytodev\Blogr\Rendering;

use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Xml\XmlNodeRendererInterface;

class ImageLightboxRenderer implements NodeRendererInterface, XmlNodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (! $node instanceof Image) {
            throw new \InvalidArgumentException('Expected Image node, got '.get_class($node));
        }

        $url = $node->getUrl();
        $alt = $childRenderer->renderNodes($node->children());
        $title = $node->getTitle() ?? '';

        $imgAttrs = ['src' => $url, 'alt' => $alt];
        if ($title !== '') {
            $imgAttrs['title'] = $title;
        }

        $img = new HtmlElement('img', $imgAttrs, null, true);

        $parent = $node->parent();
        if ($parent instanceof Link) {
            return (string) $img;
        }

        $caption = $title !== '' ? $title : $alt;

        return (string) new HtmlElement('a', [
            'href' => $url,
            'class' => 'blogr-lightbox-trigger',
            'data-caption' => $caption,
        ], $img);
    }

    public function getXmlTagName(Node $node): string
    {
        return 'image_lightbox';
    }

    public function getXmlAttributes(Node $node): array
    {
        return [];
    }
}
