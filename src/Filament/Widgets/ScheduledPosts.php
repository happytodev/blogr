<?php

namespace Happytodev\Blogr\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Happytodev\Blogr\Models\BlogPost;
use Illuminate\Database\Eloquent\Builder;

class ScheduledPosts extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Scheduled Posts';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BlogPost::query()
                    ->with(['category', 'user'])
                    ->where('is_published', true)
                    ->whereNotNull('published_at')
                    ->where('published_at', '>', now())
                    ->orderBy('published_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    }),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->placeholder('No author'),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Will publish')
                    ->dateTime()
                    ->since()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('published_at', 'asc')
            ->emptyStateHeading('No scheduled posts')
            ->emptyStateDescription('Posts scheduled for future publication will appear here.')
            ->emptyStateIcon('heroicon-o-clock');
    }
}
