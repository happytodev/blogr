<?php

namespace Happytodev\Blogr\Filament\Resources\BlogPosts;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                FileUpload::make('photo')
                    ->image()
                    ->directory('blog-photos')
                    ->columnSpanFull()
                    ->nullable(),
                MarkdownEditor::make('content')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('slug')
                    ->required()
                    ->unique()
                    ->maxLength(255),
            ]);
    }
}
