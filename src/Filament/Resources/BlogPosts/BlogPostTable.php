<?php

namespace Happytodev\Blogr\Filament\Resources\BlogPosts;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Facades\Filament;

class BlogPostTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $user = Filament::auth()->user();
                if ($user->hasRole('writer') && !$user->hasRole('admin')) {
                    // Writers can only see their own posts
                    $query->where('user_id', $user->id);
                }
                // Admins can see all posts
                return $query;
            })
            ->columns([
                TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('slug')
                    ->sortable()
                    ->searchable(),
                ImageColumn::make('photo')
                    ->getStateUsing(function ($record) {
                        return $record->photo ? Storage::temporaryUrl($record->photo, now()->addMinutes(5)) : null;
                    }),
                TextColumn::make('user.name')
                    ->label('Author')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                TextColumn::make('tags.name')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $tags = $record->tags;
                        $tagNames = $tags->pluck('name')->toArray();

                        if (count($tagNames) <= 3) {
                            return $tagNames;
                        }

                        // Take first 3 tags and add "+X other(s)"
                        $displayTags = array_slice($tagNames, 0, 3);
                        $remainingCount = count($tagNames) - 3;

                        // Use singular "other" for 1, plural "others" for > 1
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
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
