<?php

namespace Happytodev\Blogr\Services\Translation;

class BlockTranslator
{
    /** @var array<string, list<string>> */
    protected array $fieldMap = [
        'hero' => ['title', 'subtitle', 'cta_text'],
        'features' => ['title', 'subtitle', 'cta_text'],
        'content' => ['content'],
        'testimonials' => ['title', 'subtitle'],
        'stats' => ['heading'],
        'faq' => ['title'],
        'cta' => ['heading', 'subheading', 'button_text'],
        'gallery' => ['heading', 'description'],
        'pricing' => ['title', 'subtitle'],
        'team' => ['title'],
        'timeline' => ['heading'],
        'video' => ['title'],
        'newsletter' => ['heading', 'subheading', 'button_text'],
        'blog_posts' => ['title'],
        'blog_title' => ['title'],
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
                $data[$field] = $this->provider->translate($data[$field], $sourceLocale, $targetLocale);
            }
        }

        // Translate nested items (features, testimonials, stats, faq, pricing, team, timeline, gallery)
        $data = $this->translateNestedItems($data, $type, $sourceLocale, $targetLocale);

        $block['data'] = $data;

        return $block;
    }

    protected function translateNestedItems(array $data, string $type, string $source, string $target): array
    {
        $nestedMaps = [
            'features' => ['items' => ['title', 'description']],
            'testimonials' => ['items' => ['name', 'role', 'content']],
            'stats' => ['stats' => ['label']],
            'faq' => ['items' => ['question', 'answer']],
            'pricing' => ['items' => ['name', 'description', 'price_label', 'button_text']],
            'team' => ['items' => ['name', 'role', 'bio']],
            'timeline' => ['events' => ['title', 'description']],
            'gallery' => ['items' => ['caption']],
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
                        $data[$listKey][$i][$field] = $this->provider->translate(
                            $item[$field], $source, $target
                        );
                    }
                }
            }
        }

        return $data;
    }
}
