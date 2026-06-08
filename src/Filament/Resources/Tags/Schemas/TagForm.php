<?php

namespace Happytodev\Blogr\Filament\Resources\Tags\Schemas;

use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Set;
use Happytodev\Blogr\Models\Tag;

class TagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        if ($state) {
                            $set('slug', Str::slug($state));
                        }
                    }),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Placeholder::make('rss_feed_url')
                    ->label('RSS Feed URL')
                    ->content(function (?Tag $record) {
                        if (!$record || !$record->exists) {
                            return 'Save the tag first to view its RSS feed URL.';
                        }
                        $localesEnabled = config('blogr.locales.enabled', false);
                        $locale = config('blogr.locales.default', 'en');
                        $url = $localesEnabled
                            ? route('blog.feed.tag', ['locale' => $locale, 'tagSlug' => $record->slug])
                            : route('blog.feed.tag', ['tagSlug' => $record->slug]);
                        return new \Illuminate\Support\HtmlString(
                            '<div class="flex items-center gap-2">'
                            . '<code class="text-sm px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded break-all">' . e($url) . '</code>'
                            . '<button type="button" onclick="navigator.clipboard.writeText(\'' . e($url) . '\').then(() => { this.innerHTML = \'✓\'; setTimeout(() => { this.innerHTML = \'' . e('📋') . '\'; }, 1500); }).catch(() => {})" '
                            . 'class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" title="Copy URL">📋</button>'
                            . '<a href="' . e($url) . '" target="_blank" class="p-1.5 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" title="Open feed">↗</a>'
                            . '</div>'
                        );
                    })
                    ->columnSpanFull(),
            ]);
    }
}
