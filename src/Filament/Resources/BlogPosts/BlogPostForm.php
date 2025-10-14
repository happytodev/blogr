<?php

namespace Happytodev\Blogr\Filament\Resources\BlogPosts;

use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\BlogPost;
use Illuminate\Support\Str;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Metadata Section
                Section::make('Post Metadata')
                    ->description('Configure the post settings and associations')
                    ->schema([
                        FileUpload::make('photo')
                            ->label('Cover Image')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->directory('blog-photos')
                            ->columnSpanFull()
                            ->nullable()
                            ->helperText('Upload a cover image for this blog post'),
                        
                        Select::make('category_id')
                            ->label('Category')
                            ->options(Category::pluck('name', 'id'))
                            ->default(function () {
                                return Category::where('is_default', true)->first()->id;
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('The post will display the category name in the visitor\'s language if translations are available.'),
                        
                        Select::make('tags')
                            ->multiple()
                            ->relationship('tags', 'name')
                            ->preload()
                            ->searchable()
                            ->helperText('Tags will be displayed in the visitor\'s language if translations are available.')
                            ->createOptionForm([
                                \Filament\Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        if ($state) {
                                            $set('slug', Str::slug($state));
                                        }
                                    }),
                                \Filament\Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->unique()
                                    ->maxLength(255),
                            ]),
                        
                        // Blog Series fields
                        Select::make('blog_series_id')
                            ->label('Blog Series')
                            ->placeholder('Select a series (optional)')
                            ->options(function () {
                                return BlogSeries::query()
                                    ->with('translations')
                                    ->get()
                                    ->mapWithKeys(function ($series) {
                                        $translation = $series->getDefaultTranslation();
                                        $label = $translation ? $translation->title : $series->slug;
                                        return [$series->id => $label];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Assign this post to a series')
                            ->reactive(),
                        
                        \Filament\Forms\Components\TextInput::make('series_position')
                            ->label('Position in Series')
                            ->numeric()
                            ->minValue(1)
                            ->default(1)
                            ->helperText('Order of this post within the series')
                            ->visible(fn ($get) => $get('blog_series_id') !== null),
                        
                        Toggle::make('display_toc')
                            ->label('Display Table of Contents')
                            ->default(function () {
                                return config('blogr.toc.enabled', true);
                            })
                            ->helperText('Show table of contents for this post'),
                        
                        Hidden::make('user_id')
                            ->default(fn() => Filament::auth()->user()->id),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                
                // Publication Section
                Section::make('Publication Settings')
                    ->description('Control when and how this post is published')
                    ->schema([
                        Toggle::make('is_published')
                            ->label(function (Get $get) {
                                $isPublished = $get('is_published');
                                $publishedAt = $get('published_at');

                                if (!$isPublished) {
                                    return 'Draft';
                                }

                                if (!$publishedAt) {
                                    return 'Published';
                                }

                                $publishDate = \Carbon\Carbon::parse($publishedAt);
                                if ($publishDate->isFuture()) {
                                    return 'Scheduled';
                                }

                                return 'Published';
                            })
                            ->onColor(function (Get $get) {
                                $publishedAt = $get('published_at');

                                if ($publishedAt && \Carbon\Carbon::parse($publishedAt)->isFuture()) {
                                    return 'warning'; // Orange for scheduled
                                }

                                return 'success'; // Green for published
                            })
                            ->offColor('gray') // Gray for draft
                            ->default(false)
                            ->live()
                            ->visible(fn() => Filament::auth()->user() && method_exists(Filament::auth()->user(), 'hasRole') ? Filament::auth()->user()->hasRole('admin') : false)
                            ->afterStateUpdated(function (Set $set, Get $get, ?bool $state) {
                                if ($state) {
                                    // When activating publication
                                    $currentDate = $get('published_at');

                                    // If no date is set or date is in the past, leave empty for immediate publication
                                    if (!$currentDate || \Carbon\Carbon::parse($currentDate)->isPast()) {
                                        $set('published_at', null);
                                    }
                                    // If future date is set, keep it for scheduled publication
                                } elseif (!$state) {
                                    // Clear published_at when unpublishing
                                    $set('published_at', null);
                                }
                            }),
                        
                        DateTimePicker::make('published_at')
                            ->label('Publish Date')
                            ->nullable()
                            ->live()
                            ->visible(fn() => Filament::auth()->user() && method_exists(Filament::auth()->user(), 'hasRole') ? Filament::auth()->user()->hasRole('admin') : false)
                            ->helperText('Leave empty for immediate publication, or set a future date to schedule publication.'),
                        
                        Select::make('default_locale')
                            ->label('Default Language')
                            ->options(function () {
                                $availableLocales = config('blogr.locales.available', ['en']);
                                return collect($availableLocales)->mapWithKeys(function ($locale) {
                                    return [$locale => strtoupper($locale)];
                                });
                            })
                            ->default(config('blogr.locales.default', 'en'))
                            ->required()
                            ->helperText('The primary language for this post'),
                    ])
                    ->columns(2)
                    ->columnSpanFull()
                    ->collapsed(),
            ]);
    }
}
