<?php

namespace Happytodev\Blogr\Filament\Resources\Tags;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Happytodev\Blogr\Models\Tag;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Happytodev\Blogr\Filament\Resources\Tags\Pages\EditTag;
use Happytodev\Blogr\Filament\Resources\Tags\Pages\ListTags;
use Happytodev\Blogr\Filament\Resources\Tags\Pages\CreateTag;
use Happytodev\Blogr\Filament\Resources\Tags\Schemas\TagForm;
use Happytodev\Blogr\Filament\Resources\Tags\Tables\TagsTable;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Blogr';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'Tag';

    public static function form(Schema $schema): Schema
    {
        return TagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TagsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TranslationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTags::route('/'),
            'create' => CreateTag::route('/create'),
            'edit' => EditTag::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
