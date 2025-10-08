<?php

namespace Happytodev\Blogr\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\File;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Artisan;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Concerns\InteractsWithForms;

class BlogrSettings extends Page
{
    use InteractsWithForms;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string | null $navigationLabel = 'Settings';

    protected static string | \UnitEnum | null $navigationGroup = 'Blogr';

    protected static ?int $navigationSort = 4;

    protected string $view = 'blogr::filament.pages.blogr-settings';

    // Form properties
    public ?int $posts_per_page = null;
    public ?string $route_prefix = null;
    public ?bool $route_frontend_enabled = null;
    public ?string $colors_primary = null;
    public ?string $blog_index_cards_colors_background = null;
    public ?string $blog_index_cards_colors_top_border = null;
    public ?int $reading_speed_words_per_minute = null;
    public ?string $reading_time_text_format = null;
    public ?string $reading_time_text_en = null;
    public ?string $reading_time_text_fr = null;
    public ?string $reading_time_text_es = null;
    public ?string $reading_time_text_de = null;
    public ?bool $reading_time_enabled = null;
    
    // SEO Settings - Translatable fields
    // @todo dynamically generate these based on available locales
    public ?string $seo_site_name_en = null;
    public ?string $seo_site_name_fr = null;
    public ?string $seo_site_name_es = null;
    public ?string $seo_site_name_de = null;
    public ?string $seo_default_title_en = null;
    public ?string $seo_default_title_fr = null;
    public ?string $seo_default_title_es = null;
    public ?string $seo_default_title_de = null;
    public ?string $seo_default_description_en = null;
    public ?string $seo_default_description_fr = null;
    public ?string $seo_default_description_es = null;
    public ?string $seo_default_description_de = null;
    public ?string $seo_default_keywords_en = null;
    public ?string $seo_default_keywords_fr = null;
    public ?string $seo_default_keywords_es = null;
    public ?string $seo_default_keywords_de = null;
    
    // SEO Settings - Legacy/non-translatable
    public ?string $seo_site_name = null;
    public ?string $seo_default_title = null;
    public ?string $seo_default_description = null;
    public ?string $seo_twitter_handle = null;
    public ?string $seo_facebook_app_id = null;
    public ?string $seo_og_image = null;
    public ?int $seo_og_image_width = null;
    public ?int $seo_og_image_height = null;
    public ?bool $seo_structured_data_enabled = null;
    public ?string $seo_structured_data_organization_name = null;
    public ?string $seo_structured_data_organization_url = null;
    public ?string $seo_structured_data_organization_logo = null;
    public ?bool $toc_enabled = null;
    public ?bool $toc_strict_mode = null;
    public ?bool $locales_enabled = null;
    public ?string $locales_default = null;
    public ?string $locales_available = null;
    public ?bool $series_enabled = null;
    public ?string $series_default_image = null;
    
    // UI Settings - Navigation
    public ?bool $navigation_enabled = null;
    public ?bool $navigation_sticky = null;
    public ?bool $navigation_show_logo = null;
    public ?bool $navigation_show_language_switcher = null;
    public ?bool $navigation_show_theme_switcher = null;
    
    // UI Settings - Footer
    public ?bool $footer_enabled = null;
    public ?string $footer_text = null;
    public ?bool $footer_show_social_links = null;
    public ?string $footer_twitter = null;
    public ?string $footer_github = null;
    public ?string $footer_linkedin = null;
    public ?string $footer_facebook = null;
    
    // UI Settings - Theme
    public ?string $theme_default = null;
    public ?string $theme_primary_color = null;
    
    // UI Settings - Posts
    public ?string $posts_default_image = null;
    public ?bool $posts_show_language_switcher = null;

    public function mount(): void
    {
        // Load current config values
        $config = config('blogr', []);

        // Set form properties from config
        $this->posts_per_page = $config['posts_per_page'] ?? 10;
        $this->route_prefix = $config['route']['prefix'] ?? 'blog';
        $this->route_frontend_enabled = $config['route']['frontend']['enabled'] ?? true;
        $this->colors_primary = $config['colors']['primary'] ?? '#3b82f6';
        $this->blog_index_cards_colors_background = $config['blog_index']['cards']['colors']['background'] ?? 'bg-white';
        $this->blog_index_cards_colors_top_border = $config['blog_index']['cards']['colors']['top_border'] ?? 'border-t-4 border-blue-500';
        $this->reading_speed_words_per_minute = $config['reading_speed']['words_per_minute'] ?? 200;
        
        // Load reading time text format (supports both string and array formats)
        $textFormat = $config['reading_time']['text_format'] ?? 'Reading time: {time}';
        $availableLocales = $config['locales']['available'] ?? ['en'];
        
        if (is_array($textFormat)) {
            foreach ($availableLocales as $locale) {
                $property = "reading_time_text_{$locale}";
                $this->$property = $textFormat[$locale] ?? match($locale) {
                    'en' => 'Reading time: {time}',
                    'fr' => 'Temps de lecture : {time}',
                    'es' => 'Tiempo de lectura: {time}',
                    'de' => 'Lesezeit: {time}',
                    default => 'Reading time: {time}',
                };
            }
        } else {
            // Legacy string format - set for all locales
            foreach ($availableLocales as $locale) {
                $property = "reading_time_text_{$locale}";
                $this->$property = match($locale) {
                    'en' => $textFormat,
                    'fr' => 'Temps de lecture : {time}',
                    'es' => 'Tiempo de lectura: {time}',
                    'de' => 'Lesezeit: {time}',
                    default => $textFormat,
                };
            }
        }
        
        $this->reading_time_enabled = $config['reading_time']['enabled'] ?? true;
        
        // Load SEO settings - translatable fields
        $availableLocales = $config['locales']['available'] ?? ['en'];
        
        // Site Name
        $seoSiteName = $config['seo']['site_name'] ?? env('APP_NAME', 'My Blog');
        if (is_array($seoSiteName)) {
            foreach ($availableLocales as $locale) {
                $property = "seo_site_name_{$locale}";
                $this->$property = $seoSiteName[$locale] ?? env('APP_NAME', 'My Blog');
            }
        } else {
            // Legacy string format - use for all locales
            foreach ($availableLocales as $locale) {
                $property = "seo_site_name_{$locale}";
                $this->$property = $seoSiteName;
            }
        }
        
        // Default Title
        $seoDefaultTitle = $config['seo']['default_title'] ?? 'Blog';
        if (is_array($seoDefaultTitle)) {
            foreach ($availableLocales as $locale) {
                $property = "seo_default_title_{$locale}";
                $this->$property = $seoDefaultTitle[$locale] ?? 'Blog';
            }
        } else {
            foreach ($availableLocales as $locale) {
                $property = "seo_default_title_{$locale}";
                $this->$property = $seoDefaultTitle;
            }
        }
        
        // Default Description
        $seoDefaultDescription = $config['seo']['default_description'] ?? 'Discover our latest articles and insights';
        if (is_array($seoDefaultDescription)) {
            foreach ($availableLocales as $locale) {
                $property = "seo_default_description_{$locale}";
                $this->$property = $seoDefaultDescription[$locale] ?? 'Discover our latest articles and insights';
            }
        } else {
            foreach ($availableLocales as $locale) {
                $property = "seo_default_description_{$locale}";
                $this->$property = $seoDefaultDescription;
            }
        }
        
        // Default Keywords
        $seoDefaultKeywords = $config['seo']['default_keywords'] ?? 'blog, articles, news, insights';
        if (is_array($seoDefaultKeywords)) {
            foreach ($availableLocales as $locale) {
                $property = "seo_default_keywords_{$locale}";
                $this->$property = $seoDefaultKeywords[$locale] ?? 'blog, articles, news, insights';
            }
        } else {
            foreach ($availableLocales as $locale) {
                $property = "seo_default_keywords_{$locale}";
                $this->$property = $seoDefaultKeywords;
            }
        }
        
        // Legacy non-translatable fields (for backward compatibility)
        $this->seo_site_name = is_array($seoSiteName) ? ($seoSiteName['en'] ?? env('APP_NAME', 'My Blog')) : $seoSiteName;
        $this->seo_default_title = is_array($seoDefaultTitle) ? ($seoDefaultTitle['en'] ?? 'Blog') : $seoDefaultTitle;
        $this->seo_default_description = is_array($seoDefaultDescription) ? ($seoDefaultDescription['en'] ?? 'Discover our latest articles and insights') : $seoDefaultDescription;
        $this->seo_twitter_handle = $config['seo']['twitter_handle'] ?? '';
        $this->seo_facebook_app_id = $config['seo']['facebook_app_id'] ?? '';
        $this->seo_og_image = $config['seo']['og']['image'] ?? '';
        $this->seo_og_image_width = $config['seo']['og']['image_width'] ?? null;
        $this->seo_og_image_height = $config['seo']['og']['image_height'] ?? null;
        $this->seo_structured_data_enabled = $config['seo']['structured_data']['enabled'] ?? true;
        $this->seo_structured_data_organization_name = $config['seo']['structured_data']['organization']['name'] ?? '';
        $this->seo_structured_data_organization_url = $config['seo']['structured_data']['organization']['url'] ?? '';
        $this->seo_structured_data_organization_logo = $config['seo']['structured_data']['organization']['logo'] ?? '';
        $this->toc_enabled = $config['toc']['enabled'] ?? true;
        $this->toc_strict_mode = $config['toc']['strict_mode'] ?? false;
        $this->locales_enabled = $config['locales']['enabled'] ?? false;
        $this->locales_default = $config['locales']['default'] ?? 'en';
        $this->locales_available = is_array($config['locales']['available'] ?? [])
            ? implode(', ', $config['locales']['available'])
            : 'en, fr, es, de';
        $this->series_enabled = $config['series']['enabled'] ?? true;
        $this->series_default_image = $config['series']['default_image'] ?? '/vendor/blogr/images/default-series.svg';
        
        // UI Settings
        $this->navigation_enabled = $config['ui']['navigation']['enabled'] ?? true;
        $this->navigation_sticky = $config['ui']['navigation']['sticky'] ?? true;
        $this->navigation_show_logo = $config['ui']['navigation']['show_logo'] ?? true;
        $this->navigation_show_language_switcher = $config['ui']['navigation']['show_language_switcher'] ?? true;
        $this->navigation_show_theme_switcher = $config['ui']['navigation']['show_theme_switcher'] ?? true;
        
        $this->footer_enabled = $config['ui']['footer']['enabled'] ?? true;
        $this->footer_text = $config['ui']['footer']['text'] ?? '© ' . date('Y') . ' My Blog. All rights reserved.';
        $this->footer_show_social_links = $config['ui']['footer']['show_social_links'] ?? false;
        $this->footer_twitter = $config['ui']['footer']['social_links']['twitter'] ?? '';
        $this->footer_github = $config['ui']['footer']['social_links']['github'] ?? '';
        $this->footer_linkedin = $config['ui']['footer']['social_links']['linkedin'] ?? '';
        $this->footer_facebook = $config['ui']['footer']['social_links']['facebook'] ?? '';
        
        $this->theme_default = $config['ui']['theme']['default'] ?? 'light';
        $this->theme_primary_color = $config['ui']['theme']['primary_color'] ?? '#3b82f6';
        
        $this->posts_default_image = $config['ui']['posts']['default_image'] ?? null;
        $this->posts_show_language_switcher = $config['ui']['posts']['show_language_switcher'] ?? true;
    }

    protected function getFormSchema(): array
    {
        return [
                Section::make('General Settings')
                    ->description('Basic blog configuration')
                    ->schema([
                        TextInput::make('posts_per_page')
                            ->label('Posts Per Page')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(100)
                            ->required(),
                        TextInput::make('route_prefix')
                            ->label('Route Prefix')
                            ->placeholder('blog')
                            ->helperText('URL prefix for blog routes')
                            ->required(),
                        Toggle::make('route_frontend_enabled')
                            ->label('Enable Frontend Routes')
                            ->helperText('Enable frontend routes for the blog')
                            ->default(true)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Appearance')
                    ->description('Visual customization options')
                    ->schema([
                        ColorPicker::make('colors_primary')
                            ->label('Primary Color')
                            ->default('#FA2C36')
                            ->required(),

                        TextInput::make('blog_index_cards_colors_background')
                            ->label('Card Background Color')
                            ->placeholder('bg-green-50')
                            ->helperText('Tailwind CSS class for card background'),

                        TextInput::make('blog_index_cards_colors_top_border')
                            ->label('Card Border Color')
                            ->placeholder('border-green-600')
                            ->helperText('Tailwind CSS class for card top border'),
                    ])
                    ->columns(3),

                Section::make('Reading Time')
                    ->description('Reading time calculation and display settings')
                    ->schema(function () {
                        $availableLocales = config('blogr.locales.available', ['en']);
                        $localeNames = [
                            'en' => 'English',
                            'fr' => 'Français',
                            'es' => 'Español',
                            'de' => 'Deutsch',
                        ];
                        
                        $fields = [
                            Toggle::make('reading_time_enabled')
                                ->label('Enable Reading Time Display')
                                ->default(true)
                                ->columnSpan(2),
                            TextInput::make('reading_speed_words_per_minute')
                                ->label('Words Per Minute')
                                ->numeric()
                                ->minValue(100)
                                ->default(200)
                                ->maxValue(400)
                                ->step(50)
                                ->helperText('Average reading speed for calculating reading time')
                                ->required()
                                ->columnSpan(2),
                        ];
                        
                        // Add text inputs for each available locale
                        foreach ($availableLocales as $locale) {
                            $localeName = $localeNames[$locale] ?? strtoupper($locale);
                            $fields[] = TextInput::make("reading_time_text_{$locale}")
                                ->label("Reading Time Text ({$localeName})")
                                ->placeholder(match($locale) {
                                    'en' => 'Reading time: {time}',
                                    'fr' => 'Temps de lecture : {time}',
                                    'es' => 'Tiempo de lectura: {time}',
                                    'de' => 'Lesezeit: {time}',
                                    default => 'Reading time: {time}',
                                })
                                ->helperText('Use {time} as placeholder for the reading time')
                                ->required();
                        }
                        
                        return $fields;
                    })
                    ->columns(2),

                Section::make('SEO Settings')
                    ->description('Search engine optimization configuration')
                    ->schema(function () {
                        $availableLocales = config('blogr.locales.available', ['en']);
                        $localeNames = [
                            'en' => 'English',
                            'fr' => 'Français',
                            'es' => 'Español',
                            'de' => 'Deutsch',
                        ];
                        
                        $fields = [];
                        
                        // Add translatable fields for each locale
                        foreach ($availableLocales as $locale) {
                            $localeName = $localeNames[$locale] ?? strtoupper($locale);
                            
                            $fields[] = TextInput::make("seo_site_name_{$locale}")
                                ->label("Site Name ({$localeName})")
                                ->placeholder('My Blog')
                                ->required();
                            
                            $fields[] = TextInput::make("seo_default_title_{$locale}")
                                ->label("Default Title ({$localeName})")
                                ->placeholder('Blog')
                                ->required();
                            
                            $fields[] = Textarea::make("seo_default_description_{$locale}")
                                ->label("Default Description ({$localeName})")
                                ->placeholder('Discover our latest articles and insights')
                                ->rows(2)
                                ->required()
                                ->columnSpan(2);
                            
                            $fields[] = Textarea::make("seo_default_keywords_{$locale}")
                                ->label("Default Keywords ({$localeName})")
                                ->placeholder('blog, articles, news, insights')
                                ->rows(2)
                                ->helperText('Comma-separated keywords for SEO meta tags')
                                ->required()
                                ->columnSpan(2);
                        }
                        
                        $fields[] = TextInput::make('seo_twitter_handle')
                            ->label('Twitter Handle')
                            ->placeholder('@yourhandle');
                        
                        $fields[] = TextInput::make('seo_facebook_app_id')
                            ->label('Facebook App ID');
                        
                        return $fields;
                    })
                    ->columns(2),

                Section::make('Structured Data')
                    ->description('Schema.org structured data settings')
                    ->schema([
                        Toggle::make('seo_structured_data_enabled')
                            ->label('Enable Structured Data')
                            ->default(true),
                        TextInput::make('seo_structured_data_organization_name')
                            ->label('Organization Name')
                            ->placeholder('My Blog'),
                        TextInput::make('seo_structured_data_organization_url')
                            ->label('Organization URL')
                            ->placeholder('https://yourwebsite.com')
                            ->url(),
                        TextInput::make('seo_structured_data_organization_logo')
                            ->label('Organization Logo')
                            ->placeholder('https://yourwebsite.com/images/logo.png')
                            ->url(),
                    ])
                    ->columns(2),

                Section::make('Series Settings')
                    ->description('Configure blog series and default images')
                    ->schema([
                        Toggle::make('series_enabled')
                            ->label('Enable Series')
                            ->default(true)
                            ->helperText('Allow grouping blog posts into series'),
                        TextInput::make('series_default_image')
                            ->label('Default Series Image')
                            ->required()
                            ->default('/vendor/blogr/images/default-series.svg')
                            ->helperText('Path to the default image for series without a custom photo (e.g., /vendor/blogr/images/default-series.svg or /images/my-default.jpg)')
                            ->columnSpan(2),
                    ])
                    ->columns(2),

                Section::make('Multilingual Settings')
                    ->description('Configure available locales and default locale for translations')
                    ->schema([
                        Toggle::make('locales_enabled')
                            ->label('Enable Localized Routes')
                            ->default(false)
                            ->helperText('Enable URL structure like /{locale}/blog/... (e.g., /en/blog/my-post, /fr/blog/mon-article)'),
                        TextInput::make('locales_default')
                            ->label('Default Locale')
                            ->required()
                            ->default('en')
                            ->helperText('The default locale used when no translation is available'),
                        Textarea::make('locales_available')
                            ->label('Available Locales')
                            ->required()
                            ->default('en, fr, es, de')
                            ->rows(2)
                            ->helperText('Comma-separated list of available locales (e.g., en, fr, es, de)'),
                    ])
                    ->columns(2),

                Section::make('Navigation Bar')
                    ->description('Configure the top navigation bar appearance and behavior')
                    ->schema([
                        Toggle::make('navigation_enabled')
                            ->label('Enable Navigation Bar')
                            ->default(true)
                            ->helperText('Show the navigation bar at the top of every page'),
                        
                        Toggle::make('navigation_sticky')
                            ->label('Sticky Navigation')
                            ->default(true)
                            ->helperText('Keep navigation bar visible when scrolling'),
                        
                        Toggle::make('navigation_show_logo')
                            ->label('Show Site Logo/Name')
                            ->default(true)
                            ->helperText('Display your site name in the navigation bar'),
                        
                        Toggle::make('navigation_show_language_switcher')
                            ->label('Show Language Switcher')
                            ->default(true)
                            ->helperText('Allow users to switch between available languages'),
                        
                        Toggle::make('navigation_show_theme_switcher')
                            ->label('Show Theme Switcher')
                            ->default(true)
                            ->helperText('Allow users to switch between light/dark/auto themes'),
                    ])
                    ->columns(2),

                Section::make('Footer Configuration')
                    ->description('Customize your blog footer appearance and content')
                    ->schema([
                        Toggle::make('footer_enabled')
                            ->label('Enable Footer')
                            ->default(true)
                            ->live()
                            ->helperText('Show footer at the bottom of every page'),
                        
                        Textarea::make('footer_text')
                            ->label('Footer Text')
                            ->default('© ' . date('Y') . ' My Blog. All rights reserved.')
                            ->helperText('Supports HTML. Use <br> for line breaks.')
                            ->rows(3)
                            ->visible(fn (Get $get) => $get('footer_enabled'))
                            ->columnSpanFull(),
                        
                        Toggle::make('footer_show_social_links')
                            ->label('Show Social Media Links')
                            ->default(false)
                            ->live()
                            ->helperText('Display social media icons in footer')
                            ->visible(fn (Get $get) => $get('footer_enabled')),
                        
                        TextInput::make('footer_twitter')
                            ->label('Twitter/X URL')
                            ->url()
                            ->placeholder('https://twitter.com/yourusername')
                            ->visible(fn (Get $get) => $get('footer_enabled') && $get('footer_show_social_links')),
                        
                        TextInput::make('footer_github')
                            ->label('GitHub URL')
                            ->url()
                            ->placeholder('https://github.com/yourusername')
                            ->visible(fn (Get $get) => $get('footer_enabled') && $get('footer_show_social_links')),
                        
                        TextInput::make('footer_linkedin')
                            ->label('LinkedIn URL')
                            ->url()
                            ->placeholder('https://linkedin.com/in/yourusername')
                            ->visible(fn (Get $get) => $get('footer_enabled') && $get('footer_show_social_links')),
                        
                        TextInput::make('footer_facebook')
                            ->label('Facebook URL')
                            ->url()
                            ->placeholder('https://facebook.com/yourusername')
                            ->visible(fn (Get $get) => $get('footer_enabled') && $get('footer_show_social_links')),
                    ])
                    ->columns(2),

                Section::make('Theme Settings')
                    ->description('Configure default theme and appearance')
                    ->schema([
                        Select::make('theme_default')
                            ->label('Default Theme')
                            ->options([
                                'light' => 'Light Mode',
                                'dark' => 'Dark Mode',
                                'auto' => 'Auto (System Preference)',
                            ])
                            ->default('light')
                            ->helperText('Users can override this in their browser'),
                        
                        ColorPicker::make('theme_primary_color')
                            ->label('Primary Color')
                            ->default('#3b82f6')
                            ->helperText('Main accent color used throughout the blog'),
                    ])
                    ->columns(2),

                Section::make('Post Display Settings')
                    ->description('Configure how blog posts are displayed')
                    ->schema([
                        FileUpload::make('posts_default_image')
                            ->label('Default Post Image')
                            ->image()
                            ->imageEditor()
                            ->directory('blog/defaults')
                            ->visibility('public')
                            ->helperText('Used when a post has no featured image')
                            ->acceptedFileTypes(['image/*'])
                            ->maxSize(2048)
                            ->columnSpanFull(),
                        
                        Toggle::make('posts_show_language_switcher')
                            ->label('Show Language Availability')
                            ->default(true)
                            ->helperText('Display available translations on post pages'),
                    ])
                    ->columns(2),

                Section::make('Table of Contents')
                    ->description('Table of contents configuration for blog posts')
                    ->schema([
                        Toggle::make('toc_enabled')
                            ->label('Enable Table of Contents by Default')
                            ->default(true)
                            ->helperText('Enable TOC globally. Individual posts can override this unless strict mode is enabled.'),
                        Toggle::make('toc_strict_mode')
                            ->label('Strict Mode')
                            ->default(false)
                            ->helperText('When enabled, individual posts cannot override the global TOC setting.'),
                    ]),
            ];
    }

    /**
     * Get reading time text formats for available locales
     *
     * @return array
     */
    private function getReadingTimeTextFormats(): array
    {
        $availableLocales = array_map('trim', explode(',', $this->locales_available ?? 'en'));
        $formats = [];
        
        foreach ($availableLocales as $locale) {
            $property = "reading_time_text_{$locale}";
            if (property_exists($this, $property) && $this->$property) {
                $formats[$locale] = $this->$property;
            }
        }
        
        return $formats;
    }

    private function getSeoSiteNames(): array
    {
        $availableLocales = array_map('trim', explode(',', $this->locales_available ?? 'en'));
        $names = [];
        
        foreach ($availableLocales as $locale) {
            $property = "seo_site_name_{$locale}";
            if (property_exists($this, $property) && $this->$property) {
                $names[$locale] = $this->$property;
            }
        }
        
        return $names;
    }

    private function getSeoDefaultTitles(): array
    {
        $availableLocales = array_map('trim', explode(',', $this->locales_available ?? 'en'));
        $titles = [];
        
        foreach ($availableLocales as $locale) {
            $property = "seo_default_title_{$locale}";
            if (property_exists($this, $property) && $this->$property) {
                $titles[$locale] = $this->$property;
            }
        }
        
        return $titles;
    }

    private function getSeoDefaultDescriptions(): array
    {
        $availableLocales = array_map('trim', explode(',', $this->locales_available ?? 'en'));
        $descriptions = [];
        
        foreach ($availableLocales as $locale) {
            $property = "seo_default_description_{$locale}";
            if (property_exists($this, $property) && $this->$property) {
                $descriptions[$locale] = $this->$property;
            }
        }
        
        return $descriptions;
    }

    private function getSeoDefaultKeywords(): array
    {
        $availableLocales = array_map('trim', explode(',', $this->locales_available ?? 'en'));
        $keywords = [];
        
        foreach ($availableLocales as $locale) {
            $property = "seo_default_keywords_{$locale}";
            if (property_exists($this, $property) && $this->$property) {
                $keywords[$locale] = $this->$property;
            }
        }
        
        return $keywords;
    }

    public function save(): void
    {
        $data = [
            'posts_per_page' => $this->posts_per_page,
            'route' => [
                'prefix' => $this->route_prefix,
                'frontend' => [
                    'enabled' => $this->route_frontend_enabled,
                ],
            ],
            'colors' => [
                'primary' => $this->colors_primary,
            ],
            'blog_index' => [
                'cards' => [
                    'colors' => [
                        'background' => $this->blog_index_cards_colors_background,
                        'top_border' => $this->blog_index_cards_colors_top_border,
                    ],
                ],
            ],
            'reading_speed' => [
                'words_per_minute' => $this->reading_speed_words_per_minute,
            ],
            'reading_time' => [
                'text_format' => $this->getReadingTimeTextFormats(),
                'enabled' => $this->reading_time_enabled,
            ],
            'locales' => [
                'enabled' => $this->locales_enabled,
                'default' => $this->locales_default,
                'available' => array_map('trim', explode(',', $this->locales_available ?? 'en')),
            ],
            'series' => [
                'enabled' => $this->series_enabled,
                'default_image' => $this->series_default_image,
            ],
            'toc' => [
                'enabled' => $this->toc_enabled,
                'strict_mode' => $this->toc_strict_mode,
            ],
            'seo' => [
                'site_name' => $this->getSeoSiteNames(),
                'default_title' => $this->getSeoDefaultTitles(),
                'default_description' => $this->getSeoDefaultDescriptions(),
                'default_keywords' => $this->getSeoDefaultKeywords(),
                'twitter_handle' => $this->seo_twitter_handle,
                'facebook_app_id' => $this->seo_facebook_app_id,
                'og' => [
                    'image' => $this->seo_og_image,
                    'image_width' => $this->seo_og_image_width,
                    'image_height' => $this->seo_og_image_height,
                ],
                'structured_data' => [
                    'enabled' => $this->seo_structured_data_enabled,
                    'organization' => [
                        'name' => $this->seo_structured_data_organization_name,
                        'url' => $this->seo_structured_data_organization_url,
                        'logo' => $this->seo_structured_data_organization_logo,
                    ],
                ],
            ],
            'ui' => [
                'navigation' => [
                    'enabled' => $this->navigation_enabled,
                    'sticky' => $this->navigation_sticky,
                    'show_logo' => $this->navigation_show_logo,
                    'show_language_switcher' => $this->navigation_show_language_switcher,
                    'show_theme_switcher' => $this->navigation_show_theme_switcher,
                ],
                'footer' => [
                    'enabled' => $this->footer_enabled,
                    'text' => $this->footer_text,
                    'show_social_links' => $this->footer_show_social_links,
                    'social_links' => [
                        'twitter' => $this->footer_twitter,
                        'github' => $this->footer_github,
                        'linkedin' => $this->footer_linkedin,
                        'facebook' => $this->footer_facebook,
                    ],
                ],
                'theme' => [
                    'default' => $this->theme_default,
                    'primary_color' => $this->theme_primary_color,
                ],
                'posts' => [
                    'default_image' => $this->posts_default_image,
                    'show_language_switcher' => $this->posts_show_language_switcher,
                ],
            ],
        ];

        // Update the config file
        $this->updateConfigFile($data);

        // Clear config cache
        Artisan::call('config:clear');

        Notification::make()
            ->title('Settings saved successfully!')
            ->success()
            ->send();
    }

    private function updateConfigFile(array $data): void
    {
        $configPath = config_path('blogr.php');

        // Read current config
        $currentConfig = config('blogr', []);

        // Merge the new data with current config
        $updatedConfig = array_merge($currentConfig, $data);

        // Generate new config file content
        $content = $this->generateConfigContent($updatedConfig);

        // Write to file
        File::put($configPath, $content);
    }

    private function generateConfigContent(array $config): string
    {
        $content = "<?php\n\n";
        $content .= "// config for Happytodev/Blogr\n";
        $content .= "return [\n";
        $content .= $this->arrayToString($config, 1);
        $content .= "];\n";

        return $content;
    }

    private function arrayToString(array $array, int $indent = 0): string
    {
        $result = '';
        $indentStr = str_repeat('    ', $indent);

        foreach ($array as $key => $value) {
            $result .= $indentStr;

            if (is_int($key)) {
                $result .= $this->valueToString($value);
            } else {
                $result .= "'{$key}' => ";
                $result .= $this->valueToString($value, $indent);
            }

            $result .= ",\n";
        }

        return $result;
    }

    private function valueToString($value, int $indent = 0): string
    {
        if (is_array($value)) {
            if (empty($value)) {
                return '[]';
            }

            $result = "[\n";
            $result .= $this->arrayToString($value, $indent + 1);
            $result .= str_repeat('    ', $indent) . ']';

            return $result;
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_null($value)) {
            return 'null';
        } elseif (is_string($value)) {
            return "'{$value}'";
        } else {
            return (string) $value;
        }
    }
}
