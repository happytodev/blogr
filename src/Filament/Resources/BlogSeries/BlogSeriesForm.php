<?php

namespace Happytodev\Blogr\Filament\Resources\BlogSeries;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;

class BlogSeriesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Series Information')
                    ->description('Basic information about the blog series')
                    ->schema([
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Unique identifier for the series (e.g., "learn-laravel")')
                            ->placeholder('my-series-slug')
                            ->columnSpan(1),
                        
                        TextInput::make('position')
                            ->label('Display Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Order position for displaying series (lower numbers appear first)')
                            ->columnSpan(1),
                        
                        FileUpload::make('photo')
                            ->label('Series Image')
                            ->image()
                            ->directory('series-images')
                            ->visibility('public')
                            ->imageEditor()
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1200')
                            ->imageResizeTargetHeight('675')
                            ->helperText('Recommended size: 1200x675px (16:9 ratio). Leave empty to use default.')
                            ->columnSpan(2),
                        
                        Toggle::make('is_featured')
                            ->label('Featured Series')
                            ->helperText('Featured series will be highlighted on the blog')
                            ->default(false)
                            ->columnSpan(1),
                        
                        DateTimePicker::make('published_at')
                            ->label('Publish Date')
                            ->helperText('Leave empty to keep as draft. Set to publish the series.')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpan('full'),

                Section::make('Translations')
                    ->description('Add translations for different languages')
                    ->schema([
                        Repeater::make('translations')
                            ->relationship()
                            ->schema([
                                Select::make('locale')
                                    ->label('Language')
                                    ->options([
                                        'en' => 'English',
                                        'fr' => 'Français',
                                        'es' => 'Español',
                                        'de' => 'Deutsch',
                                    ])
                                    ->required()
                                    ->columnSpan(2),
                                
                                TextInput::make('title')
                                    ->label('Title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),
                                
                                Textarea::make('description')
                                    ->label('Description')
                                    ->rows(3)
                                    ->columnSpan(2),
                                
                                TextInput::make('seo_title')
                                    ->label('SEO Title')
                                    ->maxLength(255)
                                    ->helperText('Optimized title for search engines')
                                    ->columnSpan(2),
                                
                                Textarea::make('seo_description')
                                    ->label('SEO Description')
                                    ->rows(2)
                                    ->maxLength(160)
                                    ->helperText('Meta description for search engines (max 160 characters)')
                                    ->columnSpan(2),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                isset($state['title']) && isset($state['locale']) 
                                    ? "{$state['locale']}: {$state['title']}" 
                                    : null
                            )
                            ->addActionLabel('Add Translation')
                            ->defaultItems(0),
                    ])
                    ->columnSpan('full'),
            ]);
    }
}
