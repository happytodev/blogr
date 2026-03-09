<?php

namespace Happytodev\Blogr\Extensions;

use League\CommonMark\Extension\Embed\Embed;
use League\CommonMark\Extension\Embed\EmbedAdapterInterface;

/**
 * Transforms standalone video URLs into responsive embed iframes.
 * Supports YouTube, Vimeo and Dailymotion without external dependencies.
 */
class VideoEmbedAdapter implements EmbedAdapterInterface
{
    public function updateEmbeds(array $embeds): void
    {
        foreach ($embeds as $embed) {
            $code = $this->resolveEmbedCode($embed->getUrl());
            if ($code !== null) {
                $embed->setEmbedCode($code);
            }
        }
    }

    private function resolveEmbedCode(string $url): ?string
    {
        // YouTube: youtube.com/watch?v=ID or youtu.be/ID
        if (preg_match(
            '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
            $url,
            $matches
        )) {
            return $this->buildIframe(
                'https://www.youtube-nocookie.com/embed/' . $matches[1],
                'allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen'
            );
        }

        // Vimeo: vimeo.com/ID
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            return $this->buildIframe(
                'https://player.vimeo.com/video/' . $matches[1],
                'allow="autoplay; fullscreen; picture-in-picture" allowfullscreen'
            );
        }

        // Dailymotion: dailymotion.com/video/ID
        if (preg_match('/dailymotion\.com\/video\/([a-zA-Z0-9]+)/', $url, $matches)) {
            return $this->buildIframe(
                'https://www.dailymotion.com/embed/video/' . $matches[1],
                'allow="autoplay; fullscreen; picture-in-picture" allowfullscreen'
            );
        }

        return null;
    }

    private function buildIframe(string $src, string $extraAttributes): string
    {
        return sprintf(
            '<div class="video-embed aspect-video w-full rounded-xl overflow-hidden shadow-lg my-8">'
            . '<iframe src="%s" class="w-full h-full" frameborder="0" %s></iframe>'
            . '</div>',
            htmlspecialchars($src, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $extraAttributes
        );
    }
}
