<?php

namespace Happytodev\Blogr\Services\Translation;

class BlockTranslator
{
    /** @var array<string, list<string>> */
    protected array $fieldMap = [
        'hero' => ['title', 'subtitle', 'cta_text'],
        'features' => ['title', 'subtitle'],
        'content' => ['content'],
        'testimonials' => ['title'],
        'stats' => ['heading'],
        'faq' => ['title'],
        'cta' => ['heading', 'subheading', 'button_text'],
        'gallery' => ['heading', 'description'],
        'pricing' => ['heading', 'description'],
        'team' => ['heading', 'description'],
        'timeline' => ['heading'],
        'video' => ['heading'],
        'newsletter' => ['heading', 'description', 'placeholder', 'button_text'],
        'blog_posts' => ['heading'],
        'blog-title' => ['title', 'description'],
        'map' => ['heading', 'subtitle', 'tagline'],
        'contact_form' => ['heading', 'subtitle', 'submit_text', 'success_message'],
    ];

    public function __construct(protected TranslationProvider $provider) {}

    public function translateBlocks(array $blocks, string $sourceLocale, string $targetLocale): array
    {
        $result = [];

        foreach ($blocks as $block) {
            $result[] = $this->translateBlock($block, $sourceLocale, $targetLocale);
        }

        return $result;
    }

    protected function translateBlock(array $block, string $sourceLocale, string $targetLocale): array
    {
        $type = $block['type'] ?? '';
        $data = $block['data'] ?? [];
        $fields = $this->fieldMap[$type] ?? [];

        foreach ($fields as $field) {
            if (isset($data[$field]) && is_string($data[$field]) && ! empty(trim($data[$field]))) {
                $data[$field] = $this->translateText($data[$field], $sourceLocale, $targetLocale);
            }
        }

        // Translate nested items (features, testimonials, stats, faq, pricing, team, timeline, gallery)
        $data = $this->translateNestedItems($data, $type, $sourceLocale, $targetLocale);

        $block['data'] = $data;

        return $block;
    }

    protected function translateText(string $text, string $source, string $target): string
    {
        if (! str_contains($text, '`')) {
            return $this->provider->translate($text, $source, $target);
        }

        return (new CodeBlockPreserver)->translateContent($this->provider, $text, $source, $target);
    }

    protected function translateNestedItems(array $data, string $type, string $source, string $target): array
    {
        $nestedMaps = [
            'features' => ['items' => ['title', 'description']],
            'testimonials' => ['items' => ['name', 'role', 'quote']],
            'stats' => ['stats' => ['label']],
            'faq' => ['items' => ['question', 'answer']],
            'pricing' => ['plans' => ['name', 'description', 'cta_text']],
            'team' => ['members' => ['name', 'role', 'bio']],
            'timeline' => ['events' => ['title', 'description']],
        ];

        $map = $nestedMaps[$type] ?? [];

        foreach ($map as $listKey => $itemFields) {
            if (! isset($data[$listKey]) || ! is_array($data[$listKey])) {
                continue;
            }

            foreach ($data[$listKey] as $i => $item) {
                if (! is_array($item)) {
                    continue;
                }

                foreach ($itemFields as $field) {
                    if (isset($item[$field]) && is_string($item[$field]) && ! empty(trim($item[$field]))) {
                        $data[$listKey][$i][$field] = $this->translateText(
                            $item[$field], $source, $target
                        );
                    }
                }
            }
        }

        // Deep nested items (e.g., pricing plans → features)
        $deepNestedMaps = [
            'pricing' => ['plans' => ['features' => ['feature']]],
        ];

        $deepMap = $deepNestedMaps[$type] ?? [];

        foreach ($deepMap as $parentKey => $childConfig) {
            if (! isset($data[$parentKey]) || ! is_array($data[$parentKey])) {
                continue;
            }

            foreach ($childConfig as $childKey => $childFields) {
                foreach ($data[$parentKey] as $i => $parentItem) {
                    if (! isset($parentItem[$childKey]) || ! is_array($parentItem[$childKey])) {
                        continue;
                    }

                    foreach ($parentItem[$childKey] as $j => $childItem) {
                        if (! is_array($childItem)) {
                            continue;
                        }

                        foreach ($childFields as $field) {
                            if (isset($childItem[$field]) && is_string($childItem[$field]) && ! empty(trim($childItem[$field]))) {
                                $data[$parentKey][$i][$childKey][$j][$field] = $this->translateText(
                                    $childItem[$field], $source, $target
                                );
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }
}
