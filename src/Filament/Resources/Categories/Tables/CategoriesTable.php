<?php

namespace Happytodev\Blogr\Filament\Resources\Categories\Tables;

use Filament\Tables\Table;

use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;

use Happytodev\Blogr\Models\Category;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\ForceDeleteBulkAction;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_default')
                    ->boolean(),
                TextColumn::make('posts_count')
                    ->counts('posts')
                    ->sortable()
                    ->label('Post Count'),
            ])
            ->filters([
                // TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                    ->disabled(function () {
                        return Category::where('is_default', true)->exists();
                    }),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
