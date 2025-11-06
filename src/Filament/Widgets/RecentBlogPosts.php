<?php

namespace Happytodev\Blogr\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Happytodev\Blogr\Models\BlogPost;
use Illuminate\Database\Eloquent\Builder;

class RecentBlogPosts extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                BlogPost::query()
                    ->with(['category', 'user', 'translations'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->limit(50)
                    ->url(function (?\Happytodev\Blogr\Models\BlogPost $record): ?string {
                        if (!$record) {
                            return null;
                        }
                        
                        $locale = app()->getLocale();
                        $defaultLocale = config('blogr.locales.default', 'en');
                        $localesEnabled = config('blogr.locales.enabled', false);
                        
                        // Get the first available translation slug
                        $translation = $record->translations->first();
                        $slug = $translation ? $translation->slug : $record->id;
                        
                        // If locales are enabled and route has locale parameter
                        if ($localesEnabled && \Illuminate\Support\Facades\Route::has('blog.show')) {
                            $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('blog.show');
                            if ($route && str_contains($route->uri(), '{locale}')) {
                                return route('blog.show', ['locale' => $locale ?: $defaultLocale, 'slug' => $slug]);
                            }
                        }
                        
                        return route('blog.show', ['slug' => $slug]);
                    })
                    ->openUrlInNewTab()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
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

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'scheduled' => 'warning',
                        'draft' => 'gray',
                    })
                    ->getStateUsing(function (BlogPost $record): string {
                        return $record->getPublicationStatus();
                    }),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime()
                    ->placeholder('Not published')
                    ->since(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No blog posts yet')
            ->emptyStateDescription('Create your first blog post to get started.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
