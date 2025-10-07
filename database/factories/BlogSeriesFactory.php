<?php

namespace Happytodev\Blogr\Database\Factories;

use Happytodev\Blogr\Models\BlogSeries;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlogSeriesFactory extends Factory
{
    protected $model = BlogSeries::class;

    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug(3),
            'photo' => null,
            'position' => $this->faker->numberBetween(1, 100),
            'is_featured' => $this->faker->boolean(20),
            'published_at' => $this->faker->boolean(80) ? now() : null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => now()->subDays($this->faker->numberBetween(1, 365)),
        ]);
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => null,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function withPhoto(): static
    {
        return $this->state(fn (array $attributes) => [
            'photo' => 'series-images/' . $this->faker->uuid() . '.jpg',
        ]);
    }
}
