<?php

namespace Happytodev\Blogr\Filament\Forms;

use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

/**
 * Trait providing a reusable "reload slug" suffix action.
 * Adds a reload icon to a slug input that regenerates the slug
 * from the latest value of a sibling title/name field.
 */
trait SlugFieldTrait
{
    /**
     * Build a suffix action that regenerates a slug field from a sibling title/name field.
     *
     * @param  string  $slugField  Name of the slug field to update
     * @param  string  $titleField  Name of the title/name field to read from
     * @param  string  $name  Action name (must be unique within the form container)
     */
    public static function syncSlugFromTitleAction(
        string $slugField = 'slug',
        string $titleField = 'title',
        string $name = 'syncSlugFromTitle',
    ): Action {
        return Action::make($name)
            ->icon('heroicon-m-arrow-path')
            ->tooltip('Update slug from title')
            ->action(function (Set $set, Get $get) use ($slugField, $titleField): void {
                $title = $get($titleField);

                if (is_string($title) && $title !== '') {
                    $set($slugField, Str::slug($title));
                }
            });
    }
}
