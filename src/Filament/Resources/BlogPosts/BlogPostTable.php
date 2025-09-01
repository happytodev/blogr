<?php

namespace Happytodev\Blogr\Filament\Resources\BlogPosts;


use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Storage;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;

class BlogPostTable
{
    public static function configure(Table $schema): Table
    {
        return $schema
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
                TextColumn::make('category.name')
                    ->label('Category'),
                TextColumn::make('tags.name')
                    ->badge(),
                TextColumn::make('publication_status')
                    ->label('Status')
                    ->badge()
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
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
