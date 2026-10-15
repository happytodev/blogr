<?php

namespace Happytodev\Blogr\Tests\Database\Factories;

use Happytodev\Blogr\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // unique(): fake()->word() can repeat the same word, and a test that
        // asks for several tags at once then hit the
        // uniqueness constraint on tags.slug (observed: 'Sed' / 'sed' inserted twice).
        $name = fake()->unique()->word();

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
        ];
    }
}
