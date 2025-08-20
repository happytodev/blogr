<?php

namespace Happytodev\Blogr\Filament\Resources\Tags\Schemas;

use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;

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
                    // ->reactive()
                    // ->afterStateUpdated(function ($state, callable $set) {
                    //     $set('slug', \Illuminate\Support\Str::slug($state)); // Mise à jour immédiate comme fallback
                    // })
                    // ->extraAlpineAttributes(fn($component) => [
                    //     'x-data' => '{}', // Initialise un scope Alpine
                    //     'x-on:input.debounce.500ms' => '$wire.set(\'data.slug\', $event.target.value.toLowerCase().replace(/[^a-z0-9]+/g, \'-\').replace(/-+/g, \'-\'))',
                    // ]),
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
            ]);
    }
}
