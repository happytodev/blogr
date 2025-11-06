<?php

namespace Happytodev\Blogr\Filament\Resources\CmsPages;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Happytodev\Blogr\Enums\CmsBlockType;
use Happytodev\Blogr\Enums\BackgroundType;

class CmsBlockBuilder
{
    public static function make(): Builder
    {
        return Builder::make('blocks')
            ->label(__('Content Blocks'))
            ->blocks([
                self::heroBlock(),
                self::featuresBlock(),
                self::testimonialsBlock(),
                self::ctaBlock(),
                self::contentBlock(),
                self::faqBlock(),
                self::galleryBlock(),
                self::teamBlock(),
                self::pricingBlock(),
                self::blogPostsBlock(),
                self::statsBlock(),
                self::timelineBlock(),
                self::videoBlock(),
                self::newsletterBlock(),
                self::mapBlock(),
            ])
            ->collapsible()
            ->blockNumbers(false)
            ->columnSpanFull();
    }

    protected static function heroBlock(): Block
    {
        return Block::make(CmsBlockType::HERO->value)
            ->label(CmsBlockType::HERO->getLabel())
            ->icon(CmsBlockType::HERO->getIcon())
            ->schema([
                Section::make(__('Content'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Title'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Textarea::make('subtitle')
                            ->label(__('Subtitle'))
                            ->maxLength(500)
                            ->rows(2)
                            ->columnSpan(2),

                        FileUpload::make('image')
                            ->label(__('Hero Image'))
                            ->image()
                            ->disk('public')
                            ->directory('cms-blocks')
                            ->visibility('public')
                            ->imageEditor()
                            ->columnSpan(2),

                        TextInput::make('cta_text')
                            ->label(__('Button Text'))
                            ->maxLength(50)
                            ->columnSpan(1),

                        TextInput::make('cta_url')
                            ->label(__('Button URL'))
                            ->url()
                            ->columnSpan(1),

                        Select::make('alignment')
                            ->label(__('Text Alignment'))
                            ->options([
                                'left' => __('Left'),
                                'center' => __('Center'),
                                'right' => __('Right'),
                            ])
                            ->default('center')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                self::getBackgroundFields(),
            ])
            ->columns(1);
    }

    protected static function featuresBlock(): Block
    {
        return Block::make(CmsBlockType::FEATURES->value)
            ->label(CmsBlockType::FEATURES->getLabel())
            ->icon(CmsBlockType::FEATURES->getIcon())
            ->schema([
                Section::make(__('Content'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Section Title'))
                            ->maxLength(255)
                            ->columnSpan(2),

                        Textarea::make('subtitle')
                            ->label(__('Subtitle'))
                            ->maxLength(500)
                            ->rows(2)
                            ->columnSpan(2),

                        Select::make('columns')
                            ->label(__('Columns'))
                            ->options([
                                '2' => __('2 columns'),
                                '3' => __('3 columns'),
                                '4' => __('4 columns'),
                            ])
                            ->default('3')
                            ->columnSpan(1),

                        Repeater::make('items')
                            ->label(__('Features'))
                            ->schema([
                                TextInput::make('icon')
                                    ->label(__('Heroicon Name'))
                                    ->helperText(__('Ex: heroicon-o-bolt, heroicon-o-shield-check'))
                                    ->placeholder('heroicon-o-bolt')
                                    ->columnSpan(2),

                                TextInput::make('title')
                                    ->label(__('Title'))
                                    ->required()
                                    ->columnSpan(2),

                                Textarea::make('description')
                                    ->label(__('Description'))
                                    ->rows(2)
                                    ->columnSpan(2),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state) => $state['title'] ?? __('New Feature'))
                            ->defaultItems(3)
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                self::getBackgroundFields(),
            ])
            ->columns(1);
    }

    protected static function testimonialsBlock(): Block
    {
        return Block::make(CmsBlockType::TESTIMONIALS->value)
            ->label(CmsBlockType::TESTIMONIALS->getLabel())
            ->icon(CmsBlockType::TESTIMONIALS->getIcon())
            ->schema([
                Section::make(__('Content'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Section Title'))
                            ->maxLength(255)
                            ->columnSpan(2),

                        Toggle::make('full_width')
                            ->label(__('Full Width Layout'))
                            ->helperText(__('Display testimonials in full width (recommended for single featured quote)'))
                            ->default(false)
                            ->columnSpan(2),

                        Repeater::make('items')
                            ->label(__('Testimonials'))
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('Name'))
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('role')
                                    ->label(__('Role/Company'))
                                    ->columnSpan(1),

                                FileUpload::make('photo')
                                    ->label(__('Photo'))
                                    ->image()
                                    ->disk('public')
                                    ->directory('cms-blocks/testimonials')
                                    ->visibility('public')
                                    ->avatar()
                                    ->imageEditor()
                                    ->columnSpan(2),

                                Textarea::make('quote')
                                    ->label(__('Quote'))
                                    ->required()
                                    ->rows(3)
                                    ->columnSpan(2),

                                Select::make('rating')
                                    ->label(__('Rating'))
                                    ->options([
                                        '0' => __('No rating'),
                                        '1' => '⭐',
                                        '2' => '⭐⭐',
                                        '3' => '⭐⭐⭐',
                                        '4' => '⭐⭐⭐⭐',
                                        '5' => '⭐⭐⭐⭐⭐',
                                    ])
                                    ->default('5')
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state) => $state['name'] ?? __('New Testimonial'))
                            ->defaultItems(2)
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                self::getBackgroundFields(),
            ])
            ->columns(1);
    }

    protected static function ctaBlock(): Block
    {
        return Block::make(CmsBlockType::CTA->value)
            ->label(CmsBlockType::CTA->getLabel())
            ->icon(CmsBlockType::CTA->getIcon())
            ->schema([
                Section::make(__('Content'))
                    ->schema([
                        TextInput::make('heading')
                            ->label(__('Heading'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Textarea::make('subheading')
                            ->label(__('Subheading'))
                            ->maxLength(500)
                            ->rows(2)
                            ->columnSpan(2),

                        TextInput::make('button_text')
                            ->label(__('Button Text'))
                            ->required()
                            ->maxLength(50)
                            ->columnSpan(1),

                        TextInput::make('button_url')
                            ->label(__('Button URL'))
                            ->required()
                            ->url()
                            ->columnSpan(1),

                        Select::make('button_style')
                            ->label(__('Button Style'))
                            ->options([
                                'primary' => __('Primary'),
                                'secondary' => __('Secondary'),
                            ])
                            ->default('primary')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                self::getBackgroundFields(),
            ])
            ->columns(1);
    }

    protected static function contentBlock(): Block
    {
        return Block::make(CmsBlockType::CONTENT->value)
            ->label(CmsBlockType::CONTENT->getLabel())
            ->icon(CmsBlockType::CONTENT->getIcon())
            ->schema([
                Section::make(__('Content'))
                    ->schema([
                        MarkdownEditor::make('content')
                            ->label(__('Content'))
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'link',
                                'heading',
                                'bulletList',
                                'orderedList',
                                'blockquote',
                                'codeBlock',
                            ])
                            ->columnSpan(2),

                        Select::make('max_width')
                            ->label(__('Maximum Width'))
                            ->options([
                                'prose' => __('Prose (readable width)'),
                                'full' => __('Full width'),
                            ])
                            ->default('prose')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                self::getBackgroundFields(),
            ])
            ->columns(1);
    }

    protected static function faqBlock(): Block
    {
        return Block::make(CmsBlockType::FAQ->value)
            ->label(CmsBlockType::FAQ->getLabel())
            ->icon(CmsBlockType::FAQ->getIcon())
            ->schema([
                Section::make(__('Content'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Section Title'))
                            ->maxLength(255)
                            ->columnSpan(2),

                        Repeater::make('items')
                            ->label(__('Questions'))
                            ->schema([
                                TextInput::make('question')
                                    ->label(__('Question'))
                                    ->required()
                                    ->columnSpan(2),

                                Textarea::make('answer')
                                    ->label(__('Answer'))
                                    ->required()
                                    ->rows(3)
                                    ->columnSpan(2),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state) => $state['question'] ?? __('New Question'))
                            ->defaultItems(3)
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                self::getBackgroundFields(),
            ])
            ->columns(1);
    }

    protected static function galleryBlock(): Block
    {
        return Block::make(CmsBlockType::GALLERY->value)
            ->label(CmsBlockType::GALLERY->getLabel())
            ->icon(CmsBlockType::GALLERY->getIcon())
            ->schema([
                Section::make(__('Content'))
                    ->schema([
                        TextInput::make('heading')
                            ->label(__('Heading'))
                            ->maxLength(255)
                            ->columnSpan(2),

                        Textarea::make('description')
                            ->label(__('Description'))
                            ->rows(2)
                            ->columnSpan(2),

                        Select::make('layout')
                            ->label(__('Layout'))
                            ->options([
                                'grid' => __('Grid (equal heights)'),
                                'masonry' => __('Masonry (Pinterest style)'),
                                'bento' => __('Bento Grid (Apple style)'),
                            ])
                            ->default('grid')
                            ->live()
                            ->columnSpan(1),

                        Select::make('columns')
                            ->label(__('Columns'))
                            ->options([
                                '2' => '2',
                                '3' => '3',
                                '4' => '4',
                            ])
                            ->default('3')
                            ->visible(fn ($get) => $get('layout') === 'grid')
                            ->dehydrated()
                            ->columnSpan(1),

                        FileUpload::make('images')
                            ->label(__('Images'))
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->disk('public')
                            ->directory('cms-blocks/gallery')
                            ->visibility('public')
                            ->imageEditor()
                            ->required()
                            ->helperText(__('For Bento Grid, first 6 images work best. Masonry works with any number.'))
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                self::getBackgroundFields(),
            ])
            ->columns(1);
    }

    protected static function teamBlock(): Block
    {
        return Block::make(CmsBlockType::TEAM->value)
            ->label(CmsBlockType::TEAM->getLabel())
            ->icon(CmsBlockType::TEAM->getIcon())
            ->schema([
                Section::make(__('Content'))
                    ->schema([
                        TextInput::make('heading')
                            ->label(__('Heading'))
                            ->maxLength(255)
                            ->columnSpan(2),

                        Textarea::make('description')
                            ->label(__('Description'))
                            ->rows(2)
                            ->columnSpan(2),

                        Select::make('columns')
                            ->label(__('Columns'))
                            ->options([
                                '2' => __('2 columns'),
                                '3' => __('3 columns'),
                                '4' => __('4 columns'),
                            ])
                            ->default('3')
                            ->columnSpan(1),

                        Repeater::make('members')
                            ->label(__('Team Members'))
                            ->schema([
                                FileUpload::make('photo')
                                    ->label(__('Photo'))
                                    ->image()
                                    ->disk('public')
                                    ->directory('cms-blocks/team')
                                    ->visibility('public')
                                    ->avatar()
                                    ->imageEditor()
                                    ->columnSpan(2),

                                TextInput::make('name')
                                    ->label(__('Name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                TextInput::make('role')
                                    ->label(__('Role/Position'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                Textarea::make('bio')
                                    ->label(__('Bio'))
                                    ->rows(3)
                                    ->columnSpan(2),

                                TextInput::make('linkedin')
                                    ->label(__('LinkedIn'))
                                    ->url()
                                    ->placeholder('https://linkedin.com/in/username')
                                    ->columnSpan(1),

                                TextInput::make('twitter')
                                    ->label(__('Twitter/X'))
                                    ->url()
                                    ->placeholder('https://twitter.com/username')
                                    ->columnSpan(1),

                                TextInput::make('email')
                                    ->label(__('Email'))
                                    ->email()
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state) => $state['name'] ?? __('New Member'))
                            ->defaultItems(3)
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                self::getBackgroundFields(),
            ])
            ->columns(1);
    }

    protected static function pricingBlock(): Block
    {
        return Block::make(CmsBlockType::PRICING->value)
            ->label(CmsBlockType::PRICING->getLabel())
            ->icon(CmsBlockType::PRICING->getIcon())
            ->schema([
                Section::make(__('Content'))
                    ->schema([
                        TextInput::make('heading')
                            ->label(__('Heading'))
                            ->maxLength(255)
                            ->columnSpan(2),

                        Textarea::make('description')
                            ->label(__('Description'))
                            ->rows(2)
                            ->columnSpan(2),

                        Repeater::make('plans')
                            ->label(__('Pricing Plans'))
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('Plan Name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                TextInput::make('price')
                                    ->label(__('Price'))
                                    ->required()
                                    ->numeric()
                                    ->prefix('$')
                                    ->columnSpan(1),

                                Select::make('period')
                                    ->label(__('Period'))
                                    ->options([
                                        'month' => __('/ month'),
                                        'year' => __('/ year'),
                                        'once' => __('one-time'),
                                    ])
                                    ->default('month')
                                    ->columnSpan(1),

                                Textarea::make('description')
                                    ->label(__('Description'))
                                    ->rows(2)
                                    ->columnSpan(2),

                                Repeater::make('features')
                                    ->label(__('Features'))
                                    ->simple(
                                        TextInput::make('feature')
                                            ->required()
                                            ->placeholder(__('Feature description'))
                                    )
                                    ->defaultItems(3)
                                    ->columnSpan(2),

                                TextInput::make('cta_text')
                                    ->label(__('Button Text'))
                                    ->default(__('Get Started'))
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('cta_url')
                                    ->label(__('Button URL'))
                                    ->url()
                                    ->required()
                                    ->columnSpan(1),

                                Select::make('highlight')
                                    ->label(__('Highlight Plan'))
                                    ->boolean()
                                    ->helperText(__('Show "Popular" badge'))
                                    ->columnSpan(1),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(fn (array $state) => $state['name'] ?? __('New Plan'))
                            ->defaultItems(3)
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                self::getBackgroundFields(),
            ])
            ->columns(1);
    }

    protected static function blogPostsBlock(): Block
    {
        return Block::make(CmsBlockType::BLOG_POSTS->value)
            ->label(CmsBlockType::BLOG_POSTS->getLabel())
            ->icon(CmsBlockType::BLOG_POSTS->getIcon())
            ->schema([
                Section::make(__('Content'))
                    ->schema([
                        TextInput::make('heading')
                            ->label(__('Heading'))
                            ->default(__('Latest Posts'))
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('limit')
                            ->label(__('Number of Posts'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12)
                            ->default(3)
                            ->columnSpan(1),

                        Select::make('layout')
                            ->label(__('Layout'))
                            ->options([
                                'grid' => __('Grid'),
                                'list' => __('List'),
                            ])
                            ->default('grid')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                self::getBackgroundFields(),
            ])
            ->columns(1);
    }

    protected static function statsBlock(): Block
    {
        return Block::make(CmsBlockType::STATS->value)
            ->label(CmsBlockType::STATS->getLabel())
            ->icon(CmsBlockType::STATS->getIcon())
            ->schema([
                Section::make(__('Content'))
                    ->schema([
                        TextInput::make('heading')
                            ->label(__('Heading'))
                            ->maxLength(255)
                            ->columnSpan(2),

                        Repeater::make('stats')
                            ->label(__('Statistics'))
                            ->schema([
                                TextInput::make('number')
                                    ->label(__('Number'))
                                    ->required()
                                    ->numeric()
                                    ->columnSpan(1),

                                TextInput::make('suffix')
                                    ->label(__('Suffix'))
                                    ->placeholder(__('+, K, M, %'))
                                    ->columnSpan(1),

                                TextInput::make('label')
                                    ->label(__('Label'))
                                    ->required()
                                    ->columnSpan(2),
                            ])
                            ->columns(2)
                            ->defaultItems(3)
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                self::getBackgroundFields(),
            ])
            ->columns(1);
    }

    protected static function timelineBlock(): Block
    {
        return Block::make(CmsBlockType::TIMELINE->value)
            ->label(CmsBlockType::TIMELINE->getLabel())
            ->icon(CmsBlockType::TIMELINE->getIcon())
            ->schema([
                Section::make(__('Content'))
                    ->schema([
                        TextInput::make('heading')
                            ->label(__('Heading'))
                            ->maxLength(255)
                            ->columnSpan(2),

                        Repeater::make('events')
                            ->label(__('Timeline Events'))
                            ->schema([
                                TextInput::make('date')
                                    ->label(__('Date'))
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('title')
                                    ->label(__('Title'))
                                    ->required()
                                    ->columnSpan(2),

                                Textarea::make('description')
                                    ->label(__('Description'))
                                    ->rows(2)
                                    ->columnSpan(2),
                            ])
                            ->columns(2)
                            ->defaultItems(4)
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                self::getBackgroundFields(),
            ])
            ->columns(1);
    }

    protected static function videoBlock(): Block
    {
        return Block::make(CmsBlockType::VIDEO->value)
            ->label(CmsBlockType::VIDEO->getLabel())
            ->icon(CmsBlockType::VIDEO->getIcon())
            ->schema([
                Section::make(__('Content'))
                    ->schema([
                        TextInput::make('heading')
                            ->label(__('Heading'))
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('url')
                            ->label(__('Video URL'))
                            ->url()
                            ->required()
                            ->placeholder('https://youtube.com/watch?v=... or https://vimeo.com/...')
                            ->helperText(__('YouTube or Vimeo URL'))
                            ->columnSpan(2),

                        Select::make('aspect_ratio')
                            ->label(__('Aspect Ratio'))
                            ->options([
                                '16/9' => '16:9 (Standard)',
                                '4/3' => '4:3 (Classic)',
                                '21/9' => '21:9 (Ultrawide)',
                            ])
                            ->default('16/9')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                self::getBackgroundFields(),
            ])
            ->columns(1);
    }

    protected static function newsletterBlock(): Block
    {
        return Block::make(CmsBlockType::NEWSLETTER->value)
            ->label(CmsBlockType::NEWSLETTER->getLabel())
            ->icon(CmsBlockType::NEWSLETTER->getIcon())
            ->schema([
                Section::make(__('Content'))
                    ->schema([
                        TextInput::make('heading')
                            ->label(__('Heading'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Textarea::make('description')
                            ->label(__('Description'))
                            ->rows(2)
                            ->columnSpan(2),

                        TextInput::make('placeholder')
                            ->label(__('Email Placeholder'))
                            ->default(__('Enter your email'))
                            ->columnSpan(1),

                        TextInput::make('button_text')
                            ->label(__('Button Text'))
                            ->default(__('Subscribe'))
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                self::getBackgroundFields(),
            ])
            ->columns(1);
    }

    protected static function mapBlock(): Block
    {
        return Block::make(CmsBlockType::MAP->value)
            ->label(CmsBlockType::MAP->getLabel())
            ->icon(CmsBlockType::MAP->getIcon())
            ->schema([
                Section::make(__('Content'))
                    ->schema([
                        TextInput::make('heading')
                            ->label(__('Heading'))
                            ->maxLength(255)
                            ->columnSpan(2),

                        TextInput::make('address')
                            ->label(__('Address'))
                            ->required()
                            ->columnSpan(2),

                        TextInput::make('latitude')
                            ->label(__('Latitude'))
                            ->numeric()
                            ->columnSpan(1),

                        TextInput::make('longitude')
                            ->label(__('Longitude'))
                            ->numeric()
                            ->columnSpan(1),

                        TextInput::make('zoom')
                            ->label(__('Zoom Level'))
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(20)
                            ->default(14)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                self::getBackgroundFields(),
            ])
            ->columns(1);
    }

    /**
     * Get common background configuration fields
     * 
     * @return Section
     */
    protected static function getBackgroundFields(): Section
    {
        return Section::make(__('Background'))
            ->description(__('Configure the block background'))
            ->schema([
                Select::make('background_type')
                    ->label(__('Background Type'))
                    ->options(BackgroundType::options())
                    ->default(BackgroundType::NONE->value)
                    ->live()
                    ->columnSpan(2),

                // Solid Color
                ColorPicker::make('background_color')
                    ->label(__('Background Color'))
                    ->visible(fn ($get) => $get('background_type') === BackgroundType::COLOR->value)
                    ->columnSpan(1),

                TextInput::make('background_opacity')
                    ->label(__('Opacity'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(100)
                    ->suffix('%')
                    ->visible(fn ($get) => in_array($get('background_type'), [BackgroundType::COLOR->value, BackgroundType::GRADIENT->value, BackgroundType::IMAGE->value]))
                    ->columnSpan(1),

                // Gradient
                ColorPicker::make('gradient_from')
                    ->label(__('Gradient From'))
                    ->visible(fn ($get) => $get('background_type') === BackgroundType::GRADIENT->value)
                    ->columnSpan(1),

                ColorPicker::make('gradient_to')
                    ->label(__('Gradient To'))
                    ->visible(fn ($get) => $get('background_type') === BackgroundType::GRADIENT->value)
                    ->columnSpan(1),

                Select::make('gradient_direction')
                    ->label(__('Gradient Direction'))
                    ->options([
                        'to-r' => __('Left to Right'),
                        'to-l' => __('Right to Left'),
                        'to-t' => __('Bottom to Top'),
                        'to-b' => __('Top to Bottom'),
                        'to-br' => __('Top-Left to Bottom-Right'),
                        'to-bl' => __('Top-Right to Bottom-Left'),
                    ])
                    ->default('to-r')
                    ->visible(fn ($get) => $get('background_type') === BackgroundType::GRADIENT->value)
                    ->columnSpan(2),

                // Image
                FileUpload::make('background_image')
                    ->label(__('Background Image'))
                    ->image()
                    ->disk('public')
                    ->directory('cms-backgrounds')
                    ->visibility('public')
                    ->imageEditor()
                    ->visible(fn ($get) => $get('background_type') === BackgroundType::IMAGE->value)
                    ->columnSpan(2),

                Select::make('background_size')
                    ->label(__('Image Size'))
                    ->options([
                        'cover' => __('Cover'),
                        'contain' => __('Contain'),
                        'auto' => __('Auto'),
                    ])
                    ->default('cover')
                    ->visible(fn ($get) => $get('background_type') === BackgroundType::IMAGE->value)
                    ->columnSpan(1),

                Select::make('background_position')
                    ->label(__('Image Position'))
                    ->options([
                        'center' => __('Center'),
                        'top' => __('Top'),
                        'bottom' => __('Bottom'),
                        'left' => __('Left'),
                        'right' => __('Right'),
                    ])
                    ->default('center')
                    ->visible(fn ($get) => $get('background_type') === BackgroundType::IMAGE->value)
                    ->columnSpan(1),

                // Pattern
                Select::make('pattern_type')
                    ->label(__('Pattern Type'))
                    ->options([
                        'dots' => __('Dots'),
                        'grid' => __('Grid'),
                        'stripes' => __('Stripes'),
                        'waves' => __('Waves'),
                        'circles' => __('Circles'),
                        'zigzag' => __('Zigzag'),
                        'cross' => __('Cross'),
                        'hexagons' => __('Hexagons'),
                    ])
                    ->default('dots')
                    ->visible(fn ($get) => $get('background_type') === BackgroundType::PATTERN->value)
                    ->columnSpan(2),

                ColorPicker::make('pattern_background_color')
                    ->label(__('Background Color'))
                    ->default('#ffffff')
                    ->visible(fn ($get) => $get('background_type') === BackgroundType::PATTERN->value)
                    ->columnSpan(1),

                ColorPicker::make('pattern_color')
                    ->label(__('Pattern Color'))
                    ->default('#e5e7eb')
                    ->visible(fn ($get) => $get('background_type') === BackgroundType::PATTERN->value)
                    ->columnSpan(1),

                TextInput::make('pattern_opacity')
                    ->label(__('Pattern Opacity'))
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(100)
                    ->suffix('%')
                    ->visible(fn ($get) => $get('background_type') === BackgroundType::PATTERN->value)
                    ->columnSpan(1),

                TextInput::make('pattern_size')
                    ->label(__('Pattern Size'))
                    ->numeric()
                    ->minValue(5)
                    ->maxValue(200)
                    ->default(20)
                    ->suffix('px')
                    ->helperText(__('Size of the pattern tile'))
                    ->visible(fn ($get) => $get('background_type') === BackgroundType::PATTERN->value)
                    ->columnSpan(1),

                TextInput::make('pattern_spacing')
                    ->label(__('Pattern Spacing'))
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(100)
                    ->default(15)
                    ->suffix('px')
                    ->helperText(__('Distance between pattern elements'))
                    ->visible(fn ($get) => $get('background_type') === BackgroundType::PATTERN->value)
                    ->columnSpan(1),
            ])
            ->columns(2)
            ->collapsible()
            ->collapsed();
    }
}
