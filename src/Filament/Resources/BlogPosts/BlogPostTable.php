<?php

namespace Happytodev\Blogr\Filament\Resources\BlogPosts;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Happytodev\Blogr\Models\BlogSeries;

class BlogPostTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function ($query) {
                $user = Filament::auth()->user();
                if ($user->hasRole('writer') && !$user->hasRole('admin')) {
                    $query->where('user_id', $user->id);
                }

                return $query->with('translations', 'category', 'tags', 'user', 'series.translations');
            })
            ->columns([
                TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('slug')
                    ->sortable()
                    ->searchable(),
                ImageColumn::make('photo')
                    ->getStateUsing(fn ($record) => $record->photo_url),
                TextColumn::make('user.name')
                    ->label('Author')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                TextColumn::make('locales')
                    ->label('Locales')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->translations->pluck('locale')->map('strtoupper')->toArray())
                    ->color('primary'),
                TextColumn::make('series.title')
                    ->label('Series')
                    ->placeholder('—'),
                TextColumn::make('tags.name')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $tags = $record->tags;
                        $tagNames = $tags->pluck('name')->toArray();

                        if (count($tagNames) <= 3) {
                            return $tagNames;
                        }

                        $displayTags = array_slice($tagNames, 0, 3);
                        $remainingCount = count($tagNames) - 3;

                        $otherText = $remainingCount === 1 ? 'other' : 'others';
                        $displayTags[] = "+{$remainingCount} {$otherText}";

                        return $displayTags;
                    })
                    ->listWithLineBreaks(),
                TextColumn::make('publication_status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->color(fn ($record) => $record->getPublicationStatusColor())
                    ->getStateUsing(fn ($record) => ucfirst($record->getPublicationStatus())),
                TextColumn::make('published_at')
                    ->label('Publish Date')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not set'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name'),
                SelectFilter::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple(),
                TernaryFilter::make('is_published')
                    ->attribute('is_published')
                    ->label('Publication Status')
                    ->trueLabel('Published')
                    ->falseLabel(__('blogr::date.draft')),
                Filter::make('published_at')
                    ->form([
                        DatePicker::make('published_from'),
                        DatePicker::make('published_until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['published_from'], fn ($q, $date) => $q->whereDate('published_at', '>=', $date))
                            ->when($data['published_until'], fn ($q, $date) => $q->whereDate('published_at', '<=', $date));
                    }),
                SelectFilter::make('blog_series_id')
                    ->label('Series')
                    ->options(fn () => BlogSeries::with('translations')->get()->pluck('title', 'id'))
                    ->attribute('blog_series_id'),
                SelectFilter::make('locale')
                    ->label('Language')
                    ->options([
                        'en' => 'English',
                        'fr' => 'Français',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['value'], fn ($q, $locale) => $q->whereHas('translations', fn ($q) => $q->where('locale', $locale)));
                    }),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
