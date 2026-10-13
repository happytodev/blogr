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
        // unique() : fake()->word() peut répéter le même mot, et un test qui
        // demande plusieurs tags d'un coup se heurtait alors à la contrainte
        // d'unicité sur tags.slug (observé : 'Sed' / 'sed' inséré deux fois).
        $name = fake()->unique()->word();

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
        ];
    }
}
