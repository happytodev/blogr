<?php

use Happytodev\Blogr\Services\Translation\BlockTranslator;
use Happytodev\Blogr\Services\Translation\LibreTranslateProvider;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('blogr.translation.libretranslate.url', 'http://localhost:5000');

    $this->provider = new LibreTranslateProvider;
    $this->translator = new BlockTranslator($this->provider);
});

it('translates team block heading and description', function () {
    Http::fake([
        '*/translate' => Http::sequence()
            ->push(['translatedText' => 'Translated heading'])
            ->push(['translatedText' => 'Translated description'])
            ->push(['translatedText' => 'Translated name'])
            ->push(['translatedText' => 'Translated role'])
            ->push(['translatedText' => 'Translated bio']),
        '*/languages' => Http::response([]),
    ]);

    $block = [
        'type' => 'team',
        'data' => [
            'heading' => 'Our Team',
            'description' => 'Meet our amazing team',
            'members' => [
                ['name' => 'John', 'role' => 'CEO', 'bio' => 'Founder'],
            ],
        ],
    ];

    $result = $this->translator->translateBlocks([$block], 'en', 'fr');

    expect($result[0]['data']['heading'])->toBe('Translated heading')
        ->and($result[0]['data']['description'])->toBe('Translated description')
        ->and($result[0]['data']['members'][0]['name'])->toBe('Translated name')
        ->and($result[0]['data']['members'][0]['role'])->toBe('Translated role')
        ->and($result[0]['data']['members'][0]['bio'])->toBe('Translated bio');
});

it('translates blog_posts block heading', function () {
    Http::fake([
        '*/translate' => Http::response(['translatedText' => 'Translated heading']),
        '*/languages' => Http::response([]),
    ]);

    $block = [
        'type' => 'blog_posts',
        'data' => [
            'heading' => 'Latest Writing',
            'limit' => 6,
        ],
    ];

    $result = $this->translator->translateBlocks([$block], 'en', 'fr');

    expect($result[0]['data']['heading'])->toBe('Translated heading');
});

it('translates video block heading', function () {
    Http::fake([
        '*/translate' => Http::response(['translatedText' => 'Translated heading']),
        '*/languages' => Http::response([]),
    ]);

    $block = [
        'type' => 'video',
        'data' => ['heading' => 'Our Video'],
    ];

    $result = $this->translator->translateBlocks([$block], 'en', 'fr');

    expect($result[0]['data']['heading'])->toBe('Translated heading');
});

it('translates blog-title block', function () {
    Http::fake([
        '*/translate' => Http::sequence()
            ->push(['translatedText' => 'Titre traduit'])
            ->push(['translatedText' => 'Description traduite']),
        '*/languages' => Http::response([]),
    ]);

    $block = [
        'type' => 'blog-title',
        'data' => [
            'title' => 'Blog Title',
            'description' => 'Blog Description',
        ],
    ];

    $result = $this->translator->translateBlocks([$block], 'en', 'fr');

    expect($result[0]['data']['title'])->toBe('Titre traduit')
        ->and($result[0]['data']['description'])->toBe('Description traduite');
});

it('translates map block', function () {
    Http::fake([
        '*/translate' => Http::sequence()
            ->push(['translatedText' => 'En-tête traduit'])
            ->push(['translatedText' => 'Sous-titre traduit'])
            ->push(['translatedText' => 'Tagline traduite']),
        '*/languages' => Http::response([]),
    ]);

    $block = [
        'type' => 'map',
        'data' => [
            'heading' => 'Our Location',
            'subtitle' => 'Find us',
            'tagline' => 'We are here',
        ],
    ];

    $result = $this->translator->translateBlocks([$block], 'en', 'fr');

    expect($result[0]['data']['heading'])->toBe('En-tête traduit')
        ->and($result[0]['data']['subtitle'])->toBe('Sous-titre traduit')
        ->and($result[0]['data']['tagline'])->toBe('Tagline traduite');
});

it('translates contact_form block', function () {
    Http::fake([
        '*/translate' => Http::sequence()
            ->push(['translatedText' => 'En-tête'])
            ->push(['translatedText' => 'Sous-titre'])
            ->push(['translatedText' => 'Envoyer'])
            ->push(['translatedText' => 'Merci']),
        '*/languages' => Http::response([]),
    ]);

    $block = [
        'type' => 'contact_form',
        'data' => [
            'heading' => 'Contact Us',
            'subtitle' => 'Get in touch',
            'submit_text' => 'Send',
            'success_message' => 'Thank you',
        ],
    ];

    $result = $this->translator->translateBlocks([$block], 'en', 'fr');

    expect($result[0]['data']['heading'])->toBe('En-tête')
        ->and($result[0]['data']['subtitle'])->toBe('Sous-titre')
        ->and($result[0]['data']['submit_text'])->toBe('Envoyer')
        ->and($result[0]['data']['success_message'])->toBe('Merci');
});

it('translates pricing block with deep nested features', function () {
    Http::fake([
        '*/translate' => Http::sequence()
            ->push(['translatedText' => 'En-tête'])
            ->push(['translatedText' => 'Description'])
            ->push(['translatedText' => 'Plan'])
            ->push(['translatedText' => 'Plan desc'])
            ->push(['translatedText' => 'Acheter'])
            ->push(['translatedText' => 'Fonction']),
        '*/languages' => Http::response([]),
    ]);

    $block = [
        'type' => 'pricing',
        'data' => [
            'heading' => 'Pricing',
            'description' => 'Choose your plan',
            'plans' => [
                [
                    'name' => 'Basic',
                    'description' => 'Basic plan',
                    'cta_text' => 'Buy now',
                    'features' => [
                        ['feature' => 'Feature 1'],
                    ],
                ],
            ],
        ],
    ];

    $result = $this->translator->translateBlocks([$block], 'en', 'fr');

    expect($result[0]['data']['heading'])->toBe('En-tête')
        ->and($result[0]['data']['description'])->toBe('Description')
        ->and($result[0]['data']['plans'][0]['name'])->toBe('Plan')
        ->and($result[0]['data']['plans'][0]['description'])->toBe('Plan desc')
        ->and($result[0]['data']['plans'][0]['cta_text'])->toBe('Acheter')
        ->and($result[0]['data']['plans'][0]['features'][0]['feature'])->toBe('Fonction');
});

it('translates newsletter block correctly', function () {
    Http::fake([
        '*/translate' => Http::sequence()
            ->push(['translatedText' => 'Titre'])
            ->push(['translatedText' => 'Description'])
            ->push(['translatedText' => 'Placeholder'])
            ->push(['translatedText' => 'Bouton']),
        '*/languages' => Http::response([]),
    ]);

    $block = [
        'type' => 'newsletter',
        'data' => [
            'heading' => 'Newsletter',
            'description' => 'Subscribe',
            'placeholder' => 'Your email',
            'button_text' => 'Subscribe',
        ],
    ];

    $result = $this->translator->translateBlocks([$block], 'en', 'fr');

    expect($result[0]['data']['heading'])->toBe('Titre')
        ->and($result[0]['data']['description'])->toBe('Description')
        ->and($result[0]['data']['placeholder'])->toBe('Placeholder')
        ->and($result[0]['data']['button_text'])->toBe('Bouton');
});
