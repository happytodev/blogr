<?php

namespace Happytodev\Blogr\Tests\Database\Factories;

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Happytodev\Blogr\Models\BlogPost>
 */
class BlogPostFactory extends Factory
{
    protected $model = BlogPost::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();
        
        return [
            'photo' => null,
            'user_id' => 1, // Default user ID, will be overridden in tests
            'is_published' => false,
            'published_at' => null,
            'category_id' => Category::factory(),
            'default_locale' => 'en',
            'display_toc' => false,
            // Translatable fields (will be auto-moved to translations by model hook)
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::random(6), // Add random suffix for global uniqueness
            'content' => fake()->paragraphs(3, true),
            'tldr' => fake()->sentence(),
            'meta_title' => $title,
            'meta_description' => fake()->sentence(),
            'meta_keywords' => implode(', ', fake()->words(3)),
        ];
    }

    /**
     * Indicate that the blog post should be published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => now(),
        ]);
    }
}
