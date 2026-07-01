<?php

namespace Happytodev\Blogr\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Happytodev\Blogr\Models\BlogPost;

class MissingSeoAlert extends BaseWidget
{
    protected static ?string $heading = 'SEO Checklist';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $missingMeta = BlogPost::query()
            ->where('is_published', true)
            ->whereHas('translations', function ($q) {
                $q->whereNull('seo_description')
                    ->orWhere('seo_description', '')
                    ->orWhereNull('seo_title')
                    ->orWhere('seo_title', '');
            })
            ->orWhereNull('photo')
            ->orWhere('photo', '')
            ->with(['translations', 'category'])
            ->limit(20);

        return $table
            ->query($missingMeta)
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Post')
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        return strlen($state) > 40 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('missing_seo')
                    ->label('Missing')
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(function (BlogPost $record): array {
                        $missing = [];

                        $translation = $record->translations->first();
                        if ($translation) {
                            if (blank($translation->seo_title)) {
                                $missing[] = 'SEO Title';
                            }
                            if (blank($translation->seo_description)) {
                                $missing[] = 'Meta Description';
                            }
                        }

                        if (blank($record->photo)) {
                            $missing[] = 'Featured Image';
                        }

                        return $missing;
                    })
                    ->listWithLineBreaks(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('primary')
                    ->placeholder('Uncategorized'),
            ])
            ->emptyStateHeading('All posts have SEO metadata')
            ->emptyStateDescription('Every published post has a meta description, SEO title and featured image.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
