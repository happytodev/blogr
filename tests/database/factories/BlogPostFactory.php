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
            'title' => $title,
            'photo' => null,
            'content' => fake()->paragraphs(3, true),
            'slug' => Str::slug($title),
            'user_id' => 1, // Default user ID, will be overridden in tests
            'is_published' => false,
            'published_at' => null,
            'meta_title' => $title,
            'meta_description' => fake()->sentence(),
            'meta_keywords' => implode(', ', fake()->words(3)),
            'tldr' => fake()->sentence(),
            'category_id' => Category::factory(),
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
