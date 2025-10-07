<?php

namespace Happytodev\Blogr\Filament\Resources\BlogSeries;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

class BlogSeriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Slug copied!')
                    ->weight('bold'),
                
                TextColumn::make('translations.title')
                    ->label('Title')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (is_string($state) && strlen($state) > 50) {
                            return $state;
                        }
                        return null;
                    }),
                
                TextColumn::make('position')
                    ->label('Order')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                
                TextColumn::make('posts_count')
                    ->counts('posts')
                    ->label('Posts')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-o-document-text'),
                
                TextColumn::make('translations_count')
                    ->counts('translations')
                    ->label('Translations')
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-language'),
                
                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->placeholder('Draft')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'warning'),
                
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_featured')
                    ->label('Featured Only')
                    ->placeholder('All series')
                    ->trueLabel('Featured only')
                    ->falseLabel('Not featured'),
                
                Filter::make('published')
                    ->label('Published Only')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('published_at')),
                
                Filter::make('draft')
                    ->label('Drafts Only')
                    ->query(fn (Builder $query): Builder => $query->whereNull('published_at')),
            ])
            ->actions([
                EditAction::make()
                    ->icon('heroicon-o-pencil'),
                DeleteAction::make()
                    ->icon('heroicon-o-trash'),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('position', 'asc')
            ->emptyStateHeading('No series yet')
            ->emptyStateDescription('Create your first blog series to organize related posts.')
            ->emptyStateIcon('heroicon-o-queue-list');
    }
}
