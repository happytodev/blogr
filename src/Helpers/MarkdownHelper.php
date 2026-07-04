<?php

namespace Happytodev\Blogr\Helpers;

use Happytodev\Blogr\Extensions\VideoEmbedAdapter;
use Happytodev\Blogr\Rendering\ShikiCodeBlockRenderer;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\Embed\EmbedExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\MarkdownConverter;

class MarkdownHelper
{
    protected static ?MarkdownConverter $converter = null;

    /**
     * Get or create the markdown converter instance
     */
    protected static function getConverter(): MarkdownConverter
    {
        if (static::$converter === null) {
            $environment = new Environment([
                'html_input' => 'escape',  // Escape HTML to prevent XSS
                'allow_unsafe_links' => false,
                'embed' => [
                    'adapter' => new VideoEmbedAdapter,
                    'allowed_domains' => [],
                    'fallback' => 'link',
                ],
            ]);

            $environment->addExtension(new CommonMarkCoreExtension);
            $environment->addExtension(new EmbedExtension);
            $environment->addExtension(new TableExtension);

            $environment->addRenderer(
                FencedCode::class,
                new ShikiCodeBlockRenderer,
            );
            $environment->addRenderer(
                IndentedCode::class,
                new ShikiCodeBlockRenderer,
            );

            static::$converter = new MarkdownConverter($environment);
        }

        return static::$converter;
    }

    /**
     * Convert markdown to HTML
     */
    public static function toHtml(?string $markdown): string
    {
        if (empty($markdown)) {
            return '';
        }

        return static::getConverter()->convert($markdown)->getContent();
    }
}
