<?php

namespace Happytodev\Blogr\Tests\Database\Factories;

use Happytodev\Blogr\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Happytodev\Blogr\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->word();

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'is_default' => false,
        ];
    }

    /**
     * Indicate that the category should be the default one.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}
