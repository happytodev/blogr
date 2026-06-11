<?php

namespace Happytodev\Blogr\Services\Translation;

use Illuminate\Support\Facades\Http;

class LibreTranslateProvider implements TranslationProvider
{
    public function isAvailable(): bool
    {
        $url = config('blogr.translation.libretranslate.url', 'http://localhost:5000');

        if (empty($url)) {
            return false;
        }

        try {
            $response = Http::timeout(3)->get("{$url}/languages");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function translate(string $text, string $sourceLocale, string $targetLocale): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        $url = config('blogr.translation.libretranslate.url', 'http://localhost:5000');

        $response = Http::timeout(30)->post("{$url}/translate", [
            'q' => $text,
            'source' => $this->mapLocale($sourceLocale),
            'target' => $this->mapLocale($targetLocale),
            'format' => 'text',
        ]);

        return $response->json('translatedText', $text);
    }

    public function translateBlocks(array $blocks, string $sourceLocale, string $targetLocale): array
    {
        return (new BlockTranslator($this))->translateBlocks($blocks, $sourceLocale, $targetLocale);
    }

    private function mapLocale(string $locale): string
    {
        return match ($locale) {
            'en' => 'en',
            'fr' => 'fr',
            'de' => 'de',
            'es' => 'es',
            'it' => 'it',
            'pt' => 'pt',
            'pl' => 'pl',
            'ru' => 'ru',
            'nl' => 'nl',
            'el' => 'el',
            default => $locale,
        };
    }
}
