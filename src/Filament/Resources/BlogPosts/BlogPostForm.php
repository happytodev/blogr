<?php

namespace Happytodev\Blogr\Filament\Resources\BlogPosts;

use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Tabs;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Happytodev\Blogr\Models\Category;
use Illuminate\Support\HtmlString;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\BlogPost;
use Illuminate\Support\Str;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Translations Section - MOVED TO TOP
                Section::make('Content & Translations')
                    ->description('Add content for each language')
                    ->schema([
                        Repeater::make('translations')
                            ->relationship('translations')
                            ->schema([
                                Select::make('locale')
                                    ->label('Language')
                                    ->options(function () {
                                        $availableLocales = config('blogr.locales.available', ['en']);
                                        return collect($availableLocales)->mapWithKeys(function ($locale) {
                                            return [$locale => strtoupper($locale)];
                                        });
                                    })
                                    ->required()
                                    ->reactive()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->columnSpan(1),
                                
                                TextInput::make('title')
                                    ->label('Title')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                        if ($state && !$get('slug')) {
                                            $set('slug', Str::slug($state));
                                        }
                                    })
                                    ->columnSpan(2),
                                
                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('URL-friendly version of the title')
                                    ->columnSpan(2),
                                
                                FileUpload::make('photo')
                                    ->label('Cover Image (Optional)')
                                    ->image()
                                    ->disk('public')
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        null,
                                        '16:9',
                                        '4:3',
                                        '1:1',
                                    ])
                                    ->directory('blog-photos')
                                    ->nullable()
                                    ->helperText('Leave empty to use the main post image or another translation\'s image')
                                    ->columnSpanFull(),
                                
                                Textarea::make('tldr')
                                    ->label('TL;DR (Too Long; Didn\'t Read)')
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->helperText('Short summary of the article (max 500 characters)')
                                    ->columnSpanFull(),
                                
                                MarkdownEditor::make('content')
                                    ->label('Content')
                                    ->required()
                                    ->toolbarButtons([
                                        'attachFiles',
                                        'blockquote',
                                        'bold',
                                        'bulletList',
                                        'codeBlock',
                                        'heading',
                                        'italic',
                                        'link',
                                        'orderedList',
                                        'redo',
                                        'strike',
                                        'table',
                                        'undo',
                                    ])
                                    ->columnSpanFull(),
                                
                                TextInput::make('seo_title')
                                    ->label('SEO Title')
                                    ->maxLength(60)
                                    ->helperText('Leave empty to use post title (recommended max: 60 characters)')
                                    ->columnSpan(2),
                                
                                Textarea::make('seo_description')
                                    ->label('SEO Description')
                                    ->rows(2)
                                    ->maxLength(160)
                                    ->helperText('Leave empty to use TL;DR (recommended max: 160 characters)')
                                    ->columnSpan(2),
                                
                                TextInput::make('seo_keywords')
                                    ->label('SEO Keywords')
                                    ->maxLength(255)
                                    ->helperText('Comma-separated keywords')
                                    ->columnSpanFull(),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->addActionLabel('Add Translation')
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(function (array $state): HtmlString {
                                $locale = $state['locale'] ?? 'new';
                                $title = $state['title'] ?? '';
                                $slug = $state['slug'] ?? '';
                                
                                // Map of locale to flag emoji
                                $flags = [
                                    'en' => '🇬🇧',
                                    'fr' => '🇫🇷',
                                    'es' => '🇪🇸',
                                    'de' => '🇩🇪',
                                    'it' => '🇮🇹',
                                    'pt' => '🇵🇹',
                                    'nl' => '🇳🇱',
                                    'pl' => '🇵🇱',
                                    'ru' => '🇷🇺',
                                    'ja' => '🇯🇵',
                                    'zh' => '🇨🇳',
                                    'ar' => '🇸🇦',
                                ];
                                
                                $flag = $flags[$locale] ?? '🌐';
                                $localeUpper = strtoupper($locale === 'new' ? 'NEW' : $locale);
                                
                                $label = "<span style='font-size: 1.1rem; font-weight: 600; color: #6366f1;'>{$flag} {$localeUpper}</span>";
                                
                                if ($title) {
                                    $label .= "<span style='color: #374151; margin-left: 0.5rem;'>- {$title}</span>";
                                }
                                
                                if ($slug) {
                                    $label .= "<span style='color: #6b7280; margin-left: 0.5rem; font-size: 0.9rem;'>({$slug})</span>";
                                }
                                
                                return new HtmlString($label);
                            })
                            ->columnSpanFull()
                            ->minItems(1)
                            ->reorderable(false),
                    ])
                    ->columnSpanFull(),
                
                // Metadata Section
                Section::make('Post Metadata')
                    ->description('Configure the post settings and associations')
                    ->schema([
                        FileUpload::make('photo')
                            ->label('Cover Image')
                            ->image()
                            ->disk('public')
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
                            ->helperText('The post will display the category name in the visitor\'s language if translations are available.')
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Category Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        if ($state) {
                                            $set('slug', Str::slug($state));
                                        }
                                    })
                                    ->helperText('Main category name (usually in English)'),
                                
                                TextInput::make('slug')
                                    ->label('Slug')
                                    ->required()
                                    ->unique('categories', 'slug')
                                    ->maxLength(255)
                                    ->helperText('URL-friendly version of the name'),
                                
                                Toggle::make('is_default')
                                    ->label('Set as Default Category')
                                    ->default(false)
                                    ->helperText('Set this as the default category for new posts'),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $category = Category::create([
                                    'name' => $data['name'],
                                    'slug' => $data['slug'],
                                    'is_default' => $data['is_default'] ?? false,
                                ]);
                                
                                return $category->id;
                            }),
                        
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
                        
                        Select::make('user_id')
                            ->label('Author')
                            ->options(function () {
                                $userModel = config('auth.providers.users.model');
                                return $userModel::pluck('name', 'id');
                            })
                            ->default(fn() => Filament::auth()->user()->id)
                            ->searchable()
                            ->preload()
                            ->visible(fn () => Filament::auth()->user() && method_exists(Filament::auth()->user(), 'hasRole') ? Filament::auth()->user()->hasRole('admin') : false)
                            ->helperText('Select the author for this post (admins only)'),

                        Hidden::make('user_id')
                            ->default(fn() => Filament::auth()->user()->id)
                            ->visible(fn () => !(Filament::auth()->user() && method_exists(Filament::auth()->user(), 'hasRole') ? Filament::auth()->user()->hasRole('admin') : false)),
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
