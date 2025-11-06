<?php

namespace Happytodev\Blogr\Database\Factories;

use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Enums\CmsPageTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class CmsPageFactory extends Factory
{
    protected $model = CmsPage::class;

    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug(2),
            'template' => CmsPageTemplate::DEFAULT->value,
            'is_published' => false,
            'published_at' => null,
            'default_locale' => 'en',
        ];
    }

    /**
     * State: Published page
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);
    }

    /**
     * State: Unpublished page
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    /**
     * State: Scheduled for future publication
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => now()->addDay(),
        ]);
    }

    /**
     * State: Homepage page
     */
    public function homepage(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_homepage' => true,
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    /**
     * State: Landing page template
     * Note: Blocks are now stored in translations, not in the page itself
     */
    public function landing(): static
    {
        return $this->state(fn (array $attributes) => [
            'template' => CmsPageTemplate::LANDING->value,
            // Provide sensible default blocks for landing pages in tests/factories
            'blocks' => [
                [
                    'type' => 'hero',
                    'data' => [
                        'title' => 'Welcome',
                        'subtitle' => 'To our website',
                    ],
                ],
                [
                    'type' => 'features',
                    'data' => [
                        'items' => [
                            ['name' => 'Feature 1', 'description' => 'Description 1'],
                            ['name' => 'Feature 2', 'description' => 'Description 2'],
                        ],
                    ],
                ],
            ],
        ]);
    }

    /**
     * State: Contact page template
     */
    public function contact(): static
    {
        return $this->state(fn (array $attributes) => [
            'template' => CmsPageTemplate::CONTACT->value,
        ]);
    }

    /**
     * State: Custom template
     * Note: Blocks are now stored in translations, not in the page itself
     */
    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'template' => CmsPageTemplate::CUSTOM->value,
        ]);
    }

    /**
     * State: With specific template
     */
    public function withTemplate(CmsPageTemplate $template): static
    {
        return $this->state(fn (array $attributes) => [
            'template' => $template->value,
        ]);
    }

    /**
     * State: With specific slug
     */
    public function withSlug(string $slug): static
    {
        return $this->state(fn (array $attributes) => [
            'slug' => $slug,
        ]);
    }
}
