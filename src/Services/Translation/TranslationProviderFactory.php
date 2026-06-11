<?php

namespace Happytodev\Blogr\Services\Translation;

class TranslationProviderFactory
{
    public function make(): TranslationProvider
    {
        $provider = config('blogr.translation.provider', 'none');

        return match ($provider) {
            'libretranslate' => app(LibreTranslateProvider::class),
            'azure' => app(AzureTranslatorProvider::class),
            'google' => app(GoogleTranslateProvider::class),
            'openai' => app(OpenAIProvider::class),
            default => new class implements TranslationProvider
            {
                public function isAvailable(): bool
                {
                    return false;
                }

                public function translate(string $text, string $s, string $t): string
                {
                    return $text;
                }

                public function translateBlocks(array $blocks, string $s, string $t): array
                {
                    return $blocks;
                }
            },
        };
    }

    /** @return array<string, string> */
    public static function availableProviders(): array
    {
        return [
            'none' => 'Disabled',
            'libretranslate' => 'LibreTranslate (self-hosted, free)',
            'openai' => 'OpenAI (GPT-4o-mini)',
        ];
    }
}
