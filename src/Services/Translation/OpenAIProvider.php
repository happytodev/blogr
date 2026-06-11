<?php

namespace Happytodev\Blogr\Services\Translation;

use Illuminate\Support\Facades\Http;

class OpenAIProvider implements TranslationProvider
{
    public function isAvailable(): bool
    {
        return ! empty(config('blogr.translation.openai.api_key'));
    }

    public function translate(string $text, string $sourceLocale, string $targetLocale): string
    {
        if (empty(trim($text))) {
            return $text;
        }

        $apiKey = config('blogr.translation.openai.api_key');
        $model = config('blogr.translation.openai.model', 'gpt-4o-mini');

        $response = Http::timeout(60)->withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are a professional translator. Translate the following text from {$sourceLocale} to {$targetLocale}. Return only the translated text, nothing else. Preserve any Markdown formatting.",
                ],
                [
                    'role' => 'user',
                    'content' => $text,
                ],
            ],
            'max_tokens' => 4000,
            'temperature' => 0.3,
        ]);

        return $response->json('choices.0.message.content', $text);
    }

    public function translateBlocks(array $blocks, string $sourceLocale, string $targetLocale): array
    {
        return (new BlockTranslator($this))->translateBlocks($blocks, $sourceLocale, $targetLocale);
    }
}
