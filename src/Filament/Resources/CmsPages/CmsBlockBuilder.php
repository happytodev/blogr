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
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Happytodev\Blogr\Enums\CmsBlockType;
use Happytodev\Blogr\Enums\BackgroundType;
use Happytodev\Blogr\Filament\Forms\LinkFieldsTrait;

class CmsBlockBuilder
{
    use LinkFieldsTrait;
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
                self::transitionDiagonalBlock(),
                self::blogTitleBlock(),
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

                        Select::make('image_position')
                            ->label(__('Image Position'))
                            ->options([
                                'top' => __('Top (Behind Text)'),
                                'left' => __('Left of Text'),
                                'right' => __('Right of Text'),
                            ])
                            ->default('top')
                            ->columnSpan(1),

                        Select::make('image_max_width')
                            ->label(__('Image Max Width (when top)'))
                            ->options([
                                'max-w-sm' => __('Small (384px)'),
                                'max-w-md' => __('Medium (448px)'),
                                'max-w-lg' => __('Large (512px)'),
                                'max-w-xl' => __('Extra Large (576px)'),
                                'max-w-2xl' => __('2XL (672px)'),
                                'max-w-3xl' => __('3XL (768px)'),
                                'max-w-4xl' => __('4XL (896px)'),
                                'max-w-5xl' => __('5XL (1024px)'),
                                'max-w-full' => __('Full Width'),
                            ])
                            ->default('max-w-2xl')
                            ->helperText(__('Only applies when image position is "Top"'))
                            ->columnSpan(1),

                        TextInput::make('cta_text')
                            ->label(__('Button Text'))
                            ->maxLength(50)
                            ->columnSpan(1),

                        ...self::getLinkFieldsSchema(
                            linkTypeFieldName: 'cta_link_type',
                            urlFieldName: 'cta_url',
                            categoryIdFieldName: 'cta_category_id',
                            cmsPageIdFieldName: 'cta_cms_page_id',
                            includeBlogHome: true
                        ),

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

                        ...self::getLinkFieldsSchema(
                            linkTypeFieldName: 'button_link_type',
                            urlFieldName: 'button_url',
                            categoryIdFieldName: 'button_category_id',
                            cmsPageIdFieldName: 'button_cms_page_id',
                            includeBlogHome: true
                        ),

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
                            ->multiple()
                            ->reorderable()
                            ->image()
                            ->disk('public')
                            ->directory('cms-blocks/gallery')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(5120)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'])
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

                                ...self::getLinkFieldsSchema(
                                    linkTypeFieldName: 'cta_link_type',
                                    urlFieldName: 'cta_url',
                                    categoryIdFieldName: 'cta_category_id',
                                    cmsPageIdFieldName: 'cta_cms_page_id',
                                    includeBlogHome: true
                                ),

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
            ->description(__('Configure the block background for light and dark modes'))
            ->schema([
                // Left Column: Background + Text Shadow
                Group::make()
                    ->schema([
                        Tabs::make('background_mode')
                            ->tabs([
                                Tabs\Tab::make('Light Mode')
                                    ->icon('heroicon-o-sun')
                                    ->schema([
                                        Select::make('background_type')
                                            ->label(__('Background Type'))
                                            ->options(BackgroundType::options())
                                            ->default(BackgroundType::NONE->value)
                                            ->live()
                                            ->columnSpan(3),

                                        // Solid Color
                                        ColorPicker::make('background_color')
                                            ->label(__('Background Color'))
                                            ->visible(fn ($get) => $get('background_type') === BackgroundType::COLOR->value)
                                            ->columnSpan(2),

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
                                            ->columnSpan(1),

                                        // Image
                                        FileUpload::make('background_image')
                                            ->label(__('Background Image'))
                                            ->image()
                                            ->disk('public')
                                            ->directory('cms-backgrounds')
                                            ->visibility('public')
                                            ->imageEditor()
                                            ->visible(fn ($get) => $get('background_type') === BackgroundType::IMAGE->value)
                                            ->columnSpan(3),

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
                                            ->columnSpan(2),

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
                                            ->columnSpan(3),

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
                                            ->columnSpan(2),
                                    ])
                                    ->columns(3),

                                Tabs\Tab::make('Dark Mode')
                                    ->icon('heroicon-o-moon')
                                    ->schema([
                                Select::make('background_type_dark')
                                    ->label(__('Background Type'))
                                    ->options(BackgroundType::options())
                                    ->default(BackgroundType::NONE->value)
                                    ->live()
                                    ->columnSpan(3),

                                // Solid Color
                                ColorPicker::make('background_color_dark')
                                    ->label(__('Background Color'))
                                    ->visible(fn ($get) => $get('background_type_dark') === BackgroundType::COLOR->value)
                                    ->columnSpan(2),

                                TextInput::make('background_opacity_dark')
                                    ->label(__('Opacity'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->default(100)
                                    ->suffix('%')
                                    ->visible(fn ($get) => in_array($get('background_type_dark'), [BackgroundType::COLOR->value, BackgroundType::GRADIENT->value, BackgroundType::IMAGE->value]))
                                    ->columnSpan(1),

                                // Gradient
                                ColorPicker::make('gradient_from_dark')
                                    ->label(__('Gradient From'))
                                    ->visible(fn ($get) => $get('background_type_dark') === BackgroundType::GRADIENT->value)
                                    ->columnSpan(1),

                                ColorPicker::make('gradient_to_dark')
                                    ->label(__('Gradient To'))
                                    ->visible(fn ($get) => $get('background_type_dark') === BackgroundType::GRADIENT->value)
                                    ->columnSpan(1),

                                Select::make('gradient_direction_dark')
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
                                    ->visible(fn ($get) => $get('background_type_dark') === BackgroundType::GRADIENT->value)
                                    ->columnSpan(1),

                                // Image
                                FileUpload::make('background_image_dark')
                                    ->label(__('Background Image'))
                                    ->image()
                                    ->disk('public')
                                    ->directory('cms-backgrounds')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->visible(fn ($get) => $get('background_type_dark') === BackgroundType::IMAGE->value)
                                    ->columnSpan(3),

                                Select::make('background_size_dark')
                                    ->label(__('Image Size'))
                                    ->options([
                                        'cover' => __('Cover'),
                                        'contain' => __('Contain'),
                                        'auto' => __('Auto'),
                                    ])
                                    ->default('cover')
                                    ->visible(fn ($get) => $get('background_type_dark') === BackgroundType::IMAGE->value)
                                    ->columnSpan(1),

                                Select::make('background_position_dark')
                                    ->label(__('Image Position'))
                                    ->options([
                                        'center' => __('Center'),
                                        'top' => __('Top'),
                                        'bottom' => __('Bottom'),
                                        'left' => __('Left'),
                                        'right' => __('Right'),
                                    ])
                                    ->default('center')
                                    ->visible(fn ($get) => $get('background_type_dark') === BackgroundType::IMAGE->value)
                                    ->columnSpan(2),

                                // Pattern
                                Select::make('pattern_type_dark')
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
                                    ->visible(fn ($get) => $get('background_type_dark') === BackgroundType::PATTERN->value)
                                    ->columnSpan(3),

                                ColorPicker::make('pattern_background_color_dark')
                                    ->label(__('Background Color'))
                                    ->default('#ffffff')
                                    ->visible(fn ($get) => $get('background_type_dark') === BackgroundType::PATTERN->value)
                                    ->columnSpan(1),

                                ColorPicker::make('pattern_color_dark')
                                    ->label(__('Pattern Color'))
                                    ->default('#e5e7eb')
                                    ->visible(fn ($get) => $get('background_type_dark') === BackgroundType::PATTERN->value)
                                    ->columnSpan(1),

                                TextInput::make('pattern_opacity_dark')
                                    ->label(__('Pattern Opacity'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->default(100)
                                    ->suffix('%')
                                    ->visible(fn ($get) => $get('background_type_dark') === BackgroundType::PATTERN->value)
                                    ->columnSpan(1),

                                TextInput::make('pattern_size_dark')
                                    ->label(__('Pattern Size'))
                                    ->numeric()
                                    ->minValue(5)
                                    ->maxValue(200)
                                    ->default(20)
                                    ->suffix('px')
                                    ->helperText(__('Size of the pattern tile'))
                                    ->visible(fn ($get) => $get('background_type_dark') === BackgroundType::PATTERN->value)
                                    ->columnSpan(1),

                                TextInput::make('pattern_spacing_dark')
                                    ->label(__('Pattern Spacing'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(100)
                                    ->default(15)
                                    ->suffix('px')
                                    ->helperText(__('Distance between pattern elements'))
                                    ->visible(fn ($get) => $get('background_type_dark') === BackgroundType::PATTERN->value)
                                    ->columnSpan(2),
                            ])
                            ->columns(3),
                    ]),

                // Text Shadow (shared between modes)
                Toggle::make('text_shadow')
                    ->label(__('Add Text Shadow'))
                    ->helperText(__('Add drop shadow to text for better readability'))
                    ->default(false)
                    ->live(),

                Select::make('shadow_intensity')
                    ->label(__('Shadow Intensity'))
                    ->options([
                        'light' => __('Light'),
                        'medium' => __('Medium'),
                        'heavy' => __('Heavy'),
                    ])
                    ->default('medium')
                    ->visible(fn ($get) => $get('text_shadow') === true),
            ])
            ->columnSpan(1), // Left group takes 1 column

            // Right Column: Text Colors
            Group::make()
                ->schema([
                    // Text Colors with Light/Dark tabs
                    Tabs::make('text_colors_mode')
                        ->tabs([
                            Tabs\Tab::make('Light Mode')
                            ->icon('heroicon-o-sun')
                            ->schema([
                                ColorPicker::make('heading_color')
                                    ->label(__('Headings Color'))
                                    ->helperText(__('Color for h1, h2, h3, etc.'))
                                    ->columnSpan(1),

                                ColorPicker::make('text_color')
                                    ->label(__('Body Text Color'))
                                    ->helperText(__('Color for paragraphs and list items'))
                                    ->columnSpan(1),

                                ColorPicker::make('subtitle_color')
                                    ->label(__('Subtitle Color'))
                                    ->helperText(__('Color for subtitles and secondary text'))
                                    ->columnSpan(1),
                            ])
                            ->columns(2),

                        Tabs\Tab::make('Dark Mode')
                            ->icon('heroicon-o-moon')
                            ->schema([
                                ColorPicker::make('heading_color_dark')
                                    ->label(__('Headings Color'))
                                    ->helperText(__('Color for h1, h2, h3, etc.'))
                                    ->columnSpan(1),

                                ColorPicker::make('text_color_dark')
                                    ->label(__('Body Text Color'))
                                    ->helperText(__('Color for paragraphs and list items'))
                                    ->columnSpan(1),

                                ColorPicker::make('subtitle_color_dark')
                                    ->label(__('Subtitle Color'))
                                    ->helperText(__('Color for subtitles and secondary text'))
                                    ->columnSpan(1),
                            ])
                            ->columns(2),
                        ])
                ])
                ->columnSpan(1), // Right group takes 1 column
            ])
            ->columns(2)
            ->collapsible()
            ->collapsed();
    }

    protected static function waveSeparatorBlock(): Block
    {
        return Block::make(CmsBlockType::WAVE_SEPARATOR->value)
            ->label(CmsBlockType::WAVE_SEPARATOR->getLabel())
            ->icon(CmsBlockType::WAVE_SEPARATOR->getIcon())
            ->schema([
                Section::make(__('Wave Settings'))
                    ->schema([
                        Select::make('position')
                            ->label(__('Position'))
                            ->options([
                                'top' => __('Top'),
                                'bottom' => __('Bottom'),
                                'both' => __('Both (Top & Bottom)'),
                            ])
                            ->default('bottom')
                            ->required()
                            ->columnSpan(1),

                        Select::make('height')
                            ->label(__('Height'))
                            ->options([
                                'short' => __('Short'),
                                'normal' => __('Normal'),
                                'tall' => __('Tall'),
                            ])
                            ->default('normal')
                            ->columnSpan(1),

                        Select::make('wave_mode')
                            ->label(__('Wave Mode'))
                            ->options([
                                'auto' => __('Auto (Intelligent Colors)'),
                                'manual' => __('Manual (Custom Colors)'),
                            ])
                            ->default('auto')
                            ->required()
                            ->helperText(__('Auto mode intelligently blends colors from adjacent blocks. Manual mode lets you customize colors.'))
                            ->columnSpan(2),

                        Select::make('wave_style')
                            ->label(__('Wave Style'))
                            ->options([
                                'wave' => __('Wave 1'),
                                'wave-2' => __('Wave 2'),
                                'wave-3' => __('Wave 3'),
                                'curve' => __('Curve'),
                            ])
                            ->default('wave-3')
                            ->required()
                            ->columnSpan(1),

                        Select::make('wave_amplitude')
                            ->label(__('Wave Amplitude'))
                            ->options([
                                'low' => __('Low'),
                                'medium' => __('Medium'),
                                'high' => __('High'),
                            ])
                            ->default('medium')
                            ->required()
                            ->columnSpan(1),

                        ColorPicker::make('wave_color_light')
                            ->label(__('Wave Color (Light Theme)'))
                            ->default('#d946ef')
                            ->columnSpan(1)
                            ->visible(fn(callable $get) => $get('wave_mode') === 'manual'),

                        ColorPicker::make('wave_color_dark')
                            ->label(__('Wave Color (Dark Theme)'))
                            ->default('#ec4899')
                            ->columnSpan(1)
                            ->visible(fn(callable $get) => $get('wave_mode') === 'manual'),

                        Select::make('wave_fill_style')
                            ->label(__('Fill Style'))
                            ->options([
                                'fill' => __('Fill'),
                                'stroke' => __('Stroke'),
                            ])
                            ->default('fill')
                            ->columnSpan(1)
                            ->visible(fn(callable $get) => $get('wave_mode') === 'manual'),

                        TextInput::make('wave_opacity')
                            ->label(__('Opacity'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(100)
                            ->suffix('%')
                            ->helperText(__('Set the transparency: 0% (invisible) to 100% (opaque)'))
                            ->columnSpan(1)
                            ->visible(fn(callable $get) => $get('wave_mode') === 'manual'),
                    ])
                    ->columns(2),

                self::getBackgroundFields(),
            ])
            ->columns(1);
    }

    protected static function transitionDiagonalBlock(): Block
    {
        return Block::make(CmsBlockType::TRANSITION_DIAGONAL->value)
            ->label(CmsBlockType::TRANSITION_DIAGONAL->getLabel())
            ->icon(CmsBlockType::TRANSITION_DIAGONAL->getIcon())
            ->schema([
                Section::make(__('⚠️ Important'))
                    ->description(__('Transitions work best when the NEXT block has a solid background color (not a gradient). If the next block has a gradient, the transition may not render as expected.'))
                    ->schema([]),

                Section::make(__('Shape & Style'))
                    ->schema([
                        Select::make('shape')
                            ->label(__('Shape'))
                            ->options([
                                'wavy' => __('Wavy (Smooth curves)'),
                                'zigzag' => __('Zigzag (Sharp angles)'),
                                'diagonal' => __('Diagonal (Simple angle)'),
                                'smooth' => __('Smooth (Organic curves)'),
                            ])
                            ->default('wavy')
                            ->required()
                            ->live()
                            ->columnSpan(2),

                        Select::make('diagonal_direction')
                            ->label(__('Diagonal Direction'))
                            ->options([
                                'left' => __('Left to Right ↗'),
                                'right' => __('Right to Left ↖'),
                            ])
                            ->default('left')
                            ->visible(fn ($get) => $get('shape') === 'diagonal')
                            ->dehydrated()
                            ->columnSpan(1),

                        TextInput::make('amplitude')
                            ->label(__('Wave Amplitude (px)'))
                            ->numeric()
                            ->minValue(10)
                            ->maxValue(100)
                            ->default(40)
                            ->helperText(__('Height of the wave peak. Higher values create more dramatic transitions.'))
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }

    protected static function transitionClippathBlock(): Block
    {
        return Block::make(CmsBlockType::TRANSITION_CLIPPATH->value)
            ->label(CmsBlockType::TRANSITION_CLIPPATH->getLabel())
            ->icon(CmsBlockType::TRANSITION_CLIPPATH->getIcon())
            ->schema([
                Section::make(__('Clip Path Transition Settings'))
                    ->description(__('Creates a decorative clip-path transition between blocks'))
                    ->schema([
                        Select::make('height')
                            ->label(__('Height'))
                            ->options([
                                'short' => __('Short'),
                                'normal' => __('Normal'),
                                'tall' => __('Tall'),
                            ])
                            ->default('normal')
                            ->required(),

                        Select::make('clip_style')
                            ->label(__('Clip Style'))
                            ->options([
                                'wavy' => __('Wavy'),
                                'zigzag' => __('Zigzag'),
                                'smooth' => __('Smooth Angle'),
                            ])
                            ->default('wavy')
                            ->required(),
                    ]),
            ])
            ->columns(1);
    }

    protected static function transitionMarginBlock(): Block
    {
        return Block::make(CmsBlockType::TRANSITION_MARGIN->value)
            ->label(CmsBlockType::TRANSITION_MARGIN->getLabel())
            ->icon(CmsBlockType::TRANSITION_MARGIN->getIcon())
            ->schema([
                Section::make(__('Simple Transition Settings'))
                    ->description(__('Creates a simple gradient overlap transition'))
                    ->schema([
                        Select::make('height')
                            ->label(__('Height'))
                            ->options([
                                'short' => __('Short'),
                                'normal' => __('Normal'),
                                'tall' => __('Tall'),
                            ])
                            ->default('normal')
                            ->required(),
                    ]),
            ])
            ->columns(1);
    }

    protected static function transitionAnimationBlock(): Block
    {
        return Block::make(CmsBlockType::TRANSITION_ANIMATION->value)
            ->label(CmsBlockType::TRANSITION_ANIMATION->getLabel())
            ->icon(CmsBlockType::TRANSITION_ANIMATION->getIcon())
            ->schema([
                Section::make(__('Animated Transition Settings'))
                    ->description(__('Creates an animated transition with entrance effect'))
                    ->schema([
                        Select::make('height')
                            ->label(__('Height'))
                            ->options([
                                'short' => __('Short'),
                                'normal' => __('Normal'),
                                'tall' => __('Tall'),
                            ])
                            ->default('normal')
                            ->required(),

                        Select::make('animation_type')
                            ->label(__('Animation Type'))
                            ->options([
                                'fade-slide' => __('Fade & Slide'),
                                'scale' => __('Scale'),
                                'rotate' => __('Rotate'),
                            ])
                            ->default('fade-slide')
                            ->required(),
                    ]),
            ])
            ->columns(1);
    }

    protected static function blogTitleBlock(): Block
    {
        return Block::make(CmsBlockType::BLOG_TITLE->value)
            ->label(CmsBlockType::BLOG_TITLE->getLabel())
            ->icon(CmsBlockType::BLOG_TITLE->getIcon())
            ->schema([
                Section::make(__('Title Content'))
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Title'))
                            ->default(__('Blog'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Textarea::make('description')
                            ->label(__('Description (Optional)'))
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpan(2),

                        Toggle::make('enabled')
                            ->label(__('Display This Section'))
                            ->default(true)
                            ->columnSpan(1),

                        TextInput::make('padding_top')
                            ->label(__('Top Padding'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(200)
                            ->default(40)
                            ->suffix('px')
                            ->columnSpan(1),

                        TextInput::make('padding_bottom')
                            ->label(__('Bottom Padding'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(200)
                            ->default(40)
                            ->suffix('px')
                            ->columnSpan(1),

                        Select::make('text_alignment')
                            ->label(__('Text Alignment'))
                            ->options([
                                'left' => __('Left'),
                                'center' => __('Center'),
                                'right' => __('Right'),
                            ])
                            ->default('center')
                            ->columnSpan(1),
                    ]),

                self::getBackgroundFields(),
            ])
            ->columns(1);
    }
}
