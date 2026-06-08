<?php

namespace Happytodev\Blogr\Helpers;

use Happytodev\Blogr\Extensions\VideoEmbedAdapter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Embed\EmbedExtension;
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
