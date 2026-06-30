<?php

namespace Happytodev\Blogr\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Happytodev\Blogr\Filament\Resources\BlogPostResource;
use Happytodev\Blogr\Models\BlogPost;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;

class RecentBlogPosts extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

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
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('title')
                    ->limit(50)
                    ->url(function (?BlogPost $record): ?string {
                        if (! $record) {
                            return null;
                        }

                        $locale = app()->getLocale();
                        $defaultLocale = config('blogr.locales.default', 'en');
                        $localesEnabled = config('blogr.locales.enabled', false);

                        $translation = $record->translations->first();
                        $slug = $translation ? $translation->slug : $record->id;

                        if ($localesEnabled && Route::has('blog.show')) {
                            $route = Route::getRoutes()->getByName('blog.show');
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
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('primary')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Author')
                    ->placeholder('No author')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'scheduled' => 'warning',
                        'draft' => 'gray',
                    })
                    ->getStateUsing(function (BlogPost $record): string {
                        return $record->getPublicationStatus();
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('reading_time')
                    ->label('Reading')
                    ->getStateUsing(function (BlogPost $record): string {
                        return $record->getEstimatedReadingTime();
                    })
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime()
                    ->placeholder('Not published')
                    ->since()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'published' => 'Published',
                        'draft' => 'Draft',
                        'scheduled' => 'Scheduled',
                    ])
                    ->query(function (Builder $query, array $data): void {
                        if (blank($data['value'])) {
                            return;
                        }

                        $value = $data['value'];

                        $query->when($value === 'published', function (Builder $q) {
                            $q->where('is_published', true)
                                ->where(function (Builder $sub) {
                                    $sub->whereNull('published_at')
                                        ->orWhere('published_at', '<=', now());
                                });
                        })->when($value === 'draft', function (Builder $q) {
                            $q->where('is_published', false);
                        })->when($value === 'scheduled', function (Builder $q) {
                            $q->where('is_published', true)
                                ->whereNotNull('published_at')
                                ->where('published_at', '>', now());
                        });
                    }),

                Tables\Filters\SelectFilter::make('category')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-m-pencil-square')
                    ->color('primary')
                    ->url(fn (BlogPost $record): string => BlogPostResource::getUrl('edit', ['record' => $record])),
            ])
            ->columnManager()
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No blog posts yet')
            ->emptyStateDescription('Create your first blog post to get started.')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
