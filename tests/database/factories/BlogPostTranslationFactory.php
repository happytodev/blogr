<?php

namespace Happytodev\Blogr\Tests\Database\Factories;

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Happytodev\Blogr\Models\BlogPostTranslation>
 */
class BlogPostTranslationFactory extends Factory
{
    protected $model = BlogPostTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence();
        $locale = fake()->randomElement(['en', 'fr', 'es', 'de']);

        return [
            'blog_post_id' => BlogPost::factory(),
            'locale' => $locale,
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => fake()->paragraphs(3, true),
            'tldr' => fake()->sentence(),
            'seo_description' => fake()->sentence(),
            'reading_time' => fake()->numberBetween(1, 15),
        ];
    }

    /**
     * Set the locale for the translation.
     */
    public function locale(string $locale): static
    {
        return $this->state(fn (array $attributes) => [
            'locale' => $locale,
        ]);
    }

    /**
     * Set the blog post for the translation.
     */
    public function forBlogPost(BlogPost $blogPost): static
    {
        return $this->state(fn (array $attributes) => [
            'blog_post_id' => $blogPost->id,
        ]);
    }
}
