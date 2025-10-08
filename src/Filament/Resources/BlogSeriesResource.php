<?php

namespace Happytodev\Blogr\Filament\Resources;

use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Filament\Resources\BlogSeries\BlogSeriesForm;
use Happytodev\Blogr\Filament\Resources\BlogSeries\BlogSeriesTable;
use Happytodev\Blogr\Filament\Resources\BlogSeriesResource\Pages\ListBlogSeries;
use Happytodev\Blogr\Filament\Resources\BlogSeriesResource\Pages\CreateBlogSeries;
use Happytodev\Blogr\Filament\Resources\BlogSeriesResource\Pages\EditBlogSeries;
use BackedEnum;

class BlogSeriesResource extends Resource
{
    protected static ?string $model = BlogSeries::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-queue-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Blogr';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return BlogSeriesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BlogSeriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBlogSeries::route('/'),
            'create' => CreateBlogSeries::route('/create'),
            'edit' => EditBlogSeries::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
