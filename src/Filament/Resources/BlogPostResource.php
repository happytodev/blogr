<?php

namespace Happytodev\Blogr\Filament\Resources;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Forms\Components\FileUpload;
use Forms\Components\MarkdownEditor;
use Happytodev\Blogr\Models\BlogPost;
use Filament\Forms\Components\TextInput;
use Happytodev\Blogr\Filament\Resources\BlogPosts\BlogPostForm;
use Happytodev\Blogr\Filament\Resources\BlogPosts\BlogPostTable;
use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost;
use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\ListBlogPosts;
use Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\CreateBlogPost;

use BackedEnum;
use Filament\Facades\Filament;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Blogr';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return Filament::auth()->user()->hasRole(['admin', 'writer']);
    }

    public static function canCreate(): bool
    {
        return Filament::auth()->user()->hasRole(['admin', 'writer']);
    }

    public static function canEdit($record): bool
    {
        $user = Filament::auth()->user();
        if ($user->hasRole('admin')) {
            return true;
        }
        if ($user->hasRole('writer')) {
            return $record->user_id === $user->id;
        }
        return false;
    }

    public static function canDelete($record): bool
    {
        $user = Filament::auth()->user();
        if ($user->hasRole('admin')) {
            return true;
        }
        if ($user->hasRole('writer')) {
            return $record->user_id === $user->id;
        }
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return BlogPostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BlogPostTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBlogPosts::route('/'),
            'create' => CreateBlogPost::route('/create'),
            'edit' => EditBlogPost::route('/{record}/edit'),
        ];
    }
}