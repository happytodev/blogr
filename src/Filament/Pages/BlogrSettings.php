<?php

namespace Happytodev\Blogr\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\File;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Artisan;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Happytodev\Blogr\Helpers\ColorHelper;


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
    public ?bool $route_homepage = null;
    public ?string $colors_primary = null;
    public ?int $reading_speed_words_per_minute = null;
    
    // Appearance Colors (Card Backgrounds)
    public ?string $appearance_blog_card_bg = null;
    public ?string $appearance_blog_card_bg_dark = null;
    public ?string $appearance_series_card_bg = null;
    public ?string $appearance_series_card_bg_dark = null;
    
    // Theme Colors
    public ?string $theme_primary_color_dark = null;
    public ?string $theme_primary_color_hover = null;
    public ?string $theme_primary_color_hover_dark = null;
    public ?string $theme_category_bg = null;
    public ?string $theme_category_bg_dark = null;
    public ?string $theme_tag_bg = null;
    public ?string $theme_tag_bg_dark = null;
    public ?string $theme_author_bg = null;
    public ?string $theme_author_bg_dark = null;
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
    public ?string $toc_position = null;
    public ?string $heading_permalink_symbol = null;
    public ?string $heading_permalink_spacing = null;
    public ?string $heading_permalink_visibility = null;
    public ?bool $author_bio_enabled = null;
    public ?string $author_bio_position = null;
    public ?bool $author_bio_compact = null;
    public ?bool $author_profile_enabled = null;
    public ?bool $display_show_author_pseudo = null;
    public ?bool $display_show_author_avatar = null;
    public ?bool $display_show_series_authors = null;
    public ?int $display_series_authors_limit = null;
    public ?bool $locales_enabled = null;
    public ?string $locales_default = null;
    public ?string $locales_available = null;
    public ?bool $series_enabled = null;
    public ?array $series_default_image = null; // FileUpload expects array
    
    // UI Settings - Navigation
    public ?bool $navigation_enabled = null;
    public ?bool $navigation_sticky = null;
    public ?bool $navigation_show_logo = null;
    public ?bool $navigation_show_language_switcher = null;
    public ?bool $navigation_show_theme_switcher = null;
    
    // UI Settings - Dates
    public ?bool $dates_show_publication_date = null;
    public ?bool $dates_show_publication_date_on_cards = null;
    public ?bool $dates_show_publication_date_on_articles = null;
    
    // UI Settings - Posts
    public ?string $posts_tags_position = null;
    
    // UI Settings - Blog Post Card (DEPRECATED)
    public ?bool $blog_post_card_show_publication_date = null;
    
    // UI Settings - Footer
    public ?bool $footer_enabled = null;
    public ?string $footer_text = null;
    public ?bool $footer_show_social_links = null;
    public ?string $footer_twitter = null;
    public ?string $footer_github = null;
    public ?string $footer_linkedin = null;
    public ?string $footer_facebook = null;
    public ?string $footer_bluesky = null;
    public ?string $footer_youtube = null;
    public ?string $footer_instagram = null;
    public ?string $footer_tiktok = null;
    public ?string $footer_mastodon = null;
    
    // UI Settings - Theme
    public ?string $theme_default = null;
    public ?string $theme_primary_color = null;
    
    // UI Settings - Posts
    public ?string $posts_default_image = null;
    public ?bool $posts_show_language_switcher = null;

    /**
     * Check if the current user can access this page
     * Only admins should be able to access settings
     */
    public static function canAccess(): bool
    {
        return Filament::auth()->user()->hasRole('admin');
    }

    public function mount(): void
    {
        // Load current config values
        $config = config('blogr', []);

        // Set form properties from config
        $this->posts_per_page = $config['posts_per_page'] ?? 10;
        $this->route_prefix = $config['route']['prefix'] ?? 'blog';
        $this->route_frontend_enabled = $config['route']['frontend']['enabled'] ?? true;
        $this->route_homepage = $config['route']['homepage'] ?? false;
        $this->colors_primary = $config['colors']['primary'] ?? '#3b82f6';
        $this->reading_speed_words_per_minute = $config['reading_speed']['words_per_minute'] ?? 200;
        
        // Load appearance colors (card backgrounds)
        $this->appearance_blog_card_bg = $config['ui']['appearance']['blog_card_bg'] ?? '#ffffff';
        $this->appearance_blog_card_bg_dark = $config['ui']['appearance']['blog_card_bg_dark'] ?? '#1f2937';
        $this->appearance_series_card_bg = $config['ui']['appearance']['series_card_bg'] ?? '#f9fafb';
        $this->appearance_series_card_bg_dark = $config['ui']['appearance']['series_card_bg_dark'] ?? '#374151';
        
        // Load theme colors
        $this->theme_primary_color_dark = $config['ui']['theme']['primary_color_dark'] ?? '#9b0ab8';
        $this->theme_primary_color_hover = $config['ui']['theme']['primary_color_hover'] ?? '#d946ef';
        $this->theme_primary_color_hover_dark = $config['ui']['theme']['primary_color_hover_dark'] ?? '#a855f7';
        $this->theme_category_bg = $config['ui']['theme']['category_bg'] ?? '#e0f2fe';
        $this->theme_category_bg_dark = $config['ui']['theme']['category_bg_dark'] ?? '#0c4a6e';
        $this->theme_tag_bg = $config['ui']['theme']['tag_bg'] ?? '#d1fae5';
        $this->theme_tag_bg_dark = $config['ui']['theme']['tag_bg_dark'] ?? '#065f46';
        $this->theme_author_bg = $config['ui']['theme']['author_bg'] ?? '#fef3c7';
        $this->theme_author_bg_dark = $config['ui']['theme']['author_bg_dark'] ?? '#78350f';
        
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
        $this->toc_position = $config['toc']['position'] ?? 'center';
        $this->heading_permalink_symbol = $config['heading_permalink']['symbol'] ?? '#';
        $this->heading_permalink_spacing = $config['heading_permalink']['spacing'] ?? 'after';
        $this->heading_permalink_visibility = $config['heading_permalink']['visibility'] ?? 'hover';
        $this->author_bio_enabled = $config['author_bio']['enabled'] ?? true;
        $this->author_bio_position = $config['author_bio']['position'] ?? 'bottom';
        $this->author_bio_compact = $config['author_bio']['compact'] ?? false;
        $this->author_profile_enabled = $config['author_profile']['enabled'] ?? true;
        $this->display_show_author_pseudo = $config['display']['show_author_pseudo'] ?? true;
        $this->display_show_author_avatar = $config['display']['show_author_avatar'] ?? true;
        $this->display_show_series_authors = $config['display']['show_series_authors'] ?? true;
        $this->display_series_authors_limit = $config['display']['series_authors_limit'] ?? 4;
        $this->locales_enabled = $config['locales']['enabled'] ?? false;
        $this->locales_default = $config['locales']['default'] ?? 'en';
        $this->locales_available = is_array($config['locales']['available'] ?? [])
            ? implode(', ', $config['locales']['available'])
            : 'en, fr, es, de';
        $this->series_enabled = $config['series']['enabled'] ?? true;
        
        // FileUpload expects array, but config stores string - convert
        $defaultImage = $config['series']['default_image'] ?? '/vendor/blogr/images/default-series.svg';
        $this->series_default_image = is_string($defaultImage) && !empty($defaultImage) 
            ? [$defaultImage] 
            : (is_array($defaultImage) ? $defaultImage : null);
        
        // UI Settings
        $this->navigation_enabled = $config['ui']['navigation']['enabled'] ?? true;
        $this->navigation_sticky = $config['ui']['navigation']['sticky'] ?? true;
        $this->navigation_show_logo = $config['ui']['navigation']['show_logo'] ?? true;
        $this->navigation_show_language_switcher = $config['ui']['navigation']['show_language_switcher'] ?? true;
        $this->navigation_show_theme_switcher = $config['ui']['navigation']['show_theme_switcher'] ?? true;
        
        $this->dates_show_publication_date = $config['ui']['dates']['show_publication_date'] ?? true;
        $this->dates_show_publication_date_on_cards = $config['ui']['dates']['show_publication_date_on_cards'] ?? true;
        $this->dates_show_publication_date_on_articles = $config['ui']['dates']['show_publication_date_on_articles'] ?? true;
        
        $this->posts_tags_position = $config['ui']['posts']['tags_position'] ?? 'bottom';
        
        $this->blog_post_card_show_publication_date = $config['ui']['blog_post_card']['show_publication_date'] ?? true;
        
        $this->footer_enabled = $config['ui']['footer']['enabled'] ?? true;
        $this->footer_text = $config['ui']['footer']['text'] ?? '© ' . date('Y') . ' My Blog. All rights reserved.';
        $this->footer_show_social_links = $config['ui']['footer']['show_social_links'] ?? false;
        $this->footer_twitter = $config['ui']['footer']['social_links']['twitter'] ?? '';
        $this->footer_github = $config['ui']['footer']['social_links']['github'] ?? '';
        $this->footer_linkedin = $config['ui']['footer']['social_links']['linkedin'] ?? '';
        $this->footer_facebook = $config['ui']['footer']['social_links']['facebook'] ?? '';
        $this->footer_bluesky = $config['ui']['footer']['social_links']['bluesky'] ?? '';
        $this->footer_youtube = $config['ui']['footer']['social_links']['youtube'] ?? '';
        $this->footer_instagram = $config['ui']['footer']['social_links']['instagram'] ?? '';
        $this->footer_tiktok = $config['ui']['footer']['social_links']['tiktok'] ?? '';
        $this->footer_mastodon = $config['ui']['footer']['social_links']['mastodon'] ?? '';
        
        $this->theme_default = $config['ui']['theme']['default'] ?? 'light';
        $this->theme_primary_color = $config['ui']['theme']['primary_color'] ?? '#3b82f6';
        
        $this->posts_default_image = $config['ui']['posts']['default_image'] ?? null;
        $this->posts_show_language_switcher = $config['ui']['posts']['show_language_switcher'] ?? true;
    }

    protected function getFormSchema(): array
    {
        return [
            Tabs::make('Settings')
                ->tabs([
                    // ========================================
                    // GENERAL TAB
                    // ========================================
                    Tabs\Tab::make('General')
                        ->icon('heroicon-o-cog-6-tooth')
                        ->schema([
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
                                    Toggle::make('route_homepage')
                                        ->label('Enable Homepage')
                                        ->helperText('When enabled, the blog index becomes the homepage. You must comment out the default root route in routes/web.php to avoid conflicts.')
                                        ->default(false)
                                        ->columnSpan(2),
                                    ColorPicker::make('colors_primary')
                                        ->label('Primary Color (Admin Panel)')
                                        ->helperText('This is the primary color used in the Filament admin panel')
                                        ->default('#FA2C36')
                                        ->required(),
                                ])
                                ->columns(2),

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

                            Section::make('Series Settings')
                                ->description('Configure blog series and default images')
                                ->schema([
                                    Toggle::make('series_enabled')
                                        ->label('Enable Series')
                                        ->default(true)
                                        ->helperText('Allow grouping blog posts into series'),
                                    FileUpload::make('series_default_image')
                                        ->label('Default Series Image')
                                        ->image()
                                        ->disk('public')
                                        ->directory('blogr/series')
                                        ->visibility('public')
                                        ->imagePreviewHeight('100')
                                        ->default('/vendor/blogr/images/default-series.svg')
                                        ->helperText('Upload a default image for series without a custom photo. Accepts images only.')
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
                        ]),

                    // ========================================
                    // SEO TAB
                    // ========================================
                    Tabs\Tab::make('SEO')
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([
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
                                            ->helperText('The name of your website/brand (e.g., "My Blog"). Used in meta tags and browser title suffix.')
                                            ->required();
                                        
                                        $fields[] = TextInput::make("seo_default_title_{$locale}")
                                            ->label("Default Title ({$localeName})")
                                            ->placeholder('Blog')
                                            ->helperText('The default title for blog pages without a specific title (e.g., "Blog", "Articles"). This appears as the main page title.')
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
                        ]),

                    // ========================================
                    // APPEARANCE TAB
                    // ========================================
                    Tabs\Tab::make('Appearance')
                        ->icon('heroicon-o-paint-brush')
                        ->schema([
                            Section::make('Theme Settings')
                                ->description('Configure theme colors and appearance')
                                ->schema([
                                    Select::make('theme_default')
                                        ->label('Default Theme')
                                        ->options([
                                            'light' => 'Light Mode',
                                            'dark' => 'Dark Mode',
                                            'auto' => 'Auto (System Preference)',
                                        ])
                                        ->default('light')
                                        ->helperText('Users can override this in their browser')
                                        ->columnSpan(2),
                                    
                                    // Primary Colors
                                    ColorPicker::make('theme_primary_color')
                                        ->label('Primary Color (Light Mode)')
                                        ->default('#c20be5')
                                        ->helperText('Main accent color for links and interactive elements')
                                        ->columnSpan(1),
                                    ColorPicker::make('theme_primary_color_dark')
                                        ->label('Primary Color (Dark Mode)')
                                        ->default('#9b0ab8')
                                        ->columnSpan(1),
                                    ColorPicker::make('theme_primary_color_hover')
                                        ->label('Primary Hover (Light Mode)')
                                        ->default('#d946ef')
                                        ->columnSpan(1),
                                    ColorPicker::make('theme_primary_color_hover_dark')
                                        ->label('Primary Hover (Dark Mode)')
                                        ->default('#a855f7')
                                        ->columnSpan(1),
                                    
                                    // Blog Card Colors
                                    ColorPicker::make('appearance_blog_card_bg')
                                        ->label('Blog Post Card Background (Light Mode)')
                                        ->default('#ffffff')
                                        ->columnSpan(1),
                                    ColorPicker::make('appearance_blog_card_bg_dark')
                                        ->label('Blog Post Card Background (Dark Mode)')
                                        ->default('#1f2937')
                                        ->columnSpan(1),
                                    
                                    // Series Card Colors
                                    ColorPicker::make('appearance_series_card_bg')
                                        ->label('Series Card Background (Light Mode)')
                                        ->default('#f9fafb')
                                        ->columnSpan(1),
                                    ColorPicker::make('appearance_series_card_bg_dark')
                                        ->label('Series Card Background (Dark Mode)')
                                        ->default('#374151')
                                        ->columnSpan(1),
                                    
                                    // Category Colors
                                    ColorPicker::make('theme_category_bg')
                                        ->label('Category Badge (Light Mode)')
                                        ->default('#e0f2fe')
                                        ->columnSpan(1),
                                    ColorPicker::make('theme_category_bg_dark')
                                        ->label('Category Badge (Dark Mode)')
                                        ->default('#0c4a6e')
                                        ->columnSpan(1),
                                    
                                    // Tag Colors
                                    ColorPicker::make('theme_tag_bg')
                                        ->label('Tag Badge (Light Mode)')
                                        ->default('#d1fae5')
                                        ->columnSpan(1),
                                    ColorPicker::make('theme_tag_bg_dark')
                                        ->label('Tag Badge (Dark Mode)')
                                        ->default('#065f46')
                                        ->columnSpan(1),
                                    
                                    // Author Colors
                                    ColorPicker::make('theme_author_bg')
                                        ->label('Author Bio (Light Mode)')
                                        ->default('#fef3c7')
                                        ->columnSpan(1),
                                    ColorPicker::make('theme_author_bg_dark')
                                        ->label('Author Bio (Dark Mode)')
                                        ->default('#78350f')
                                        ->columnSpan(1),
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
                                    
                                    Toggle::make('dates_show_publication_date')
                                        ->label('Enable Publication Dates')
                                        ->default(true)
                                        ->helperText('Master toggle for all publication dates. When disabled, no dates will be shown.')
                                        ->live()
                                        ->columnSpanFull(),
                                    
                                    Toggle::make('dates_show_publication_date_on_cards')
                                        ->label('Show Dates on Blog Cards')
                                        ->default(true)
                                        ->helperText('Display publication date on blog post cards (index, category, tag pages)')
                                        ->disabled(fn (Get $get): bool => !$get('dates_show_publication_date')),
                                    
                                    Toggle::make('dates_show_publication_date_on_articles')
                                        ->label('Show Dates on Article Pages')
                                        ->default(true)
                                        ->helperText('Display publication date on article detail pages')
                                        ->disabled(fn (Get $get): bool => !$get('dates_show_publication_date')),
                                    
                                    Select::make('posts_tags_position')
                                        ->label('Tags Position')
                                        ->options([
                                            'top' => 'Top of Article',
                                            'bottom' => 'Bottom of Article',
                                        ])
                                        ->default('bottom')
                                        ->helperText('Position of tags on article detail pages')
                                        ->native(false),
                                    
                                    Toggle::make('posts_show_language_switcher')
                                        ->label('Show Language Availability')
                                        ->default(true)
                                        ->helperText('Display available translations on post pages'),
                                    
                                    Toggle::make('blog_post_card_show_publication_date')
                                        ->label('Show Publication Date on Cards (DEPRECATED)')
                                        ->default(true)
                                        ->helperText('⚠️ Deprecated - Use "Enable Publication Dates" settings above')
                                        ->disabled(true)
                                        ->hidden(),
                                ])
                                ->columns(2),
                        ]),

                    // ========================================
                    // CONTENT TAB
                    // ========================================
                    Tabs\Tab::make('Content')
                        ->icon('heroicon-o-document-text')
                        ->schema([
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
                                    Select::make('toc_position')
                                        ->label('TOC Position')
                                        ->options([
                                            'center' => 'Center (inline with content)',
                                            'left' => 'Left sidebar (sticky)',
                                            'right' => 'Right sidebar (sticky)',
                                        ])
                                        ->default('center')
                                        ->helperText('Position of the table of contents: center (default inline behavior), or sticky sidebar on left/right'),
                                ]),

                            Section::make('Heading Permalinks')
                                ->description('Configure heading anchor links appearance')
                                ->schema([
                                    TextInput::make('heading_permalink_symbol')
                                        ->label('Permalink Symbol')
                                        ->default('#')
                                        ->maxLength(5)
                                        ->helperText('Character to display next to headings (e.g., #, §, ¶, 🔗)'),
                                    Select::make('heading_permalink_spacing')
                                        ->label('Spacing')
                                        ->options([
                                            'none' => 'No spacing',
                                            'before' => 'Space before symbol',
                                            'after' => 'Space after symbol',
                                            'both' => 'Space before and after',
                                        ])
                                        ->default('after')
                                        ->helperText('Add spacing around the permalink symbol'),
                                    Select::make('heading_permalink_visibility')
                                        ->label('Visibility')
                                        ->options([
                                            'always' => 'Always visible',
                                            'hover' => 'Visible on hover only',
                                        ])
                                        ->default('hover')
                                        ->helperText('Control when permalink symbols are visible'),
                                ])
                                ->columns(3),

                            Section::make('Author Bio')
                                ->description('Configure how author information is displayed on blog posts')
                                ->schema([
                                    Toggle::make('author_profile_enabled')
                                        ->label('Enable Author Profile Pages')
                                        ->default(true)
                                        ->helperText(function () {
                                            $prefix = config('blogr.route.prefix', 'blog');
                                            $prefix = $prefix ? "/{$prefix}" : '';
                                            return "Allow users to access dedicated author profile pages at {$prefix}/author/{userSlug}";
                                        })
                                        ->columnSpanFull(),
                                    Toggle::make('display_show_author_pseudo')
                                        ->label('Show Author Pseudo/Slug')
                                        ->default(true)
                                        ->helperText('Display author pseudo (slug) instead of full name in article cards and headers')
                                        ->columnSpanFull(),
                                    Toggle::make('display_show_author_avatar')
                                        ->label('Show Author Avatar')
                                        ->default(true)
                                        ->helperText('Display author avatar thumbnail in article cards and headers')
                                        ->columnSpanFull(),
                                    Toggle::make('display_show_series_authors')
                                        ->label('Show Series Authors')
                                        ->default(true)
                                        ->helperText('Display author avatars with tooltips on series cards and pages')
                                        ->columnSpanFull(),
                                    TextInput::make('display_series_authors_limit')
                                        ->label('Series Authors Display Limit')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(10)
                                        ->default(4)
                                        ->helperText('Maximum number of author avatars to show before displaying "+X" indicator')
                                        ->columnSpanFull(),
                                    Toggle::make('author_bio_enabled')
                                        ->label('Display Author Bio')
                                        ->default(true)
                                        ->helperText('Show author information on blog posts'),
                                    Select::make('author_bio_position')
                                        ->label('Author Bio Position')
                                        ->options([
                                            'top' => 'Top of post',
                                            'bottom' => 'Bottom of post',
                                            'both' => 'Both top and bottom',
                                        ])
                                        ->default('bottom')
                                        ->helperText('Where to display the author bio on post pages'),
                                    Toggle::make('author_bio_compact')
                                        ->label('Use Compact Version')
                                        ->default(false)
                                        ->helperText('Use a compact inline version instead of the full bio box'),
                                ])
                                ->columns(3),
                        ]),

                    // ========================================
                    // NAVIGATION TAB
                    // ========================================
                    Tabs\Tab::make('Navigation')
                        ->icon('heroicon-o-bars-3')
                        ->schema([
                            Section::make('Navigation Bar')
                                ->description('Configure the top navigation bar appearance and behavior')
                                ->schema([
                                    Toggle::make('navigation_enabled')
                                        ->label('Enable Navigation Bar')
                                        ->default(true)
                                        ->live()
                                        ->helperText('Show the navigation bar at the top of every page'),
                                    
                                    Toggle::make('navigation_sticky')
                                        ->label('Sticky Navigation')
                                        ->default(true)
                                        ->visible(fn (Get $get) => $get('navigation_enabled'))
                                        ->helperText('Keep navigation bar visible when scrolling'),
                                    
                                    Toggle::make('navigation_show_logo')
                                        ->label('Show Site Logo/Name')
                                        ->default(true)
                                        ->visible(fn (Get $get) => $get('navigation_enabled'))
                                        ->helperText('Display your site name in the navigation bar'),
                                    
                                    Toggle::make('navigation_show_language_switcher')
                                        ->label('Show Language Switcher')
                                        ->default(true)
                                        ->visible(fn (Get $get) => $get('navigation_enabled'))
                                        ->helperText('Allow users to switch between available languages'),
                                    
                                    Toggle::make('navigation_show_theme_switcher')
                                        ->label('Show Theme Switcher')
                                        ->default(true)
                                        ->visible(fn (Get $get) => $get('navigation_enabled'))
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
                                        ->visible(fn (Get $get) => $get('footer_enabled'))
                                        ->columnSpanFull(),
                                    
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
                                    
                                    TextInput::make('footer_bluesky')
                                        ->label('Bluesky URL')
                                        ->url()
                                        ->placeholder('https://bsky.app/profile/yourusername.bsky.social')
                                        ->visible(fn (Get $get) => $get('footer_enabled') && $get('footer_show_social_links')),
                                    
                                    TextInput::make('footer_youtube')
                                        ->label('YouTube URL')
                                        ->url()
                                        ->placeholder('https://youtube.com/@yourusername')
                                        ->visible(fn (Get $get) => $get('footer_enabled') && $get('footer_show_social_links')),
                                    
                                    TextInput::make('footer_instagram')
                                        ->label('Instagram URL')
                                        ->url()
                                        ->placeholder('https://instagram.com/yourusername')
                                        ->visible(fn (Get $get) => $get('footer_enabled') && $get('footer_show_social_links')),
                                    
                                    TextInput::make('footer_tiktok')
                                        ->label('TikTok URL')
                                        ->url()
                                        ->placeholder('https://tiktok.com/@yourusername')
                                        ->visible(fn (Get $get) => $get('footer_enabled') && $get('footer_show_social_links')),
                                    
                                    TextInput::make('footer_mastodon')
                                        ->label('Mastodon URL')
                                        ->url()
                                        ->placeholder('https://mastodon.social/@yourusername')
                                        ->visible(fn (Get $get) => $get('footer_enabled') && $get('footer_show_social_links')),
                                ])
                                ->columns(2),
                        ]),
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
                'homepage' => $this->route_homepage,
            ],
            'colors' => [
                'primary' => $this->colors_primary,
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
                // Convert array back to string for config storage
                'default_image' => is_array($this->series_default_image) && !empty($this->series_default_image)
                    ? $this->series_default_image[0]
                    : ($this->series_default_image ?? '/vendor/blogr/images/default-series.svg'),
            ],
            'toc' => [
                'enabled' => $this->toc_enabled,
                'strict_mode' => $this->toc_strict_mode,
                'position' => $this->toc_position ?? 'center',
            ],
            'heading_permalink' => [
                'symbol' => $this->heading_permalink_symbol,
                'spacing' => $this->heading_permalink_spacing,
                'visibility' => $this->heading_permalink_visibility,
            ],
            'author_bio' => [
                'enabled' => $this->author_bio_enabled,
                'position' => $this->author_bio_position,
                'compact' => $this->author_bio_compact,
            ],
            'author_profile' => [
                'enabled' => $this->author_profile_enabled,
            ],
            'display' => [
                'show_author_pseudo' => $this->display_show_author_pseudo,
                'show_author_avatar' => $this->display_show_author_avatar,
                'show_series_authors' => $this->display_show_series_authors,
                'series_authors_limit' => $this->display_series_authors_limit,
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
                'dates' => [
                    'show_publication_date' => $this->dates_show_publication_date,
                    'show_publication_date_on_cards' => $this->dates_show_publication_date_on_cards,
                    'show_publication_date_on_articles' => $this->dates_show_publication_date_on_articles,
                ],
                'posts' => [
                    'tags_position' => $this->posts_tags_position,
                    'default_image' => $this->posts_default_image,
                    'show_language_switcher' => $this->posts_show_language_switcher,
                ],
                'blog_post_card' => [
                    'show_publication_date' => $this->blog_post_card_show_publication_date,
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
                        'bluesky' => $this->footer_bluesky,
                        'youtube' => $this->footer_youtube,
                        'instagram' => $this->footer_instagram,
                        'tiktok' => $this->footer_tiktok,
                        'mastodon' => $this->footer_mastodon,
                    ],
                ],
                'theme' => [
                    'default' => $this->theme_default,
                    'primary_color' => $this->theme_primary_color,
                    'primary_color_dark' => $this->theme_primary_color_dark,
                    'primary_color_hover' => $this->theme_primary_color_hover,
                    'primary_color_hover_dark' => $this->theme_primary_color_hover_dark,
                    'category_bg' => $this->theme_category_bg,
                    'category_bg_dark' => $this->theme_category_bg_dark,
                    'tag_bg' => $this->theme_tag_bg,
                    'tag_bg_dark' => $this->theme_tag_bg_dark,
                    'author_bg' => $this->theme_author_bg,
                    'author_bg_dark' => $this->theme_author_bg_dark,
                ],
                'appearance' => [
                    'blog_card_bg' => $this->appearance_blog_card_bg,
                    'blog_card_bg_dark' => $this->appearance_blog_card_bg_dark,
                    'series_card_bg' => $this->appearance_series_card_bg,
                    'series_card_bg_dark' => $this->appearance_series_card_bg_dark,
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
