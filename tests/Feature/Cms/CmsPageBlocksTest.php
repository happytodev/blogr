<?php

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Happytodev\Blogr\Enums\CmsPageTemplate;
use Happytodev\Blogr\Tests\CmsTestCase;

uses(CmsTestCase::class);

test('it can create a page with hero block in translation', function () {
    $page = CmsPage::create([
        'slug' => 'landing-hero',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Landing Hero Page',
        'slug' => 'landing-hero',
        'content' => 'Test content',
        'blocks' => [
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'Welcome to Our Platform',
                    'subtitle' => 'Build amazing experiences',
                    'cta_text' => 'Get Started',
                    'cta_url' => '/signup',
                    'alignment' => 'center',
                ],
            ],
        ],
    ]);

    expect($translation->blocks)->toBeArray();
    expect($translation->blocks)->toHaveCount(1);
    expect($translation->blocks[0]['type'])->toBe('hero');
    expect($translation->blocks[0]['data']['title'])->toBe('Welcome to Our Platform');
});

test('it can create a page with features block in translation', function () {
    $page = CmsPage::create([
        'slug' => 'landing-features',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Landing Features Page',
        'slug' => 'landing-features',
        'content' => 'Test content',
        'blocks' => [
            [
                'type' => 'features',
                'data' => [
                    'title' => 'Our Features',
                    'columns' => '3',
                    'items' => [
                        [
                            'icon' => 'heroicon-o-bolt',
                            'title' => 'Fast',
                            'description' => 'Lightning fast performance',
                        ],
                        [
                            'icon' => 'heroicon-o-shield-check',
                            'title' => 'Secure',
                            'description' => 'Enterprise-grade security',
                        ],
                    ],
                ],
            ],
        ],
    ]);

    expect($translation->blocks[0]['type'])->toBe('features');
    expect($translation->blocks[0]['data']['items'])->toHaveCount(2);
    expect($translation->blocks[0]['data']['columns'])->toBe('3');
});

test('it can create a page with testimonials block in translation', function () {
    $page = CmsPage::create([
        'slug' => 'landing-testimonials',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Landing Testimonials Page',
        'slug' => 'landing-testimonials',
        'content' => 'Test content',
        'blocks' => [
            [
                'type' => 'testimonials',
                'data' => [
                    'title' => 'What Our Clients Say',
                    'items' => [
                        [
                            'name' => 'John Doe',
                            'role' => 'CEO',
                            'quote' => 'This product is amazing!',
                            'rating' => '5',
                        ],
                    ],
                ],
            ],
        ],
    ]);

    expect($translation->blocks[0]['type'])->toBe('testimonials');
    expect($translation->blocks[0]['data']['items'][0]['name'])->toBe('John Doe');
    expect($translation->blocks[0]['data']['items'][0]['rating'])->toBe('5');
});

test('it can create a page with cta block in translation', function () {
    $page = CmsPage::create([
        'slug' => 'landing-cta',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Landing CTA Page',
        'slug' => 'landing-cta',
        'content' => 'Test content',
        'blocks' => [
            [
                'type' => 'cta',
                'data' => [
                    'heading' => 'Ready to get started?',
                    'subheading' => 'Join thousands of users',
                    'button_text' => 'Sign Up Now',
                    'button_url' => '/signup',
                    'button_style' => 'primary',
                ],
            ],
        ],
    ]);

    expect($translation->blocks[0]['type'])->toBe('cta');
    expect($translation->blocks[0]['data']['button_text'])->toBe('Sign Up Now');
});

test('it can create a page with content block in translation', function () {
    $page = CmsPage::create([
        'slug' => 'landing-content',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Landing Content Page',
        'slug' => 'landing-content',
        'content' => 'Test content',
        'blocks' => [
            [
                'type' => 'content',
                'data' => [
                    'content' => '## Rich Content\n\nThis is **markdown** content.',
                    'max_width' => 'prose',
                ],
            ],
        ],
    ]);

    expect($translation->blocks[0]['type'])->toBe('content');
    expect($translation->blocks[0]['data']['content'])->toContain('markdown');
});

test('it can create a page with faq block in translation', function () {
    $page = CmsPage::create([
        'slug' => 'landing-faq',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Landing FAQ Page',
        'slug' => 'landing-faq',
        'content' => 'Test content',
        'blocks' => [
            [
                'type' => 'faq',
                'data' => [
                    'title' => 'Frequently Asked Questions',
                    'items' => [
                        [
                            'question' => 'How does it work?',
                            'answer' => 'It works like magic!',
                        ],
                        [
                            'question' => 'Is it free?',
                            'answer' => 'Yes, we have a free plan.',
                        ],
                    ],
                ],
            ],
        ],
    ]);

    expect($translation->blocks[0]['type'])->toBe('faq');
    expect($translation->blocks[0]['data']['items'])->toHaveCount(2);
});

test('it can create a page with multiple blocks in translation', function () {
    $page = CmsPage::create([
        'slug' => 'full-landing',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Full Landing Page',
        'slug' => 'full-landing',
        'content' => 'Test content',
        'blocks' => [
            [
                'type' => 'hero',
                'data' => ['title' => 'Welcome'],
            ],
            [
                'type' => 'features',
                'data' => ['title' => 'Features', 'items' => []],
            ],
            [
                'type' => 'cta',
                'data' => ['heading' => 'Get Started'],
            ],
        ],
    ]);

    expect($translation->blocks)->toHaveCount(3);
    expect($translation->blocks[0]['type'])->toBe('hero');
    expect($translation->blocks[1]['type'])->toBe('features');
    expect($translation->blocks[2]['type'])->toBe('cta');
});

test('it handles empty blocks gracefully in translation', function () {
    $page = CmsPage::create([
        'slug' => 'no-blocks',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'No Blocks Page',
        'slug' => 'no-blocks',
        'content' => 'Test content',
        'blocks' => null,
    ]);

    expect($translation->blocks)->toBeNull();
});

test('it handles empty blocks array in translation', function () {
    $page = CmsPage::create([
        'slug' => 'empty-blocks',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Empty Blocks Page',
        'slug' => 'empty-blocks',
        'content' => 'Test content',
        'blocks' => [],
    ]);

    expect($translation->blocks)->toBeArray();
    expect($translation->blocks)->toBeEmpty();
});

test('landing page with blocks in translation is accessible', function () {
    $page = CmsPage::create([
        'slug' => 'test-landing',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Test Landing Page',
        'slug' => 'test-landing',
        'content' => 'Test content',
        'blocks' => [
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'Test Hero',
                    'subtitle' => 'Test subtitle',
                ],
            ],
        ],
    ]);

    expect($translation->blocks)->toBeArray();
    expect($translation->title)->toBe('Test Landing Page');
    expect($translation->blocks[0]['type'])->toBe('hero');
});

test('blocks can be stored as array in translation', function () {
    $page = CmsPage::create([
        'slug' => 'render-test',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Render Test Page',
        'slug' => 'render-test',
        'content' => 'Test content',
        'blocks' => [
            [
                'type' => 'hero',
                'data' => [
                    'title' => 'Unique Hero Title For Test',
                    'subtitle' => 'Unique subtitle',
                ],
            ],
        ],
    ]);

    expect($translation->blocks)->toBeArray();
    expect($translation->blocks[0]['data']['title'])->toBe('Unique Hero Title For Test');
});

test('it can update blocks on existing translation', function () {
    $page = CmsPage::create([
        'slug' => 'update-blocks',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Update Blocks Page',
        'slug' => 'update-blocks',
        'content' => 'Test content',
        'blocks' => [
            ['type' => 'hero', 'data' => ['title' => 'Old Title']],
        ],
    ]);

    $translation->update([
        'blocks' => [
            ['type' => 'hero', 'data' => ['title' => 'New Title']],
            ['type' => 'cta', 'data' => ['heading' => 'New CTA']],
        ],
    ]);

    $translation->refresh();
    
    expect($translation->blocks)->toHaveCount(2);
    expect($translation->blocks[0]['data']['title'])->toBe('New Title');
    expect($translation->blocks[1]['type'])->toBe('cta');
});

test('blocks structure is valid in translation', function () {
    $page = CmsPage::create([
        'slug' => 'json-validation',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'JSON Validation Page',
        'slug' => 'json-validation',
        'content' => 'Test content',
        'blocks' => [
            [
                'type' => 'features',
                'data' => [
                    'title' => 'Features',
                    'items' => [
                        ['title' => 'Feature 1', 'description' => 'Desc 1'],
                    ],
                ],
            ],
        ],
    ]);

    expect($translation->blocks)->toBeArray();
    expect($translation->blocks[0])->toHaveKey('type');
    expect($translation->blocks[0])->toHaveKey('data');
    expect($translation->blocks[0]['data'])->toBeArray();
});

test('page with blocks in translation can be deleted', function () {
    $page = CmsPage::create([
        'slug' => 'delete-with-blocks',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Delete With Blocks Page',
        'slug' => 'delete-with-blocks',
        'content' => 'Test content',
        'blocks' => [
            ['type' => 'hero', 'data' => ['title' => 'Test']],
        ],
    ]);

    $pageId = $page->id;
    
    $page->delete();

    expect(CmsPage::find($pageId))->toBeNull();
    // Cascade delete handled by database foreign key constraint
});

test('it can render gallery block', function () {
    $page = CmsPage::create([
        'slug' => 'gallery-page',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Gallery Page',
        'slug' => 'gallery-page',
        'content' => '',
        'blocks' => [
            [
                'type' => 'gallery',
                'data' => [
                    'heading' => 'Our Gallery',
                    'description' => 'Check out our amazing images',
                    'columns' => '3',
                    'images' => [
                        'cms-blocks/gallery/image1.jpg',
                        'cms-blocks/gallery/image2.jpg',
                        'cms-blocks/gallery/image3.jpg',
                    ],
                ],
            ],
        ],
    ]);

    $view = view('blogr::components.blocks.gallery', [
        'data' => $translation->blocks[0]['data']
    ])->render();

    expect($view)->toContain('Our Gallery');
    expect($view)->toContain('Check out our amazing images');
    expect($view)->toContain('grid-cols-1 sm:grid-cols-2 lg:grid-cols-3');
    expect($view)->toContain('x-data');
    expect($view)->toContain('lightboxOpen');
});

test('it can render team block', function () {
    $page = CmsPage::create([
        'slug' => 'team-page',
        'template' => CmsPageTemplate::LANDING,
        'is_published' => true,
        'published_at' => now(),
    ]);

    $translation = $page->translations()->create([
        'locale' => 'en',
        'title' => 'Team Page',
        'slug' => 'team-page',
        'content' => '',
        'blocks' => [
            [
                'type' => 'team',
                'data' => [
                    'heading' => 'Our Team',
                    'description' => 'Meet the amazing people behind our success',
                    'columns' => '3',
                    'members' => [
                        [
                            'name' => 'John Doe',
                            'role' => 'CEO & Founder',
                            'bio' => 'Leading the company with vision',
                            'linkedin' => 'https://linkedin.com/in/johndoe',
                            'email' => 'john@example.com',
                        ],
                        [
                            'name' => 'Jane Smith',
                            'role' => 'CTO',
                            'bio' => 'Building amazing tech',
                            'twitter' => 'https://twitter.com/janesmith',
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $view = view('blogr::components.blocks.team', [
        'data' => $translation->blocks[0]['data']
    ])->render();

    expect($view)->toContain('Our Team');
    expect($view)->toContain('Meet the amazing people');
    expect($view)->toContain('John Doe');
    expect($view)->toContain('CEO');
    expect($view)->toContain('Founder');
    expect($view)->toContain('Jane Smith');
    expect($view)->toContain('CTO');
    expect($view)->toContain('linkedin.com/in/johndoe');
});
