<?php

namespace Happytodev\Blogr\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Slider;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Utilities\Get;
use Happytodev\Blogr\Blogr;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Services\BlogrExportService;
use Happytodev\Blogr\Services\BlogrImportService;
use Happytodev\Blogr\Services\LinkTypeRegistry;
use Happytodev\Blogr\Services\LocaleService;
use Happytodev\Blogr\Services\TranslationUsageService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\WithFileUploads;

class BlogrSettings extends Page
{
    use InteractsWithForms;
    use WithFileUploads;

    public string $admin_path = 'admin';

    public ?int $auto_save_interval = null;

    public string $translation_provider = 'none';

    public string $translation_libretranslate_url = '';

    public string $translation_azure_api_key = '';

    public string $translation_azure_region = 'westeurope';

    public string $translation_google_api_key = '';

    public string $translation_openai_api_key = '';

    public const THEME_PRESETS = [
        'magenta' => [
            'label' => 'Magenta (default)',
            'primary_color' => '#c20be5',
            'primary_color_dark' => '#e166fa',
            'primary_color_hover' => '#d946ef',
            'primary_color_hover_dark' => '#e49df2',
            'category_bg' => '#e0f2fe',
            'category_bg_dark' => '#0c4a6e',
            'tag_bg' => '#68fc12',
            'tag_bg_dark' => '#48b00d',
            'author_bg' => '#f2e2f9',
            'author_bg_dark' => '#9b0ab8',
            'bg_color' => '#ffffff',
            'bg_color_dark' => '#111827',
            'btn_color' => '#c20be5',
            'btn_color_dark' => '#e166fa',
            'text_color' => '#1f2937',
            'text_color_dark' => '#f3f4f6',
            'highlight_color' => '#c20be5',
            'highlight_color_dark' => '#e166fa',
            'testimonial_bg' => '#ffffff',
            'testimonial_bg_dark' => '#1f2937',
            'testimonial_text' => '#374151',
            'testimonial_text_dark' => '#d1d5db',
            'font_family' => 'Instrument Sans',
        ],
        'ocean' => [
            'label' => 'Ocean Blue',
            'primary_color' => '#2563eb',
            'primary_color_dark' => '#60a5fa',
            'primary_color_hover' => '#1d4ed8',
            'primary_color_hover_dark' => '#93c5fd',
            'category_bg' => '#dbeafe',
            'category_bg_dark' => '#1e3a5f',
            'tag_bg' => '#fde68a',
            'tag_bg_dark' => '#92400e',
            'author_bg' => '#e0f2fe',
            'author_bg_dark' => '#075985',
            'bg_color' => '#ffffff',
            'bg_color_dark' => '#0f172a',
            'btn_color' => '#2563eb',
            'btn_color_dark' => '#60a5fa',
            'text_color' => '#1e293b',
            'text_color_dark' => '#f1f5f9',
            'highlight_color' => '#2563eb',
            'highlight_color_dark' => '#60a5fa',
            'testimonial_bg' => '#ffffff',
            'testimonial_bg_dark' => '#1e3a5f',
            'testimonial_text' => '#1e293b',
            'testimonial_text_dark' => '#bfdbfe',
            'font_family' => 'Instrument Sans',
        ],
        'emerald' => [
            'label' => 'Emerald Green',
            'primary_color' => '#059669',
            'primary_color_dark' => '#34d399',
            'primary_color_hover' => '#047857',
            'primary_color_hover_dark' => '#6ee7b7',
            'category_bg' => '#d1fae5',
            'category_bg_dark' => '#064e3b',
            'tag_bg' => '#fef3c7',
            'tag_bg_dark' => '#78350f',
            'author_bg' => '#ecfdf5',
            'author_bg_dark' => '#065f46',
            'bg_color' => '#ffffff',
            'bg_color_dark' => '#022c22',
            'btn_color' => '#059669',
            'btn_color_dark' => '#34d399',
            'text_color' => '#1c1917',
            'text_color_dark' => '#f5f5f4',
            'highlight_color' => '#059669',
            'highlight_color_dark' => '#34d399',
            'testimonial_bg' => '#ffffff',
            'testimonial_bg_dark' => '#064e3b',
            'testimonial_text' => '#1c1917',
            'testimonial_text_dark' => '#a7f3d0',
            'font_family' => 'Instrument Sans',
        ],
        'sunset' => [
            'label' => 'Sunset Orange',
            'primary_color' => '#ea580c',
            'primary_color_dark' => '#fb923c',
            'primary_color_hover' => '#c2410c',
            'primary_color_hover_dark' => '#fdba74',
            'category_bg' => '#fff7ed',
            'category_bg_dark' => '#7c2d12',
            'tag_bg' => '#fce7f3',
            'tag_bg_dark' => '#831843',
            'author_bg' => '#ffedd5',
            'author_bg_dark' => '#9a3412',
            'bg_color' => '#ffffff',
            'bg_color_dark' => '#1c1917',
            'btn_color' => '#ea580c',
            'btn_color_dark' => '#fb923c',
            'text_color' => '#1c1917',
            'text_color_dark' => '#fafaf9',
            'highlight_color' => '#ea580c',
            'highlight_color_dark' => '#fb923c',
            'testimonial_bg' => '#ffffff',
            'testimonial_bg_dark' => '#7c2d12',
            'testimonial_text' => '#1c1917',
            'testimonial_text_dark' => '#fed7aa',
            'font_family' => 'Instrument Sans',
        ],
        'slate' => [
            'label' => 'Slate (minimal)',
            'primary_color' => '#475569',
            'primary_color_dark' => '#94a3b8',
            'primary_color_hover' => '#334155',
            'primary_color_hover_dark' => '#cbd5e1',
            'category_bg' => '#f1f5f9',
            'category_bg_dark' => '#1e293b',
            'tag_bg' => '#e2e8f0',
            'tag_bg_dark' => '#334155',
            'author_bg' => '#f8fafc',
            'author_bg_dark' => '#0f172a',
            'bg_color' => '#ffffff',
            'bg_color_dark' => '#0f172a',
            'btn_color' => '#475569',
            'btn_color_dark' => '#94a3b8',
            'text_color' => '#1e293b',
            'text_color_dark' => '#f1f5f9',
            'highlight_color' => '#475569',
            'highlight_color_dark' => '#94a3b8',
            'testimonial_bg' => '#ffffff',
            'testimonial_bg_dark' => '#1e293b',
            'testimonial_text' => '#1e293b',
            'testimonial_text_dark' => '#cbd5e1',
            'font_family' => 'Instrument Sans',
        ],
    ];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected string $view = 'blogr::filament.pages.blogr-settings';

    // Form properties
    public ?int $posts_per_page = null;

    public ?string $route_prefix = null;

    public ?bool $route_frontend_enabled = null;

    public ?bool $route_homepage = null;

    public ?string $colors_primary = null;

    public ?int $reading_speed_words_per_minute = null;

    // CMS Settings
    public ?bool $cms_enabled = null;

    public ?string $cms_prefix = null;

    public ?string $homepage_type = null; // 'blog' or 'cms'

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

    // Brand colors — background, buttons, text, highlights
    public ?string $theme_bg_color = null;

    public ?string $theme_bg_color_dark = null;

    public ?string $theme_btn_color = null;

    public ?string $theme_btn_color_dark = null;

    public ?string $theme_text_color = null;

    public ?string $theme_text_color_dark = null;

    public ?string $theme_highlight_color = null;

    public ?string $theme_highlight_color_dark = null;

    public ?string $theme_testimonial_bg = null;

    public ?string $theme_testimonial_bg_dark = null;

    public ?string $theme_testimonial_text = null;

    public ?string $theme_testimonial_text_dark = null;

    // Font settings
    public ?string $font_family = null;

    public ?string $font_custom_name = null;

    public ?string $font_custom_url = null;

    public ?string $font_custom_google = null;

    public string $font_preview = 'Instrument Sans';

    // Header & Footer colors (nullable = auto)
    public ?string $theme_header_bg = null;

    public ?string $theme_header_bg_dark = null;

    public ?string $theme_header_text = null;

    public ?string $theme_header_text_dark = null;

    public ?string $theme_footer_bg = null;

    public ?string $theme_footer_bg_dark = null;

    public ?string $theme_footer_text = null;

    public ?string $theme_footer_text_dark = null;

    // Header & Footer brightness sliders (-10 to +10, 0 = auto)
    public int $header_brightness = 0;

    public int $header_brightness_dark = 0;

    public int $footer_brightness = 0;

    public int $footer_brightness_dark = 0;

    public ?string $reading_time_text_format = null;

    public ?string $reading_time_text_en = null;

    public ?string $reading_time_text_fr = null;

    public ?string $reading_time_text_es = null;

    public ?string $reading_time_text_de = null;

    public ?bool $reading_time_enabled = null;

    // SEO Settings - Translatable fields (keyed by locale, e.g. ['en' => '...', 'fr' => '...'])
    public array $seo_site_names = [];

    public array $seo_default_titles = [];

    public array $seo_default_descriptions = [];

    public array $seo_default_keywords = [];

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

    public ?bool $locales_auto_detect = null;

    public array $locales_disabled = [];

    public ?bool $series_enabled = null;

    public ?array $series_default_image = null; // FileUpload expects array

    public ?int $series_max_visible_posts = null;

    public array $series_subtitles = [];

    public ?bool $enable_avatar_upload = null;

    // UI Settings - Navigation
    public ?bool $navigation_enabled = null;

    public ?bool $navigation_sticky = null;

    public ?bool $navigation_show_logo = null;

    public array $navigation_logo = []; // FileUpload expects array

    public ?string $navigation_logo_display = null;

    public ?bool $navigation_show_language_switcher = null;

    public ?bool $navigation_show_theme_switcher = null;

    public ?bool $navigation_auto_add_blog = null;  // Auto-add blog link when CMS is homepage

    public ?array $navigation_menu_items = [];

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

    // UI Settings - Back to Top
    public ?bool $back_to_top_enabled = null;

    public ?string $back_to_top_shape = null;

    public ?string $back_to_top_color = null;

    // Analytics Settings
    public ?bool $analytics_enabled = null;

    public ?string $analytics_provider = null;

    // Google Analytics
    public ?string $analytics_google_measurement_id = null;

    // Plausible
    public ?string $analytics_plausible_domain = null;

    public ?string $analytics_plausible_src = null;

    // Umami
    public ?string $analytics_umami_website_id = null;

    public ?string $analytics_umami_src = null;

    // Matomo
    public ?string $analytics_matomo_url = null;

    public ?string $analytics_matomo_site_id = null;

    // Anonymize IP
    public ?bool $analytics_anonymize_ip = null;

    // Sitemap Settings
    public ?bool $sitemap_enabled = null;

    // RSS Settings
    public ?bool $rss_enabled = null;

    public ?int $rss_items_limit = null;

    public ?bool $rss_show_in_header = null;

    public ?bool $rss_show_in_footer = null;

    // Contact Settings
    public ?string $contact_to_email = null;

    // Mail Settings
    public ?string $mail_provider = null;

    public ?string $mail_from_address = null;

    public ?string $mail_from_name = null;

    public ?string $mail_brevo_username = null;

    public ?string $mail_brevo_password = null;

    // Theme Preset
    public ?string $theme_preset = null;

    // Import/Export
    public array $import_file = [];

    public bool $overwrite_existing_data = false;

    public ?int $default_author_id = null;

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

        // Load CMS settings
        $this->cms_enabled = $config['cms']['enabled'] ?? false;
        $this->cms_prefix = $config['cms']['prefix'] ?? '';
        $this->homepage_type = $config['homepage']['type'] ?? 'blog';

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

        // Brand colors
        $this->theme_bg_color = $config['ui']['theme']['bg_color'] ?? '#ffffff';
        $this->theme_bg_color_dark = $config['ui']['theme']['bg_color_dark'] ?? '#111827';
        $this->theme_btn_color = $config['ui']['theme']['btn_color'] ?? '#c20be5';
        $this->theme_btn_color_dark = $config['ui']['theme']['btn_color_dark'] ?? '#e166fa';
        $this->theme_text_color = $config['ui']['theme']['text_color'] ?? '#1f2937';
        $this->theme_text_color_dark = $config['ui']['theme']['text_color_dark'] ?? '#f3f4f6';
        $this->theme_highlight_color = $config['ui']['theme']['highlight_color'] ?? '#c20be5';
        $this->theme_highlight_color_dark = $config['ui']['theme']['highlight_color_dark'] ?? '#e166fa';
        $this->theme_testimonial_bg = $config['ui']['theme']['testimonial_bg'] ?? '#ffffff';
        $this->theme_testimonial_bg_dark = $config['ui']['theme']['testimonial_bg_dark'] ?? '#1f2937';
        $this->theme_testimonial_text = $config['ui']['theme']['testimonial_text'] ?? '#374151';
        $this->theme_testimonial_text_dark = $config['ui']['theme']['testimonial_text_dark'] ?? '#d1d5db';
        $this->font_family = $config['ui']['theme']['font_family'] ?? 'Instrument Sans';
        $this->font_preview = $this->font_family;
        $this->font_custom_name = $config['ui']['theme']['font_custom_name'] ?? null;
        $this->font_custom_url = $config['ui']['theme']['font_custom_url'] ?? null;
        $this->font_custom_google = $config['ui']['theme']['font_custom_google'] ?? null;
        $this->theme_header_bg = $config['ui']['theme']['header_bg'] ?? null;
        $this->theme_header_bg_dark = $config['ui']['theme']['header_bg_dark'] ?? null;
        $this->theme_header_text = $config['ui']['theme']['header_text'] ?? null;
        $this->theme_header_text_dark = $config['ui']['theme']['header_text_dark'] ?? null;
        $this->theme_footer_bg = $config['ui']['theme']['footer_bg'] ?? null;
        $this->theme_footer_bg_dark = $config['ui']['theme']['footer_bg_dark'] ?? null;
        $this->theme_footer_text = $config['ui']['theme']['footer_text'] ?? null;
        $this->theme_footer_text_dark = $config['ui']['theme']['footer_text_dark'] ?? null;
        $this->header_brightness = $config['ui']['theme']['header_brightness'] ?? 0;
        $this->header_brightness_dark = $config['ui']['theme']['header_brightness_dark'] ?? 0;
        $this->footer_brightness = $config['ui']['theme']['footer_brightness'] ?? 0;
        $this->footer_brightness_dark = $config['ui']['theme']['footer_brightness_dark'] ?? 0;

        // Load reading time text format (supports both string and array formats)
        $textFormat = $config['reading_time']['text_format'] ?? 'Reading time: {time}';
        $availableLocales = $config['locales']['available'] ?? ['en'];

        if (is_array($textFormat)) {
            foreach ($availableLocales as $locale) {
                $property = "reading_time_text_{$locale}";
                $this->$property = $textFormat[$locale] ?? match ($locale) {
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
                $this->$property = match ($locale) {
                    'en' => $textFormat,
                    'fr' => 'Temps de lecture : {time}',
                    'es' => 'Tiempo de lectura: {time}',
                    'de' => 'Lesezeit: {time}',
                    default => $textFormat,
                };
            }
        }

        $this->reading_time_enabled = $config['reading_time']['enabled'] ?? true;

        // Load SEO settings - translatable fields (stored in arrays keyed by locale)
        $availableLocales = $config['locales']['available'] ?? ['en'];

        $this->seo_site_names = is_array($config['seo']['site_name'] ?? '')
            ? $config['seo']['site_name']
            : array_fill_keys($availableLocales, $config['seo']['site_name'] ?? env('APP_NAME', 'My Blog'));

        $this->seo_default_titles = is_array($config['seo']['default_title'] ?? '')
            ? $config['seo']['default_title']
            : array_fill_keys($availableLocales, $config['seo']['default_title'] ?? 'Blog');

        $this->seo_default_descriptions = is_array($config['seo']['default_description'] ?? '')
            ? $config['seo']['default_description']
            : array_fill_keys($availableLocales, $config['seo']['default_description'] ?? 'Discover our latest articles and insights');

        $this->seo_default_keywords = is_array($config['seo']['default_keywords'] ?? '')
            ? $config['seo']['default_keywords']
            : array_fill_keys($availableLocales, $config['seo']['default_keywords'] ?? 'blog, articles, news, insights');

        // Legacy non-translatable fields (for backward compatibility)
        $this->seo_site_name = $this->seo_site_names[$config['locales']['default'] ?? 'en'] ?? env('APP_NAME', 'My Blog');
        $this->seo_default_title = $this->seo_default_titles[$config['locales']['default'] ?? 'en'] ?? 'Blog';
        $this->seo_default_description = $this->seo_default_descriptions[$config['locales']['default'] ?? 'en'] ?? 'Discover our latest articles and insights';
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
        $this->locales_auto_detect = $config['locales']['auto_detect'] ?? false;
        $this->locales_disabled = $config['locales']['disabled'] ?? [];
        $this->series_enabled = $config['series']['enabled'] ?? true;

        // FileUpload expects array, but config stores string - convert
        $defaultImage = $config['series']['default_image'] ?? '/vendor/blogr/images/default-series.svg';
        $this->series_default_image = is_string($defaultImage) && ! empty($defaultImage)
            ? [$defaultImage]
            : (is_array($defaultImage) ? $defaultImage : null);

        $this->series_max_visible_posts = $config['series']['max_visible_posts'] ?? 10;
        $this->series_subtitles = $config['series']['subtitle'] ?? [
            'en' => 'Browse all our blog series and learn step by step.',
            'fr' => 'Parcourez toutes nos séries et apprenez étape par étape.',
            'es' => 'Explore todas nuestras series y aprende paso a paso.',
            'de' => 'Durchstöbern Sie alle unsere Serien und lernen Sie Schritt für Schritt.',
            'pl' => 'Przeglądaj wszystkie nasze serie i ucz się krok po kroku.',
        ];

        $this->enable_avatar_upload = $config['enable_avatar_upload'] ?? true;

        // UI Settings
        $this->navigation_enabled = $config['ui']['navigation']['enabled'] ?? true;
        $this->navigation_sticky = $config['ui']['navigation']['sticky'] ?? true;
        $this->navigation_show_logo = $config['ui']['navigation']['show_logo'] ?? true;
        $this->navigation_logo = isset($config['ui']['navigation']['logo']) && $config['ui']['navigation']['logo']
            ? (is_array($config['ui']['navigation']['logo']) ? $config['ui']['navigation']['logo'] : [$config['ui']['navigation']['logo']])
            : [];
        $this->navigation_logo_display = $config['ui']['navigation']['logo_display'] ?? 'text';
        $this->navigation_show_language_switcher = $config['ui']['navigation']['show_language_switcher'] ?? true;
        $this->navigation_show_theme_switcher = $config['ui']['navigation']['show_theme_switcher'] ?? true;
        $this->navigation_auto_add_blog = $config['ui']['navigation']['auto_add_blog'] ?? false;
        $this->navigation_menu_items = $config['ui']['navigation']['menu_items'] ?? [];

        $this->dates_show_publication_date = $config['ui']['dates']['show_publication_date'] ?? true;
        $this->dates_show_publication_date_on_cards = $config['ui']['dates']['show_publication_date_on_cards'] ?? true;
        $this->dates_show_publication_date_on_articles = $config['ui']['dates']['show_publication_date_on_articles'] ?? true;

        $this->posts_tags_position = $config['ui']['posts']['tags_position'] ?? 'bottom';

        $this->blog_post_card_show_publication_date = $config['ui']['blog_post_card']['show_publication_date'] ?? true;

        $this->footer_enabled = $config['ui']['footer']['enabled'] ?? true;
        $this->footer_text = $config['ui']['footer']['text'] ?? '© '.date('Y').' My Blog. All rights reserved.';
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

        // Load back-to-top settings
        $this->back_to_top_enabled = $config['ui']['back_to_top']['enabled'] ?? true;
        $this->back_to_top_shape = $config['ui']['back_to_top']['shape'] ?? 'circle';
        $this->back_to_top_color = $config['ui']['back_to_top']['color'] ?? null; // null = use primary color

        // Load analytics settings
        $this->analytics_enabled = $config['analytics']['enabled'] ?? false;
        $this->analytics_provider = $config['analytics']['provider'] ?? null;
        // Google Analytics
        $this->analytics_google_measurement_id = $config['analytics']['google']['measurement_id'] ?? null;
        // Plausible
        $this->analytics_plausible_domain = $config['analytics']['plausible']['domain'] ?? null;
        $this->analytics_plausible_src = $config['analytics']['plausible']['src'] ?? null;
        // Umami
        $this->analytics_umami_website_id = $config['analytics']['umami']['website_id'] ?? null;
        $this->analytics_umami_src = $config['analytics']['umami']['src'] ?? null;
        // Matomo
        $this->analytics_matomo_url = $config['analytics']['matomo']['url'] ?? null;
        $this->analytics_matomo_site_id = $config['analytics']['matomo']['site_id'] ?? null;
        $this->analytics_anonymize_ip = $config['analytics']['anonymize_ip'] ?? true;

        // Load sitemap settings
        $this->sitemap_enabled = $config['sitemap']['enabled'] ?? true;

        // Load RSS settings
        $this->rss_enabled = $config['rss']['enabled'] ?? true;
        $this->rss_items_limit = $config['rss']['items_limit'] ?? 20;
        $this->rss_show_in_header = $config['rss']['show_in_header'] ?? false;
        $this->rss_show_in_footer = $config['rss']['show_in_footer'] ?? false;

        // Load contact settings
        $this->contact_to_email = $config['contact']['to_email'] ?? '';

        // Load mail settings
        $this->mail_provider = $config['mail']['provider'] ?? '';
        $this->mail_from_address = $config['mail']['from']['address'] ?? '';
        $this->mail_from_name = $config['mail']['from']['name'] ?? '';
        $this->mail_brevo_username = $config['mail']['brevo']['username'] ?? env('MAIL_USERNAME', '');
        $this->mail_brevo_password = $config['mail']['brevo']['password'] ?? env('MAIL_PASSWORD', '');

        // Load theme preset (no preset = custom)
        $this->theme_preset = $config['ui']['theme']['preset'] ?? '';

        // Load admin panel path
        $this->admin_path = $config['admin_path'] ?? 'admin';

        $this->auto_save_interval = $config['auto_save_interval'] ?? 30;

        $this->translation_provider = $config['translation']['provider'] ?? 'none';
        $this->translation_libretranslate_url = $config['translation']['libretranslate']['url'] ?? 'http://localhost:5000';
        $this->translation_azure_api_key = $config['translation']['azure']['api_key'] ?? '';
        $this->translation_azure_region = $config['translation']['azure']['region'] ?? 'westeurope';
        $this->translation_google_api_key = $config['translation']['google']['api_key'] ?? '';
        $this->translation_openai_api_key = $config['translation']['openai']['api_key'] ?? '';
    }

    public function updated($name, $value): void
    {
        match ($name) {
            'locales_auto_detect' => $this->autoDetectLocales($value),
            default => null,
        };
    }

    public function updatedThemePreset(?string $value): void
    {
        if (! $value || ! isset(self::THEME_PRESETS[$value])) {
            return;
        }

        $this->applyThemePreset($value);
    }

    public function updatedLocalesAutoDetect(?string $value): void
    {
        $this->autoDetectLocales($value);
    }

    private function autoDetectLocales(?string $value): void
    {
        if ($value) {
            $this->locales_available = null;
        }
    }

    private function applyThemePreset(?string $value): void
    {
        if (! $value || ! isset(self::THEME_PRESETS[$value])) {
            return;
        }

        $colors = self::THEME_PRESETS[$value];
        $this->theme_primary_color = $colors['primary_color'];
        $this->theme_primary_color_dark = $colors['primary_color_dark'];
        $this->theme_primary_color_hover = $colors['primary_color_hover'];
        $this->theme_primary_color_hover_dark = $colors['primary_color_hover_dark'];
        $this->theme_category_bg = $colors['category_bg'];
        $this->theme_category_bg_dark = $colors['category_bg_dark'];
        $this->theme_tag_bg = $colors['tag_bg'];
        $this->theme_tag_bg_dark = $colors['tag_bg_dark'];
        $this->theme_author_bg = $colors['author_bg'];
        $this->theme_author_bg_dark = $colors['author_bg_dark'];
        $this->theme_bg_color = $colors['bg_color'];
        $this->theme_bg_color_dark = $colors['bg_color_dark'];
        $this->theme_btn_color = $colors['btn_color'];
        $this->theme_btn_color_dark = $colors['btn_color_dark'];
        $this->theme_text_color = $colors['text_color'];
        $this->theme_text_color_dark = $colors['text_color_dark'];
        $this->theme_highlight_color = $colors['highlight_color'];
        $this->theme_highlight_color_dark = $colors['highlight_color_dark'];
        $this->theme_testimonial_bg = $colors['testimonial_bg'];
        $this->theme_testimonial_bg_dark = $colors['testimonial_bg_dark'];
        $this->theme_testimonial_text = $colors['testimonial_text'];
        $this->theme_testimonial_text_dark = $colors['testimonial_text_dark'];
        $this->font_family = $colors['font_family'] ?? 'Instrument Sans';
        $this->font_custom_name = $colors['font_custom_name'] ?? null;
        $this->font_custom_url = $colors['font_custom_url'] ?? null;
    }

    public function getFormSchema(): array
    {
        return [
            Tabs::make('Settings')
                ->persistTabInQueryString('tab')
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
                                        ->helperText('URL prefix for blog routes (e.g., /blog/my-post)')
                                        ->required(),
                                    Toggle::make('route_frontend_enabled')
                                        ->label('Enable Frontend Routes')
                                        ->helperText('Enable frontend routes for the blog')
                                        ->default(true)
                                        ->required(),
                                    ColorPicker::make('colors_primary')
                                        ->label('Primary Color (Admin Panel)')
                                        ->helperText('This is the primary color used in the Filament admin panel')
                                        ->default('#FA2C36')
                                        ->required(),
                                ])
                                ->columns(2),

                            Section::make('Homepage & CMS Configuration')
                                ->description('Configure your website homepage and CMS (static pages) settings')
                                ->schema([
                                    Select::make('homepage_type')
                                        ->label('Homepage Type')
                                        ->options([
                                            'blog' => 'Blog Index (list of posts)',
                                            'cms' => 'CMS Page (static homepage)',
                                        ])
                                        ->default('blog')
                                        ->live()
                                        ->required()
                                        ->helperText('Choose what appears at the root URL (/)'),

                                    Toggle::make('cms_enabled')
                                        ->label('Enable CMS (Static Pages)')
                                        ->helperText('Enable the CMS module for creating static pages like About, Contact, etc.')
                                        ->default(false)
                                        ->live()
                                        ->columnSpan(1),

                                    TextInput::make('cms_prefix')
                                        ->label('CMS Route Prefix')
                                        ->placeholder('page or leave empty')
                                        ->helperText('URL prefix for CMS pages (e.g., /page/about or /about if empty). Not used if CMS is homepage.')
                                        ->visible(fn (Get $get) => $get('cms_enabled'))
                                        ->columnSpan(1),

                                    TextInput::make('contact_to_email')
                                        ->label('Contact Form Recipient Email')
                                        ->placeholder('hello@example.com')
                                        ->helperText('Emails from the contact form will be sent to this address.')
                                        ->email()
                                        ->columnSpan(1),

                                    Section::make('Email Configuration')
                                        ->description('Configure your email provider for sending emails (contact form, notifications, etc.)')
                                        ->collapsed()
                                        ->schema([
                                            Select::make('mail_provider')
                                                ->label('Email Provider')
                                                ->options([
                                                    '' => 'Use .env configuration (default)',
                                                    'brevo' => 'Brevo (Sendinblue)',
                                                ])
                                                ->default('')
                                                ->live()
                                                ->columnSpan(1),

                                            TextInput::make('mail_from_address')
                                                ->label('From Address')
                                                ->placeholder('hello@example.com')
                                                ->helperText('The email address that will appear as the sender.')
                                                ->email()
                                                ->columnSpan(1),

                                            TextInput::make('mail_from_name')
                                                ->label('From Name')
                                                ->placeholder('My Blog')
                                                ->columnSpan(1),

                                            TextInput::make('mail_brevo_username')
                                                ->label('Brevo SMTP Login')
                                                ->placeholder('xxxxxx1234@smtp-brevo.com')
                                                ->helperText('Your Brevo SMTP login from Brevo > SMTP & API > SMTP Settings (the full email-like address, NOT your account email).')
                                                ->visible(fn (Get $get) => $get('mail_provider') === 'brevo')
                                                ->columnSpan(1),

                                            TextInput::make('mail_brevo_password')
                                                ->label('Brevo SMTP Key')
                                                ->placeholder('xsmtpsib-xxxxxxxx...')
                                                ->helperText('Your SMTP key from Brevo > SMTP & API > SMTP Keys. This is your password for SMTP authentication.')
                                                ->password()
                                                ->visible(fn (Get $get) => $get('mail_provider') === 'brevo')
                                                ->columnSpan(1),
                                        ])
                                        ->headerActions([
                                            Action::make('send_test_email')
                                                ->label('Send Test Email')
                                                ->icon('heroicon-o-envelope')
                                                ->color('gray')
                                                ->form([
                                                    TextInput::make('test_email')
                                                        ->label('Send test to')
                                                        ->placeholder('your@email.com')
                                                        ->email()
                                                        ->required(),
                                                ])
                                                ->action(function (array $data) {
                                                    try {
                                                        Mail::raw(
                                                            'This is a test email from Blogr. Your email configuration is working correctly!',
                                                            function ($message) use ($data) {
                                                                $message->to($data['test_email'])
                                                                    ->subject('[Blogr] Test Email');
                                                            }
                                                        );

                                                        Notification::make()
                                                            ->title('Test email sent successfully!')
                                                            ->success()
                                                            ->send();
                                                    } catch (\Exception $e) {
                                                        $mailer = config('mail.mailers.smtp', []);
                                                        $from = config('mail.from.address');

                                                        Log::error('Blogr test email failed', [
                                                            'error' => $e->getMessage(),
                                                            'mail_driver' => config('mail.default'),
                                                            'mail_host' => $mailer['host'] ?? 'not set',
                                                            'mail_port' => $mailer['port'] ?? 'not set',
                                                            'mail_username' => $mailer['username'] ?? 'not set',
                                                            'mail_from' => $from,
                                                        ]);

                                                        $msg = $e->getMessage();
                                                        $hint = '';

                                                        if (str_contains($msg, 'Authentication failed') || str_contains($msg, '535')) {
                                                            $hint = ' Check that your Brevo SMTP username is the email you used to register your Brevo account, and the password is a valid SMTP key from Brevo (Settings > SMTP & API > SMTP Keys).';
                                                        }

                                                        Notification::make()
                                                            ->title('Test email failed')
                                                            ->body($msg.$hint)
                                                            ->danger()
                                                            ->send();
                                                    }
                                                }),
                                        ])
                                        ->columnSpanFull(),

                                    Placeholder::make('cms_info')
                                        ->content(fn (Get $get) => match ($get('homepage_type')) {
                                            'blog' => '📝 Homepage will show blog posts. CMS pages will be accessible via /'.($get('cms_prefix') ?: '').'{slug}',
                                            'cms' => '🏠 Homepage will show a CMS page (you need to create one and mark it as homepage). Blog will be accessible via /'.$get('route_prefix').'',
                                            default => '',
                                        })
                                        ->visible(fn (Get $get) => $get('cms_enabled') || $get('homepage_type') === 'cms')
                                        ->columnSpanFull(),
                                ])
                                ->columns(2)
                                ->collapsible(),

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
                                            ->placeholder(match ($locale) {
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
                                ->schema(function () {
                                    $availableLocales = config('blogr.locales.available', ['en']);
                                    $localeNames = [
                                        'en' => 'English',
                                        'fr' => 'Français',
                                        'es' => 'Español',
                                        'de' => 'Deutsch',
                                        'pl' => 'Polski',
                                    ];

                                    $fields = [
                                        Toggle::make('series_enabled')
                                            ->label('Enable Series')
                                            ->default(true)
                                            ->helperText('Allow grouping blog posts into series'),
                                        TextInput::make('series_max_visible_posts')
                                            ->label('Max visible posts')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(50)
                                            ->default(10)
                                            ->helperText('Number of posts shown in the series list before the "show more" toggle appears. Additional posts are hidden behind a click-to-expand link.')
                                            ->columnSpan(1),
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
                                    ];

                                    foreach ($availableLocales as $locale) {
                                        $localeName = $localeNames[$locale] ?? strtoupper($locale);
                                        $fields[] = TextInput::make("series_subtitles.{$locale}")
                                            ->label("Series Subtitle ({$localeName})")
                                            ->placeholder('Browse all our blog series and learn step by step.')
                                            ->helperText('Subtitle text displayed on the series index page.')
                                            ->columnSpan(2);
                                    }

                                    return $fields;
                                })
                                ->columns(2),

                            Section::make('Multilingual Settings')
                                ->description('Configure available locales and default locale for translations')
                                ->schema([
                                    Toggle::make('locales_enabled')
                                        ->label('Enable Localized Routes')
                                        ->default(false)
                                        ->helperText('Enable URL structure like /{locale}/blog/... (e.g., /en/blog/my-post, /fr/blog/mon-article)'),
                                    Select::make('locales_default')
                                        ->label('Default Locale')
                                        ->required()
                                        ->default('en')
                                        ->helperText('The default locale used when no translation is available')
                                        ->options(function () {
                                            $localeNames = [
                                                'en' => 'English',
                                                'fr' => 'Français',
                                                'es' => 'Español',
                                                'de' => 'Deutsch',
                                                'it' => 'Italiano',
                                                'pt' => 'Português',
                                                'ru' => 'Русский',
                                                'pl' => 'Polski',
                                                'el' => 'Ελληνικά',
                                                'no' => 'Norsk',
                                            ];
                                            $available = config('blogr.locales.available', ['en']);
                                            $options = [];
                                            foreach ($available as $locale) {
                                                $name = $localeNames[$locale] ?? strtoupper($locale);
                                                $options[$locale] = "{$name} ({$locale})";
                                            }

                                            return $options;
                                        }),
                                    Toggle::make('locales_auto_detect')
                                        ->label('Auto-detect Languages')
                                        ->default(false)
                                        ->helperText('When enabled, available locales are automatically detected from published content. When disabled, the manual list below is used.')
                                        ->live(),
                                    Textarea::make('locales_available')
                                        ->label('Available Locales (Manual)')
                                        ->required()
                                        ->default('en, fr, es, de')
                                        ->rows(2)
                                        ->helperText('Comma-separated list of available locales (e.g., en, fr, es, de). Only used when Auto-detect is OFF.')
                                        ->visible(fn ($get) => ! $get('locales_auto_detect')),
                                    CheckboxList::make('locales_disabled')
                                        ->label('Disabled Languages')
                                        ->helperText('Languages checked here will be hidden from the frontend language switcher and will return a 404 when accessed directly. Only used when Auto-detect is ON.')
                                        ->options(function () {
                                            $service = app(LocaleService::class);
                                            $available = $service->getAvailable();
                                            $localeNames = [
                                                'en' => 'English',
                                                'fr' => 'Français',
                                                'es' => 'Español',
                                                'de' => 'Deutsch',
                                                'it' => 'Italiano',
                                                'pt' => 'Português',
                                                'ru' => 'Русский',
                                                'pl' => 'Polski',
                                                'el' => 'Ελληνικά',
                                                'no' => 'Norsk',
                                            ];

                                            $options = [];
                                            foreach ($available as $locale) {
                                                $name = $localeNames[$locale] ?? strtoupper($locale);
                                                $options[$locale] = "{$name} ({$locale})";
                                            }

                                            return $options;
                                        })
                                        ->visible(fn ($get) => $get('locales_auto_detect')),
                                ])
                                ->columns(2),

                            Section::make('Sitemap & RSS')
                                ->description('Sitemap and RSS feed configuration')
                                ->schema([
                                    Toggle::make('sitemap_enabled')
                                        ->label('Enable XML Sitemap')
                                        ->helperText('Generate and serve /sitemap.xml for search engines')
                                        ->default(true)
                                        ->columnSpan(2),

                                    Placeholder::make('rss_separator')
                                        ->label('RSS Feeds')
                                        ->content('Configure RSS/Atom feed visibility and discoverability on the frontend.')
                                        ->columnSpanFull(),

                                    Toggle::make('rss_enabled')
                                        ->label('Enable RSS Feeds')
                                        ->helperText('Enable RSS feed generation at /feed or /{locale}/feed')
                                        ->default(true)
                                        ->live()
                                        ->columnSpan(1),

                                    TextInput::make('rss_items_limit')
                                        ->label('Feed Items Limit')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(100)
                                        ->default(20)
                                        ->helperText('Maximum number of posts in the feed')
                                        ->visible(fn (Get $get) => $get('rss_enabled'))
                                        ->columnSpan(1),

                                    Toggle::make('rss_show_in_header')
                                        ->label('Show RSS Icon in Header')
                                        ->helperText('Display RSS icon in the navigation bar')
                                        ->default(false)
                                        ->visible(fn (Get $get) => $get('rss_enabled'))
                                        ->columnSpan(1),

                                    Toggle::make('rss_show_in_footer')
                                        ->label('Show RSS Icon in Footer')
                                        ->helperText('Display RSS icon in the footer area')
                                        ->default(false)
                                        ->visible(fn (Get $get) => $get('rss_enabled'))
                                        ->columnSpan(1),
                                ])
                                ->columns(2)
                                ->collapsible(),

                            Section::make('Profile & 2FA')
                                ->description('Configure profile avatar upload and two-factor authentication settings')
                                ->schema([
                                    Toggle::make('enable_avatar_upload')
                                        ->label('Enable avatar upload on profile page')
                                        ->default(true)
                                        ->helperText('When enabled, users can upload a profile photo from their profile page. When disabled, the avatar field is hidden. Requires Breezy 2FA to be installed (php artisan blogr:install-breezy). Changes take effect immediately.'),
                                ])
                                ->columns(2)
                                ->collapsible(),

                            Section::make('Auto-save')
                                ->description('Configure automatic saving of blog posts and CMS pages')
                                ->schema([
                                    TextInput::make('auto_save_interval')
                                        ->label('Auto-save interval (seconds)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(600)
                                        ->default(30)
                                        ->helperText('How often the editor auto-saves your work. Set to 0 to disable auto-save. A beforeunload warning protects against accidental navigation when there are unsaved changes.'),
                                ])
                                ->columns(2)
                                ->collapsible(),

                            Section::make('Admin Panel')
                                ->description('Customize your admin panel access path. Current path: /'.(config('blogr.admin_path') ?? 'admin').'. After saving, run: php artisan blogr:sync-admin-path')
                                ->schema([
                                    TextInput::make('admin_path')
                                        ->label('Admin panel path')
                                        ->helperText('The URL path to access the admin panel (e.g. "admin", "backoffice", "dashboard"). Saved to .env as BLOGR_ADMIN_PATH and config/blogr.php. After saving, run: php artisan blogr:sync-admin-path')
                                        ->default('admin')
                                        ->required()
                                        ->alphaDash()
                                        ->maxLength(50),
                                    Placeholder::make('current_path')
                                        ->label('Current effective path')
                                        ->content(function () {
                                            $path = config('blogr.admin_path', 'admin');

                                            return '/'.$path;
                                        }),
                                ])
                                ->columns(2)
                                ->collapsible(),

                            Section::make('About Blogr')
                                ->description('Version information and resources')
                                ->schema([
                                    Placeholder::make('blogr_version')
                                        ->label('Version')
                                        ->content(fn () => Blogr::getVersion()),
                                    Placeholder::make('blogr_releases')
                                        ->label('Releases')
                                        ->content(fn () => 'https://blogr.happyto.dev/en/blog/v'.Blogr::getVersion())
                                        ->view('blogr::filament.components.version-link'),
                                ])
                                ->columns(2)
                                ->collapsible(),
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

                                        $fields[] = TextInput::make("seo_site_names.{$locale}")
                                            ->label("Site Name ({$localeName})")
                                            ->placeholder('My Blog')
                                            ->helperText('The name of your website/brand (e.g., "My Blog"). Used in meta tags and browser title suffix.')
                                            ->required();

                                        $fields[] = TextInput::make("seo_default_titles.{$locale}")
                                            ->label("Default Title ({$localeName})")
                                            ->placeholder('Blog')
                                            ->helperText('The default title for blog pages without a specific title (e.g., "Blog", "Articles"). This appears as the main page title.')
                                            ->required();

                                        $fields[] = Textarea::make("seo_default_descriptions.{$locale}")
                                            ->label("Default Description ({$localeName})")
                                            ->placeholder('Discover our latest articles and insights')
                                            ->rows(2)
                                            ->required()
                                            ->columnSpan(2);

                                        $fields[] = Textarea::make("seo_default_keywords.{$locale}")
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

                                    Select::make('theme_preset')
                                        ->label('Theme Preset')
                                        ->options([
                                            '' => 'Custom (manual colors)',
                                            'magenta' => 'Magenta (default)',
                                            'ocean' => 'Ocean Blue',
                                            'emerald' => 'Emerald Green',
                                            'sunset' => 'Sunset Orange',
                                            'slate' => 'Slate (minimal)',
                                        ])
                                        ->default('')
                                        ->helperText('Select a preset to auto-fill all theme colors. Choose "Custom" to set colors manually.')
                                        ->live()
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

                                    // Brand Colors
                                    ColorPicker::make('theme_bg_color')
                                        ->label('Page Background (Light Mode)')
                                        ->default('#ffffff')
                                        ->columnSpan(1),
                                    ColorPicker::make('theme_bg_color_dark')
                                        ->label('Page Background (Dark Mode)')
                                        ->default('#111827')
                                        ->columnSpan(1),

                                    ColorPicker::make('theme_btn_color')
                                        ->label('Buttons (Light Mode)')
                                        ->default('#c20be5')
                                        ->columnSpan(1),
                                    ColorPicker::make('theme_btn_color_dark')
                                        ->label('Buttons (Dark Mode)')
                                        ->default('#e166fa')
                                        ->columnSpan(1),

                                    ColorPicker::make('theme_text_color')
                                        ->label('Text (Light Mode)')
                                        ->default('#1f2937')
                                        ->columnSpan(1),
                                    ColorPicker::make('theme_text_color_dark')
                                        ->label('Text (Dark Mode)')
                                        ->default('#f3f4f6')
                                        ->columnSpan(1),

                                    ColorPicker::make('theme_highlight_color')
                                        ->label('Highlights (Light Mode)')
                                        ->default('#c20be5')
                                        ->columnSpan(1),
                                    ColorPicker::make('theme_highlight_color_dark')
                                        ->label('Highlights (Dark Mode)')
                                        ->default('#e166fa')
                                        ->columnSpan(1),

                                    // Testimonial Card Colors
                                    ColorPicker::make('theme_testimonial_bg')
                                        ->label('Testimonial Card BG (Light Mode)')
                                        ->default('#ffffff')
                                        ->columnSpan(1),
                                    ColorPicker::make('theme_testimonial_bg_dark')
                                        ->label('Testimonial Card BG (Dark Mode)')
                                        ->default('#1f2937')
                                        ->columnSpan(1),

                                    ColorPicker::make('theme_testimonial_text')
                                        ->label('Testimonial Quote Text (Light Mode)')
                                        ->default('#374151')
                                        ->columnSpan(1),
                                    ColorPicker::make('theme_testimonial_text_dark')
                                        ->label('Testimonial Quote Text (Dark Mode)')
                                        ->default('#d1d5db')
                                        ->columnSpan(1),
                                ])
                                ->columns(2),

                            Section::make('Typography')
                                ->description('Choose the font family for your blog')
                                ->schema([
                                    Select::make('font_family')
                                        ->label('Font Family')
                                        ->options([
                                            'Instrument Sans' => 'Instrument Sans',
                                            'Inter' => 'Inter',
                                            'Figtree' => 'Figtree',
                                            'Plus Jakarta Sans' => 'Plus Jakarta Sans',
                                            'DM Sans' => 'DM Sans',
                                            'Outfit' => 'Outfit',
                                            'Onest' => 'Onest',
                                            'Manrope' => 'Manrope',
                                            'Lexend' => 'Lexend',
                                            'Sora' => 'Sora',
                                            'Public Sans' => 'Public Sans',
                                            'Space Grotesk' => 'Space Grotesk',
                                            'Fraunces' => 'Fraunces',
                                            'Source Serif 4' => 'Source Serif 4',
                                            'Lora' => 'Lora',
                                            'Merriweather' => 'Merriweather',
                                            'Playfair Display' => 'Playfair Display',
                                            'PT Serif' => 'PT Serif',
                                            'JetBrains Mono' => 'JetBrains Mono',
                                            'Fira Code' => 'Fira Code',
                                            'DM Mono' => 'DM Mono',
                                            'custom' => 'Custom (upload .woff2 or enter name)',
                                            'system' => 'System default',
                                        ])
                                        ->searchable()
                                        ->default('Instrument Sans')
                                        ->live()
                                        ->afterStateUpdated(fn ($state, callable $set) => $set('font_preview', $state))
                                        ->helperText('Choose a Google Font from the list, or pick "Custom" to upload your own.')
                                        ->columnSpan(2),

                                    TextInput::make('font_preview')
                                        ->label('Font Preview (save to see on frontend)')
                                        ->default('Instrument Sans')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->columnSpan(2),

                                    TextInput::make('font_custom_name')
                                        ->label('Custom Font Name')
                                        ->placeholder('MyFont')
                                        ->visible(fn (callable $get) => $get('font_family') === 'custom')
                                        ->helperText('The name of your custom font (used in CSS font-family).')
                                        ->columnSpan(1),

                                    FileUpload::make('font_custom_url')
                                        ->label('Custom Font File (.woff2)')
                                        ->disk('public')
                                        ->directory('blogr/fonts')
                                        ->visibility('public')
                                        ->acceptedFileTypes(['font/woff2', 'application/octet-stream'])
                                        ->visible(fn (callable $get) => $get('font_family') === 'custom')
                                        ->helperText('Upload a .woff2 file, or leave empty and just enter a Google Font name above.')
                                        ->columnSpan(1),

                                    TextInput::make('font_custom_google')
                                        ->label('Custom Google Font Name')
                                        ->placeholder('Outfit')
                                        ->visible(fn (callable $get) => $get('font_family') === 'custom')
                                        ->helperText('Enter ANY Google Font name (e.g. "Outfit", "Bebas Neue"). The font will be loaded automatically from Google Fonts.')
                                        ->columnSpan(2),
                                ])
                                ->columns(2),

                            Section::make('Header & Footer Colors')
                                ->description('Customize the header/navigation and footer backgrounds. Leave empty to auto-match the page background color.')
                                ->schema([
                                    ColorPicker::make('theme_header_bg')
                                        ->label('Header Background (Light Mode)')
                                        ->helperText('Leave empty to use page background')
                                        ->nullable()
                                        ->columnSpan(1),
                                    ColorPicker::make('theme_header_bg_dark')
                                        ->label('Header Background (Dark Mode)')
                                        ->nullable()
                                        ->columnSpan(1),

                                    ColorPicker::make('theme_header_text')
                                        ->label('Header Text (Light Mode)')
                                        ->nullable()
                                        ->columnSpan(1),
                                    ColorPicker::make('theme_header_text_dark')
                                        ->label('Header Text (Dark Mode)')
                                        ->nullable()
                                        ->columnSpan(1),

                                    ColorPicker::make('theme_footer_bg')
                                        ->label('Footer Background (Light Mode)')
                                        ->nullable()
                                        ->columnSpan(1),
                                    ColorPicker::make('theme_footer_bg_dark')
                                        ->label('Footer Background (Dark Mode)')
                                        ->nullable()
                                        ->columnSpan(1),

                                    ColorPicker::make('theme_footer_text')
                                        ->label('Footer Text (Light Mode)')
                                        ->nullable()
                                        ->columnSpan(1),
                                    ColorPicker::make('theme_footer_text_dark')
                                        ->label('Footer Text (Dark Mode)')
                                        ->nullable()
                                        ->columnSpan(1),

                                    Slider::make('header_brightness')
                                        ->label('Header brightness (Light Mode)')
                                        ->minValue(-10)->maxValue(10)->default(0)
                                        ->extraAttributes(['x-on:dblclick' => '$wire.set(\'header_brightness\', 0)'])
                                        ->helperText('Negative = darker, positive = lighter. Double-click to reset to 0 (auto).')
                                        ->columnSpan(1),
                                    Slider::make('header_brightness_dark')
                                        ->label('Header brightness (Dark Mode)')
                                        ->minValue(-10)->maxValue(10)->default(0)
                                        ->extraAttributes(['x-on:dblclick' => '$wire.set(\'header_brightness_dark\', 0)'])
                                        ->columnSpan(1),

                                    Slider::make('footer_brightness')
                                        ->label('Footer brightness (Light Mode)')
                                        ->minValue(-10)->maxValue(10)->default(0)
                                        ->extraAttributes(['x-on:dblclick' => '$wire.set(\'footer_brightness\', 0)'])
                                        ->columnSpan(1),
                                    Slider::make('footer_brightness_dark')
                                        ->label('Footer brightness (Dark Mode)')
                                        ->minValue(-10)->maxValue(10)->default(0)
                                        ->extraAttributes(['x-on:dblclick' => '$wire.set(\'footer_brightness_dark\', 0)'])
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
                                        ->disabled(fn (Get $get): bool => ! $get('dates_show_publication_date')),

                                    Toggle::make('dates_show_publication_date_on_articles')
                                        ->label('Show Dates on Article Pages')
                                        ->default(true)
                                        ->helperText('Display publication date on article detail pages')
                                        ->disabled(fn (Get $get): bool => ! $get('dates_show_publication_date')),

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

                            Section::make('Back to Top Button')
                                ->description('Configure the floating back-to-top button')
                                ->schema([
                                    Toggle::make('back_to_top_enabled')
                                        ->label('Enable Back to Top Button')
                                        ->default(true)
                                        ->helperText('Display a floating button to scroll back to top of the page'),

                                    Select::make('back_to_top_shape')
                                        ->label('Button Shape')
                                        ->options([
                                            'circle' => 'Circle',
                                            'square' => 'Square (rounded corners)',
                                        ])
                                        ->default('circle')
                                        ->helperText('Choose the visual style of the button')
                                        ->native(false),

                                    ColorPicker::make('back_to_top_color')
                                        ->label('Button Color')
                                        ->helperText('Leave empty to use the primary theme color')
                                        ->nullable(),
                                ])
                                ->columns(3),
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

                                    FileUpload::make('navigation_logo')
                                        ->label('Logo Image')
                                        ->image()
                                        ->disk('public')
                                        ->directory('blogr/logos')
                                        ->imageResizeMode('contain')
                                        ->imageResizeTargetHeight(200)
                                        ->maxSize(2048)
                                        ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml', 'image/webp'])
                                        ->visibility('public')
                                        ->storeFiles()
                                        ->moveFiles()
                                        ->visible(fn (Get $get) => $get('navigation_enabled') && $get('navigation_show_logo'))
                                        ->helperText('Upload your logo (max 2MB, will be resized to 200px height)'),                                    Select::make('navigation_logo_display')
                                        ->label('Logo Display Mode')
                                        ->options([
                                            'text' => 'Text Only (Site Name)',
                                            'image' => 'Image Only',
                                            'both' => 'Both Image and Text',
                                        ])
                                        ->default('text')
                                        ->visible(fn (Get $get) => $get('navigation_enabled') && $get('navigation_show_logo'))
                                        ->helperText('Choose how to display your site branding'),

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

                                    Toggle::make('navigation_auto_add_blog')
                                        ->label('Auto-add Blog Link (when CMS is homepage)')
                                        ->default(false)
                                        ->visible(fn (Get $get) => $get('navigation_enabled') && $get('homepage_type') === 'cms')
                                        ->helperText('Automatically add a "Blog" link to the menu when CMS is set as homepage. Labels will be translated for all enabled languages.'),
                                ])
                                ->columns(2),

                            Section::make('Navigation Menu Items')
                                ->description('Add custom links to your navigation bar. Configure labels for each language and optionally add sub-menu items for mega menus.')
                                ->schema([
                                    Repeater::make('navigation_menu_items')
                                        ->label('Menu Items')
                                        ->schema([
                                            // Multilingual labels
                                            Repeater::make('labels')
                                                ->label('Labels (by language)')
                                                ->schema([
                                                    Select::make('locale')
                                                        ->label('Language')
                                                        ->options(function () {
                                                            $locales = config('blogr.locales.available', ['en']);

                                                            return collect($locales)->mapWithKeys(fn ($locale) => [$locale => strtoupper($locale)]);
                                                        })
                                                        ->required()
                                                        ->distinct()
                                                        ->columnSpan(1),

                                                    TextInput::make('label')
                                                        ->label('Label')
                                                        ->required()
                                                        ->placeholder('About Us')
                                                        ->columnSpan(1),
                                                ])
                                                ->columns(2)
                                                ->collapsed()
                                                ->itemLabel(fn (array $state) => strtoupper($state['locale'] ?? 'NEW').': '.($state['label'] ?? 'New Label'))
                                                ->addActionLabel('Add Translation')
                                                ->defaultItems(1)
                                                ->columnSpanFull(),

                                            Select::make('type')
                                                ->label('Link Type')
                                                ->options(function () {
                                                    $options = [
                                                        'external' => 'External URL',
                                                        'blog' => 'Blog Home',
                                                        'category' => 'Category',
                                                        'cms_page' => 'CMS Page',
                                                        'megamenu' => 'Mega Menu (with sub-items)',
                                                    ];

                                                    $pluginOptions = app(LinkTypeRegistry::class)->getOptions();

                                                    if (! empty($pluginOptions)) {
                                                        $options['Plugins'] = $pluginOptions;
                                                    }

                                                    return $options;
                                                })
                                                ->default('external')
                                                ->live()
                                                ->required()
                                                ->columnSpan(1),

                                            TextInput::make('url')
                                                ->label('URL')
                                                ->url()
                                                ->placeholder('https://example.com/about')
                                                ->visible(fn (Get $get) => $get('type') === 'external')
                                                ->required(fn (Get $get) => $get('type') === 'external')
                                                ->columnSpan(1),

                                            Select::make('category_id')
                                                ->label('Select Category')
                                                ->options(function () {
                                                    return Category::with('translations')
                                                        ->get()
                                                        ->mapWithKeys(function ($category) {
                                                            $translation = $category->translations->first();

                                                            return [$category->id => $translation->name ?? 'Category #'.$category->id];
                                                        });
                                                })
                                                ->searchable()
                                                ->visible(fn (Get $get) => $get('type') === 'category')
                                                ->required(fn (Get $get) => $get('type') === 'category')
                                                ->columnSpan(1),

                                            Select::make('cms_page_id')
                                                ->label('Select CMS Page')
                                                ->options(function () {
                                                    return CmsPage::with('translations')
                                                        ->get()
                                                        ->mapWithKeys(function ($page) {
                                                            $translation = $page->translations->first();

                                                            return [$page->id => $translation->title ?? 'Page #'.$page->id];
                                                        });
                                                })
                                                ->searchable()
                                                ->visible(fn (Get $get) => $get('type') === 'cms_page')
                                                ->required(fn (Get $get) => $get('type') === 'cms_page')
                                                ->columnSpan(1),

                                            Select::make('target')
                                                ->label('Open in')
                                                ->options([
                                                    '_self' => 'Same window',
                                                    '_blank' => 'New window',
                                                ])
                                                ->default('_self')
                                                ->visible(fn (Get $get) => $get('type') !== 'megamenu')
                                                ->columnSpan(1),

                                            TextInput::make('icon')
                                                ->label('Icon (Heroicon name)')
                                                ->placeholder('heroicon-o-home')
                                                ->helperText('Optional. Use heroicon names like: heroicon-o-home, heroicon-o-user')
                                                ->columnSpan(1),

                                            // Sub-menu items for mega menu
                                            Repeater::make('children')
                                                ->label('Sub-menu Items')
                                                ->schema([
                                                    Repeater::make('labels')
                                                        ->label('Labels (by language)')
                                                        ->schema([
                                                            Select::make('locale')
                                                                ->label('Language')
                                                                ->options(function () {
                                                                    $locales = config('blogr.locales.available', ['en']);

                                                                    return collect($locales)->mapWithKeys(fn ($locale) => [$locale => strtoupper($locale)]);
                                                                })
                                                                ->required()
                                                                ->distinct()
                                                                ->columnSpan(1),

                                                            TextInput::make('label')
                                                                ->label('Label')
                                                                ->required()
                                                                ->placeholder('Sub Item')
                                                                ->columnSpan(1),
                                                        ])
                                                        ->columns(2)
                                                        ->collapsed()
                                                        ->itemLabel(fn (array $state) => strtoupper($state['locale'] ?? 'NEW').': '.($state['label'] ?? 'New Label'))
                                                        ->defaultItems(1)
                                                        ->columnSpanFull(),

                                                    Select::make('type')
                                                        ->label('Link Type')
                                                        ->options([
                                                            'external' => 'External URL',
                                                            'blog' => 'Blog Home',
                                                            'category' => 'Category',
                                                        ])
                                                        ->default('external')
                                                        ->stateBindingModifiers(['defer'])
                                                        ->required()
                                                        ->columnSpan(1),

                                                    TextInput::make('url')
                                                        ->label('URL')
                                                        ->url()
                                                        ->placeholder('https://example.com/page')
                                                        ->visible(fn (Get $get) => $get('type') === 'external')
                                                        ->required(fn (Get $get) => $get('type') === 'external')
                                                        ->columnSpan(1),

                                                    Select::make('category_id')
                                                        ->label('Select Category')
                                                        ->options(function () {
                                                            return Category::with('translations')
                                                                ->get()
                                                                ->mapWithKeys(function ($category) {
                                                                    $translation = $category->translations->first();

                                                                    return [$category->id => $translation->name ?? 'Category #'.$category->id];
                                                                });
                                                        })
                                                        ->searchable()
                                                        ->visible(fn (Get $get) => $get('type') === 'category')
                                                        ->required(fn (Get $get) => $get('type') === 'category')
                                                        ->columnSpan(1),

                                                    Select::make('target')
                                                        ->label('Open in')
                                                        ->options([
                                                            '_self' => 'Same window',
                                                            '_blank' => 'New window',
                                                        ])
                                                        ->default('_self')
                                                        ->columnSpan(1),
                                                ])
                                                ->columns(2)
                                                ->collapsed()
                                                ->itemLabel(fn (array $state) => $state['labels'][0]['label'] ?? 'Sub Item')
                                                ->addActionLabel('Add Sub-Item')
                                                ->visible(fn (Get $get) => $get('type') === 'megamenu')
                                                ->columnSpanFull(),
                                        ])
                                        ->columns(2)
                                        ->reorderable()
                                        ->collapsible()
                                        ->itemLabel(fn (array $state) => $state['labels'][0]['label'] ?? 'New Item')
                                        ->addActionLabel('Add Menu Item')
                                        ->visible(fn (Get $get) => $get('navigation_enabled'))
                                        ->columnSpanFull(),
                                ])
                                ->visible(fn (Get $get) => $get('navigation_enabled'))
                                ->columnSpanFull(),

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
                                        ->default('© '.date('Y').' My Blog. All rights reserved.')
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
                                        ->rule('nullable|url')
                                        ->placeholder('https://twitter.com/yourusername')
                                        ->visible(fn (Get $get) => $get('footer_enabled') && $get('footer_show_social_links')),

                                    TextInput::make('footer_github')
                                        ->label('GitHub URL')
                                        ->rule('nullable|url')
                                        ->placeholder('https://github.com/yourusername')
                                        ->visible(fn (Get $get) => $get('footer_enabled') && $get('footer_show_social_links')),

                                    TextInput::make('footer_linkedin')
                                        ->label('LinkedIn URL')
                                        ->rule('nullable|url')
                                        ->placeholder('https://linkedin.com/in/yourusername')
                                        ->visible(fn (Get $get) => $get('footer_enabled') && $get('footer_show_social_links')),

                                    TextInput::make('footer_facebook')
                                        ->label('Facebook URL')
                                        ->rule('nullable|url')
                                        ->placeholder('https://facebook.com/yourusername')
                                        ->visible(fn (Get $get) => $get('footer_enabled') && $get('footer_show_social_links')),

                                    TextInput::make('footer_bluesky')
                                        ->label('Bluesky URL')
                                        ->rule('nullable|url')
                                        ->placeholder('https://bsky.app/profile/yourusername.bsky.social')
                                        ->visible(fn (Get $get) => $get('footer_enabled') && $get('footer_show_social_links')),

                                    TextInput::make('footer_youtube')
                                        ->label('YouTube URL')
                                        ->rule('nullable|url')
                                        ->placeholder('https://youtube.com/@yourusername')
                                        ->visible(fn (Get $get) => $get('footer_enabled') && $get('footer_show_social_links')),

                                    TextInput::make('footer_instagram')
                                        ->label('Instagram URL')
                                        ->rule('nullable|url')
                                        ->placeholder('https://instagram.com/yourusername')
                                        ->visible(fn (Get $get) => $get('footer_enabled') && $get('footer_show_social_links')),

                                    TextInput::make('footer_tiktok')
                                        ->label('TikTok URL')
                                        ->rule('nullable|url')
                                        ->placeholder('https://tiktok.com/@yourusername')
                                        ->visible(fn (Get $get) => $get('footer_enabled') && $get('footer_show_social_links')),

                                    TextInput::make('footer_mastodon')
                                        ->label('Mastodon URL')
                                        ->rule('nullable|url')
                                        ->placeholder('https://mastodon.social/@yourusername')
                                        ->visible(fn (Get $get) => $get('footer_enabled') && $get('footer_show_social_links')),
                                ])
                                ->columns(2),
                        ]),

                    // ========================================
                    // ANALYTICS TAB
                    // ========================================
                    Tabs\Tab::make('Analytics')
                        ->icon('heroicon-o-chart-bar')
                        ->schema([
                            Section::make('Web Analytics Configuration')
                                ->description('Configure your web analytics provider to track visitor statistics')
                                ->schema([
                                    Toggle::make('analytics_enabled')
                                        ->label('Enable Analytics')
                                        ->helperText('Enable or disable analytics tracking on the frontend')
                                        ->default(false)
                                        ->live(),

                                    Select::make('analytics_provider')
                                        ->label('Analytics Provider')
                                        ->options([
                                            'google' => 'Google Analytics',
                                            'plausible' => 'Plausible Analytics',
                                            'umami' => 'Umami Analytics',
                                            'matomo' => 'Matomo Analytics',
                                        ])
                                        ->placeholder('Select your analytics provider')
                                        ->helperText('Choose your preferred analytics service')
                                        ->visible(fn (Get $get) => $get('analytics_enabled'))
                                        ->live()
                                        ->native(false),
                                ]),

                            // Google Analytics Configuration
                            Section::make('Google Analytics')
                                ->description('Configure Google Analytics 4 (GA4) or Universal Analytics')
                                ->icon('heroicon-o-chart-pie')
                                ->schema([
                                    TextInput::make('analytics_google_measurement_id')
                                        ->label('Measurement ID')
                                        ->placeholder('G-XXXXXXXXXX or UA-XXXXX-X')
                                        ->helperText('Your Google Analytics Measurement ID. Find it in GA Admin → Data Streams → Web stream details')
                                        ->required(fn (Get $get) => $get('analytics_enabled') && $get('analytics_provider') === 'google'),

                                    Placeholder::make('google_info')
                                        ->content('Google Analytics will inject the gtag.js script and track page views automatically. Make sure to configure your GA4 property correctly in the Google Analytics dashboard.')
                                        ->columnSpanFull(),
                                ])
                                ->visible(fn (Get $get) => $get('analytics_enabled') && $get('analytics_provider') === 'google')
                                ->collapsible(),

                            // Plausible Analytics Configuration
                            Section::make('Plausible Analytics')
                                ->description('Configure Plausible Analytics (privacy-friendly alternative)')
                                ->icon('heroicon-o-shield-check')
                                ->schema([
                                    TextInput::make('analytics_plausible_domain')
                                        ->label('Domain')
                                        ->placeholder('yoursite.com')
                                        ->helperText('Your site domain as registered in Plausible (without https://)')
                                        ->required(fn (Get $get) => $get('analytics_enabled') && $get('analytics_provider') === 'plausible'),

                                    TextInput::make('analytics_plausible_src')
                                        ->label('Script URL (optional)')
                                        ->placeholder('https://plausible.io/js/script.js')
                                        ->helperText('Leave empty to use the default Plausible Cloud script. For self-hosted: https://your-plausible-instance.com/js/script.js')
                                        ->url(),

                                    Placeholder::make('plausible_info')
                                        ->content('Plausible is a privacy-friendly, cookie-free analytics service. It\'s GDPR, CCPA and PECR compliant out of the box without requiring a cookie banner.')
                                        ->columnSpanFull(),
                                ])
                                ->visible(fn (Get $get) => $get('analytics_enabled') && $get('analytics_provider') === 'plausible')
                                ->collapsible(),

                            // Umami Analytics Configuration
                            Section::make('Umami Analytics')
                                ->description('Configure Umami Analytics (open-source, privacy-focused)')
                                ->icon('heroicon-o-eye-slash')
                                ->schema([
                                    TextInput::make('analytics_umami_website_id')
                                        ->label('Website ID')
                                        ->placeholder('a93a8ed3-88da-4f54-b9ce-378d8f33f06a')
                                        ->helperText('Your Umami Website ID (UUID format). Find it in Umami → Websites → Edit')
                                        ->required(fn (Get $get) => $get('analytics_enabled') && $get('analytics_provider') === 'umami'),

                                    TextInput::make('analytics_umami_src')
                                        ->label('Script URL')
                                        ->placeholder('https://cloud.umami.is/script.js')
                                        ->helperText('Umami Cloud: https://cloud.umami.is/script.js | Self-hosted: https://your-umami.com/script.js')
                                        ->url()
                                        ->required(fn (Get $get) => $get('analytics_enabled') && $get('analytics_provider') === 'umami'),

                                    Placeholder::make('umami_info')
                                        ->content('Umami is a simple, fast, privacy-focused alternative to Google Analytics. It doesn\'t use cookies and collects only essential data.')
                                        ->columnSpanFull(),
                                ])
                                ->visible(fn (Get $get) => $get('analytics_enabled') && $get('analytics_provider') === 'umami')
                                ->collapsible(),

                            // Matomo Analytics Configuration
                            Section::make('Matomo Analytics')
                                ->description('Configure Matomo Analytics (formerly Piwik)')
                                ->icon('heroicon-o-server')
                                ->schema([
                                    TextInput::make('analytics_matomo_url')
                                        ->label('Matomo URL')
                                        ->placeholder('https://matomo.yoursite.com')
                                        ->helperText('Your Matomo instance URL (without trailing slash)')
                                        ->url()
                                        ->required(fn (Get $get) => $get('analytics_enabled') && $get('analytics_provider') === 'matomo'),

                                    TextInput::make('analytics_matomo_site_id')
                                        ->label('Site ID')
                                        ->placeholder('1')
                                        ->helperText('Your Matomo Site ID (numeric). Find it in Matomo → Administration → Websites → Manage')
                                        ->numeric()
                                        ->required(fn (Get $get) => $get('analytics_enabled') && $get('analytics_provider') === 'matomo'),

                                    Placeholder::make('matomo_info')
                                        ->content('Matomo is an open-source web analytics platform. You can self-host it for complete data ownership or use Matomo Cloud.')
                                        ->columnSpanFull(),
                                ])
                                ->visible(fn (Get $get) => $get('analytics_enabled') && $get('analytics_provider') === 'matomo')
                                ->collapsible(),

                            Toggle::make('analytics_anonymize_ip')
                                ->label('Anonymize IP addresses')
                                ->helperText('Removes the last octet of visitor IP addresses before sending to analytics providers. Recommended for GDPR compliance.')
                                ->default(true)
                                ->visible(fn (Get $get) => $get('analytics_enabled'))
                                ->columnSpanFull(),
                        ]),

                    // ========================================
                    // BACKUP TAB
                    // ========================================
                    Tabs\Tab::make('Backup')
                        ->icon('heroicon-o-cloud-arrow-up')
                        ->schema([
                            Section::make('Export Blogr Data')
                                ->description('Export all your blog posts, series, categories, and tags to a JSON or ZIP file')
                                ->schema([
                                    Placeholder::make('export_info')
                                        ->content('Choose your export format below. JSON exports contain only data, while ZIP exports include both data and media files.')
                                        ->columnSpanFull(),
                                ])
                                ->headerActions([
                                    Action::make('export')
                                        ->label('Export Data (JSON)')
                                        ->icon('heroicon-o-arrow-down-tray')
                                        ->color('success')
                                        ->action(function () {
                                            try {
                                                $exportService = app(BlogrExportService::class);
                                                $filePath = $exportService->exportToFile(null, ['include_media' => false]);

                                                Notification::make()
                                                    ->title('Export Successful')
                                                    ->body("Data exported to: {$filePath}")
                                                    ->success()
                                                    ->send();

                                                return response()->download($filePath);
                                            } catch (\Exception $e) {
                                                Notification::make()
                                                    ->title('Export Failed')
                                                    ->body('An error occurred during export: '.$e->getMessage())
                                                    ->danger()
                                                    ->send();
                                            }
                                        }),
                                    Action::make('export_with_media')
                                        ->label('Export Data + Media (ZIP)')
                                        ->icon('heroicon-o-photo')
                                        ->color('info')
                                        ->action(function () {
                                            try {
                                                $exportService = app(BlogrExportService::class);
                                                $filePath = $exportService->exportToFile(null, ['include_media' => true]);

                                                Notification::make()
                                                    ->title('Export Successful')
                                                    ->body("Data and media exported to: {$filePath}")
                                                    ->success()
                                                    ->send();

                                                return response()->download($filePath)->deleteFileAfterSend(true);
                                            } catch (\Exception $e) {
                                                Notification::make()
                                                    ->title('Export Failed')
                                                    ->body('An error occurred during export: '.$e->getMessage())
                                                    ->danger()
                                                    ->send();
                                            }
                                        }),
                                ])
                                ->columnSpanFull(),

                            Section::make('Import Blogr Data')
                                ->description('Import blog posts, series, categories, and tags from a JSON or ZIP file')
                                ->schema([
                                    FileUpload::make('import_file')
                                        ->label('Import File')
                                        ->acceptedFileTypes(['application/json', 'application/zip'])
                                        ->maxSize(51200) // 50MB for ZIP files
                                        ->directory('blogr/temp')
                                        ->visibility('private')
                                        ->helperText('Upload a JSON or ZIP file exported from Blogr'),

                                    Toggle::make('overwrite_existing_data')
                                        ->label('Écraser les données existantes / Overwrite existing data')
                                        ->helperText('⚠️ ATTENTION : Cette option supprimera TOUS les posts, catégories, tags et séries existants avant l\'importation. Les utilisateurs ne seront PAS supprimés. / WARNING: This will DELETE ALL existing blog posts, categories, tags and series before import. Users will NOT be deleted.')
                                        ->default(false)
                                        ->inline(false),

                                    Select::make('default_author_id')
                                        ->label('Auteur par défaut pour les posts orphelins / Default author for orphaned posts')
                                        ->helperText('Si des posts dans l\'import ont un auteur qui n\'existe pas dans la base cible, ils seront assignés à cet utilisateur. Si non spécifié, ces posts seront ignorés. / If posts in the import have an author that doesn\'t exist in the target database, they will be assigned to this user. If not specified, those posts will be skipped.')
                                        ->options(fn () => User::all()->pluck('name', 'id')->toArray())
                                        ->searchable()
                                        ->nullable(),

                                ])
                                ->headerActions([
                                    Action::make('import')
                                        ->label('Import Data')
                                        ->icon('heroicon-o-arrow-up-tray')
                                        ->color('warning')
                                        ->requiresConfirmation()
                                        ->modalHeading(fn () => $this->overwrite_existing_data
                                            ? '⚠️ ATTENTION: Supprimer toutes les données existantes ? / Delete all existing data?'
                                            : 'Importer les données / Import data')
                                        ->modalDescription(fn () => $this->overwrite_existing_data
                                            ? 'Vous êtes sur le point de SUPPRIMER TOUS les posts, catégories, tags et séries existants. Cette action est IRRÉVERSIBLE. Les utilisateurs ne seront pas supprimés. / You are about to DELETE ALL existing blog posts, categories, tags and series. This action is IRREVERSIBLE. Users will not be deleted.'
                                            : 'Les données existantes seront conservées. Seules les nouvelles données seront importées. / Existing data will be preserved. Only new data will be imported.')
                                        ->modalSubmitActionLabel(fn () => $this->overwrite_existing_data
                                            ? 'Oui, tout supprimer et importer / Yes, delete all and import'
                                            : 'Importer / Import')
                                        ->action(function () {
                                            Log::info('Blogr Import: Starting import process', [
                                                'import_file' => $this->import_file,
                                                'import_file_type' => gettype($this->import_file),
                                                'import_file_count' => is_array($this->import_file) ? count($this->import_file) : 'N/A',
                                            ]);

                                            // Validate that import_file is not empty
                                            if (empty($this->import_file) || ! is_array($this->import_file) || count($this->import_file) === 0) {
                                                Log::warning('Blogr Import: No file selected', [
                                                    'import_file' => $this->import_file,
                                                ]);

                                                Notification::make()
                                                    ->title('Import Failed')
                                                    ->body('Please select a file to import.')
                                                    ->danger()
                                                    ->send();

                                                return;
                                            }

                                            try {
                                                $importService = app(BlogrImportService::class);

                                                // Get the first file from the array safely
                                                // Livewire stores uploaded files in an associative array with UUID keys
                                                // The value is a TemporaryUploadedFile object
                                                $fileName = null;
                                                $filePath = null;

                                                // Get the first value regardless of the key
                                                $firstFile = reset($this->import_file);

                                                Log::info('Blogr Import: Analyzing uploaded file', [
                                                    'firstFile' => $firstFile,
                                                    'firstFile_type' => gettype($firstFile),
                                                    'is_object' => is_object($firstFile),
                                                ]);

                                                // Handle TemporaryUploadedFile object from Livewire
                                                if (is_object($firstFile) && method_exists($firstFile, 'getRealPath')) {
                                                    $filePath = $firstFile->getRealPath();
                                                    $fileName = $firstFile->getClientOriginalName();

                                                    Log::info('Blogr Import: TemporaryUploadedFile detected', [
                                                        'realPath' => $filePath,
                                                        'originalName' => $fileName,
                                                    ]);
                                                } elseif (is_string($firstFile)) {
                                                    // Fallback for string paths
                                                    $fileName = $firstFile;

                                                    Log::info('Blogr Import: String path detected', [
                                                        'fileName' => $fileName,
                                                    ]);
                                                } else {
                                                    Log::error('Blogr Import: Unexpected file format', [
                                                        'firstFile' => $firstFile,
                                                        'type' => gettype($firstFile),
                                                    ]);
                                                }

                                                // Validate we have a file path
                                                if (! $filePath && $fileName) {
                                                    // Try different path combinations
                                                    $possiblePaths = [
                                                        storage_path('app/'.$fileName),
                                                        storage_path('app/public/'.$fileName),
                                                        storage_path('app/private/'.$fileName),
                                                        $fileName, // In case it's already a full path
                                                    ];

                                                    foreach ($possiblePaths as $path) {
                                                        if (File::exists($path)) {
                                                            $filePath = $path;
                                                            break;
                                                        }
                                                    }

                                                    Log::info('Blogr Import: Checking file paths', [
                                                        'fileName' => $fileName,
                                                        'checked_paths' => $possiblePaths,
                                                        'found_path' => $filePath,
                                                    ]);
                                                }

                                                if (! $filePath || ! File::exists($filePath)) {
                                                    Log::error('Blogr Import: Invalid file or path', [
                                                        'fileName' => $fileName,
                                                        'filePath' => $filePath,
                                                        'import_file_raw' => $this->import_file,
                                                    ]);

                                                    Notification::make()
                                                        ->title('Import Failed')
                                                        ->body('No valid file found in upload. Please try uploading the file again.')
                                                        ->danger()
                                                        ->send();

                                                    return;
                                                }

                                                // Check if file exists
                                                if (! $filePath || ! File::exists($filePath)) {
                                                    Log::error('Blogr Import: File not found', [
                                                        'filePath' => $filePath,
                                                        'storage_path' => storage_path('app'),
                                                    ]);

                                                    Notification::make()
                                                        ->title('Import Failed')
                                                        ->body('The uploaded file could not be found. Please try uploading again.')
                                                        ->danger()
                                                        ->send();

                                                    return;
                                                }

                                                Log::info('Blogr Import: Starting import from file', [
                                                    'filePath' => $filePath,
                                                    'fileSize' => File::size($filePath),
                                                    'overwrite_existing_data' => $this->overwrite_existing_data,
                                                    'default_author_id' => $this->default_author_id,
                                                ]);

                                                $result = $importService->importFromFile($filePath, [
                                                    'overwrite' => $this->overwrite_existing_data,
                                                    'default_author_id' => $this->default_author_id,
                                                ]);

                                                Log::info('Blogr Import: Import completed', [
                                                    'success' => $result['success'] ?? false,
                                                    'result' => $result,
                                                ]);

                                                if ($result['success']) {
                                                    // Build detailed success message
                                                    $stats = $result['results'] ?? [];
                                                    $messages = [];

                                                    foreach ($stats as $type => $counts) {
                                                        if (is_array($counts)) {
                                                            $imported = $counts['imported'] ?? 0;
                                                            $updated = $counts['updated'] ?? 0;
                                                            $skipped = $counts['skipped'] ?? 0;

                                                            if ($imported > 0 || $updated > 0) {
                                                                $parts = [];
                                                                if ($imported > 0) {
                                                                    $parts[] = "{$imported} new";
                                                                }
                                                                if ($updated > 0) {
                                                                    $parts[] = "{$updated} updated";
                                                                }
                                                                if ($skipped > 0) {
                                                                    $parts[] = "{$skipped} skipped";
                                                                }
                                                                $messages[] = ucfirst(str_replace('_', ' ', $type)).': '.implode(', ', $parts);
                                                            }
                                                        }
                                                    }

                                                    $body = ! empty($messages)
                                                        ? implode(' | ', $messages)
                                                        : 'Data imported successfully.';

                                                    Notification::make()
                                                        ->title('Import Successful')
                                                        ->body($body)
                                                        ->success()
                                                        ->duration(10000) // 10 seconds to read the stats
                                                        ->send();

                                                    // Clear the file upload after successful import
                                                    $this->import_file = [];
                                                } else {
                                                    Log::error('Blogr Import: Import failed', [
                                                        'errors' => $result['errors'] ?? [],
                                                    ]);

                                                    Notification::make()
                                                        ->title('Import Failed')
                                                        ->body('Import failed: '.implode(', ', $result['errors'] ?? ['Unknown error']))
                                                        ->danger()
                                                        ->send();
                                                }
                                            } catch (\Exception $e) {
                                                Log::error('Blogr Import: Exception occurred', [
                                                    'exception' => $e->getMessage(),
                                                    'trace' => $e->getTraceAsString(),
                                                ]);

                                                Notification::make()
                                                    ->title('Import Failed')
                                                    ->body('An error occurred during import: '.$e->getMessage())
                                                    ->danger()
                                                    ->send();
                                            }
                                        }),
                                ])
                                ->columnSpanFull(),

                            Section::make('Backup Commands')
                                ->description('Available Artisan commands for backup operations')
                                ->schema([
                                    Placeholder::make('export_command')
                                        ->label('Export Command')
                                        ->content('php artisan blogr:export [--output=path/to/file.json] [--include-media]'),

                                    Placeholder::make('import_command')
                                        ->label('Import Command')
                                        ->content('php artisan blogr:import path/to/file.json [--skip-existing]'),

                                ])
                                ->headerActions([
                                    Action::make('run_export')
                                        ->label('Run Export Now')
                                        ->icon('heroicon-o-play')
                                        ->action(function () {
                                            try {
                                                $exportService = new BlogrExportService;
                                                $filePath = $exportService->exportToFile();

                                                $size = File::size($filePath);
                                                $sizeFormatted = $this->formatBytes($size);

                                                Notification::make()
                                                    ->title('Export Successful')
                                                    ->body("Export file created: {$filePath} ({$sizeFormatted})")
                                                    ->success()
                                                    ->send();
                                            } catch (\Exception $e) {
                                                Notification::make()
                                                    ->title('Export Failed')
                                                    ->body('Error: '.$e->getMessage())
                                                    ->danger()
                                                    ->send();
                                            }
                                        }),
                                ])
                                ->columnSpanFull(),
                        ]),

                    // ========================================
                    // AI TRANSLATION TAB
                    // ========================================
                    Tabs\Tab::make('AI Translation')
                        ->icon('heroicon-o-language')
                        ->schema([
                            Placeholder::make('translation_usage_recap')
                                ->label('Monthly Usage')
                                ->content(function () {
                                    $provider = $this->translation_provider;
                                    if (! $provider || $provider === 'none') {
                                        return __('blogr::blogr.translation.select_provider');
                                    }

                                    $stats = app(TranslationUsageService::class)->getUsageStats($provider);

                                    if (! $stats) {
                                        return __('blogr::blogr.translation.no_data');
                                    }

                                    return json_encode($stats);
                                })
                                ->view('blogr::filament.components.translation-usage-card')
                                ->visible(fn () => $this->translation_provider !== 'none')
                                ->columnSpanFull(),

                            Section::make('AI Translation Service')
                                ->description('Configure AI-powered translation for your CMS pages. Requires an API key or a self-hosted LibreTranslate server.')
                                ->schema([
                                    Select::make('translation_provider')
                                        ->label('Provider')
                                        ->options([
                                            'none' => 'Disabled',
                                            'libretranslate' => 'LibreTranslate (self-hosted, free)',
                                            'azure' => 'Azure Translator (2M chars/month free)',
                                            'google' => 'Google Cloud Translation (500K chars/month free)',
                                            'openai' => 'OpenAI (GPT-4o-mini, paid)',
                                        ])
                                        ->default('none')
                                        ->live()
                                        ->required(),
                                    TextInput::make('translation_libretranslate_url')
                                        ->label('LibreTranslate URL')
                                        ->placeholder('http://localhost:5000')
                                        ->helperText('🔗 https://github.com/LibreTranslate/LibreTranslate')
                                        ->visible(fn () => $this->translation_provider === 'libretranslate'),
                                    TextInput::make('translation_azure_api_key')
                                        ->label('Azure Translator Key')
                                        ->password()
                                        ->helperText('🔗 Créer un compte : https://azure.microsoft.com/products/cognitive-services/translator/')
                                        ->visible(fn () => $this->translation_provider === 'azure'),
                                    TextInput::make('translation_azure_region')
                                        ->label('Azure Region')
                                        ->placeholder('westeurope')
                                        ->helperText('Ex: westeurope, eastus, northeurope')
                                        ->visible(fn () => $this->translation_provider === 'azure'),
                                    TextInput::make('translation_google_api_key')
                                        ->label('Google Cloud API Key')
                                        ->password()
                                        ->helperText('🔗 Créer un compte : https://cloud.google.com/translate')
                                        ->visible(fn () => $this->translation_provider === 'google'),
                                    TextInput::make('translation_openai_api_key')
                                        ->label('OpenAI API Key')
                                        ->password()
                                        ->helperText('🔗 https://platform.openai.com/api-keys')
                                        ->visible(fn () => $this->translation_provider === 'openai'),
                                ]),
                        ]),
                ]),

        ];
    }

    /**
     * Get reading time text formats for available locales
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
            if (! empty($this->seo_site_names[$locale])) {
                $names[$locale] = $this->seo_site_names[$locale];
            }
        }

        return $names;
    }

    private function getSeoDefaultTitles(): array
    {
        $availableLocales = array_map('trim', explode(',', $this->locales_available ?? 'en'));
        $titles = [];

        foreach ($availableLocales as $locale) {
            if (! empty($this->seo_default_titles[$locale])) {
                $titles[$locale] = $this->seo_default_titles[$locale];
            }
        }

        return $titles;
    }

    private function getSeoDefaultDescriptions(): array
    {
        $availableLocales = array_map('trim', explode(',', $this->locales_available ?? 'en'));
        $descriptions = [];

        foreach ($availableLocales as $locale) {
            if (! empty($this->seo_default_descriptions[$locale])) {
                $descriptions[$locale] = $this->seo_default_descriptions[$locale];
            }
        }

        return $descriptions;
    }

    private function getSeoDefaultKeywords(): array
    {
        $availableLocales = array_map('trim', explode(',', $this->locales_available ?? 'en'));
        $keywords = [];

        foreach ($availableLocales as $locale) {
            if (! empty($this->seo_default_keywords[$locale])) {
                $keywords[$locale] = $this->seo_default_keywords[$locale];
            }
        }

        return $keywords;
    }

    public function save(): void
    {
        // Handle logo file upload FIRST - persist the file if it's a temporary upload
        $logoPath = null;
        if (! empty($this->navigation_logo)) {
            // Filament FileUpload returns an associative array with UUID keys
            // Get the first value (could be at key 0 or a UUID)
            $logoFile = is_array($this->navigation_logo) ? reset($this->navigation_logo) : $this->navigation_logo;

            if ($logoFile) {
                // Check type of file data
                if (is_object($logoFile)) {
                    // It's a TemporaryUploadedFile object, store it permanently
                    if (method_exists($logoFile, 'store')) {
                        $logoPath = $logoFile->store('blogr/logos', 'public');
                    } elseif (method_exists($logoFile, 'storeAs')) {
                        // Alternative method
                        $filename = $logoFile->getClientOriginalName();
                        $logoPath = $logoFile->storeAs('blogr/logos', $filename, 'public');
                    }
                } elseif (is_string($logoFile)) {
                    // It's a string - could be existing path or livewire reference
                    if (str_starts_with($logoFile, 'livewire-file:')) {
                        // Livewire temporary file reference - this shouldn't happen with proper FileUpload config
                        // Let's log this and skip
                        \Log::warning('BlogrSettings: Logo upload returned livewire-file reference instead of object', [
                            'value' => $logoFile,
                        ]);
                    } else {
                        // It's already a stored path (existing file)
                        $logoPath = $logoFile;
                    }
                }
            }
        }

        // Read admin_path from form state first (most reliable in Livewire context)
        $adminPath = 'admin';
        try {
            if (property_exists($this, 'form') && $this->form !== null) {
                $formState = $this->form->getState();
                $adminPath = $formState['admin_path'] ?? $this->admin_path ?? 'admin';
            } else {
                $adminPath = $this->admin_path ?? 'admin';
            }
        } catch (\Throwable $e) {
            $adminPath = $this->admin_path ?? 'admin';
        }
        $data = [
            'admin_path' => $adminPath,
            'posts_per_page' => $this->posts_per_page,
            'route' => [
                'prefix' => $this->route_prefix,
                'frontend' => [
                    'enabled' => $this->route_frontend_enabled,
                ],
                'homepage' => $this->route_homepage, // Keep for backward compatibility
            ],
            'homepage' => [
                'type' => $this->homepage_type ?? 'blog',
            ],
            'cms' => [
                'enabled' => $this->cms_enabled ?? false,
                'prefix' => $this->cms_prefix ?? '',
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
                'auto_detect' => $this->locales_auto_detect,
                'disabled' => is_array($this->locales_disabled ?? []) ? $this->locales_disabled : [],
            ],
            'series' => [
                'enabled' => $this->series_enabled,
                'max_visible_posts' => $this->series_max_visible_posts ?? 10,
                // Convert array back to string for config storage
                'default_image' => is_array($this->series_default_image) && ! empty($this->series_default_image)
                    ? $this->series_default_image[0]
                    : ($this->series_default_image ?? '/vendor/blogr/images/default-series.svg'),
                'subtitle' => $this->series_subtitles ?? [],
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
                    'logo' => $logoPath,
                    'logo_display' => $this->navigation_logo_display ?? 'text',
                    'show_language_switcher' => $this->navigation_show_language_switcher,
                    'show_theme_switcher' => $this->navigation_show_theme_switcher,
                    'auto_add_blog' => $this->navigation_auto_add_blog ?? false,
                    'menu_items' => $this->cleanMenuItems($this->navigation_menu_items ?? []),
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
                    'bg_color' => $this->theme_bg_color,
                    'bg_color_dark' => $this->theme_bg_color_dark,
                    'btn_color' => $this->theme_btn_color,
                    'btn_color_dark' => $this->theme_btn_color_dark,
                    'text_color' => $this->theme_text_color,
                    'text_color_dark' => $this->theme_text_color_dark,
                    'highlight_color' => $this->theme_highlight_color,
                    'highlight_color_dark' => $this->theme_highlight_color_dark,
                    'testimonial_bg' => $this->theme_testimonial_bg,
                    'testimonial_bg_dark' => $this->theme_testimonial_bg_dark,
                    'testimonial_text' => $this->theme_testimonial_text,
                    'testimonial_text_dark' => $this->theme_testimonial_text_dark,
                    'font_family' => $this->font_family ?? 'Instrument Sans',
                    'font_custom_name' => $this->font_custom_name,
                    'font_custom_url' => $this->font_custom_url,
                    'font_custom_google' => $this->font_custom_google,
                    'header_bg' => $this->theme_header_bg,
                    'header_bg_dark' => $this->theme_header_bg_dark,
                    'header_text' => $this->theme_header_text,
                    'header_text_dark' => $this->theme_header_text_dark,
                    'footer_bg' => $this->theme_footer_bg,
                    'footer_bg_dark' => $this->theme_footer_bg_dark,
                    'footer_text' => $this->theme_footer_text,
                    'footer_text_dark' => $this->theme_footer_text_dark,
                    'header_brightness' => $this->header_brightness,
                    'header_brightness_dark' => $this->header_brightness_dark,
                    'footer_brightness' => $this->footer_brightness,
                    'footer_brightness_dark' => $this->footer_brightness_dark,
                    'preset' => $this->theme_preset ?? '',
                ],
                'appearance' => [
                    'blog_card_bg' => $this->appearance_blog_card_bg,
                    'blog_card_bg_dark' => $this->appearance_blog_card_bg_dark,
                    'series_card_bg' => $this->appearance_series_card_bg,
                    'series_card_bg_dark' => $this->appearance_series_card_bg_dark,
                ],
                'back_to_top' => [
                    'enabled' => $this->back_to_top_enabled,
                    'shape' => $this->back_to_top_shape,
                    'color' => $this->back_to_top_color,
                ],
            ],
            'sitemap' => [
                'enabled' => $this->sitemap_enabled ?? true,
            ],
            'rss' => [
                'enabled' => $this->rss_enabled ?? true,
                'items_limit' => $this->rss_items_limit ?? 20,
                'show_in_header' => $this->rss_show_in_header ?? false,
                'show_in_footer' => $this->rss_show_in_footer ?? false,
            ],
            'contact' => [
                'to_email' => $this->contact_to_email ?? '',
            ],
            'analytics' => [
                'enabled' => $this->analytics_enabled,
                'provider' => $this->analytics_provider,
                'google' => [
                    'measurement_id' => $this->analytics_google_measurement_id,
                ],
                'plausible' => [
                    'domain' => $this->analytics_plausible_domain,
                    'src' => $this->analytics_plausible_src,
                ],
                'umami' => [
                    'website_id' => $this->analytics_umami_website_id,
                    'src' => $this->analytics_umami_src,
                ],
                'matomo' => [
                    'url' => $this->analytics_matomo_url,
                    'site_id' => $this->analytics_matomo_site_id,
                ],
                'anonymize_ip' => $this->analytics_anonymize_ip ?? true,
            ],
            'mail' => [
                'provider' => $this->mail_provider ?? '',
                'from' => [
                    'address' => $this->mail_from_address ?? '',
                    'name' => $this->mail_from_name ?? '',
                ],
                'brevo' => [
                    'username' => $this->mail_brevo_username ?? '',
                    'password' => $this->mail_brevo_password ?? '',
                ],
            ],
            'translation' => [
                'provider' => $this->translation_provider ?? 'none',
                'libretranslate' => [
                    'url' => $this->translation_libretranslate_url ?? 'http://localhost:5000',
                ],
                'azure' => [
                    'api_key' => $this->translation_azure_api_key ?? '',
                    'region' => $this->translation_azure_region ?? 'westeurope',
                ],
                'google' => [
                    'api_key' => $this->translation_google_api_key ?? '',
                ],
                'openai' => [
                    'api_key' => $this->translation_openai_api_key ?? '',
                ],
            ],
        ];

        $data['enable_avatar_upload'] = $this->enable_avatar_upload ?? true;

        $data['auto_save_interval'] = $this->auto_save_interval ?? 30;

        // Apply at runtime immediately
        config()->set('blogr.enable_avatar_upload', $this->enable_avatar_upload ?? true);
        config()->set('blogr.auto_save_interval', $this->auto_save_interval ?? 30);

        // Log logo path for debugging
        \Log::info('BlogrSettings: Saving logo path to config', [
            'logoPath' => $logoPath,
            'navigation_logo_raw' => $this->navigation_logo,
        ]);

        // Apply admin_path at runtime and persist to .env for reliability
        config()->set('blogr.admin_path', $adminPath);
        $envWritten = $this->updateEnvFile(['BLOGR_ADMIN_PATH' => $adminPath]);

        // Update the config file
        $this->updateConfigFile($data);

        // Clear config cache so subsequent requests use the updated files
        Artisan::call('config:clear');

        // Re-apply admin_path at runtime after cache clear
        config()->set('blogr.admin_path', $adminPath);
        // Write mail credentials to .env if Brevo is configured
        if ($this->mail_provider === 'brevo' && $this->mail_brevo_password) {
            $this->updateEnvFile([
                'MAIL_MAILER' => 'smtp',
                'MAIL_HOST' => 'smtp-relay.brevo.com',
                'MAIL_PORT' => '587',
                'MAIL_USERNAME' => $this->mail_brevo_username ?? '',
                'MAIL_PASSWORD' => $this->mail_brevo_password,
                'MAIL_ENCRYPTION' => 'tls',
                'MAIL_FROM_ADDRESS' => $this->mail_from_address ?? '',
                'MAIL_FROM_NAME' => $this->mail_from_name ?? '',
            ]);

            // Also apply mail config at runtime so test email works immediately
            config()->set('mail.mailers.smtp', [
                'transport' => 'smtp',
                'host' => 'smtp-relay.brevo.com',
                'port' => 587,
                'encryption' => 'tls',
                'username' => $this->mail_brevo_username ?? '',
                'password' => $this->mail_brevo_password,
                'timeout' => null,
            ]);

            config()->set('mail.from.address', $this->mail_from_address ?? '');
            config()->set('mail.from.name', $this->mail_from_name ?? '');
        }

        $envPath = app()->environmentFilePath();
        $envWritable = $envPath && is_writable($envPath);

        $body = __('blogr::blogr.settings.saved_successfully');

        if ($this->mail_provider === 'brevo' && ! $envWritable) {
            $body .= "\n".__('blogr::blogr.settings.env_not_writable');
        }

        $body .= "\n".__('blogr::blogr.settings.run_sync_command');

        Notification::make()
            ->title(__('blogr::blogr.settings.saved_successfully'))
            ->success()
            ->body($body)
            ->send();

        // Re-hydrate form with fresh config values so UI reflects saved state immediately
        $fresh = config('blogr', []);
        $this->font_family = $fresh['ui']['theme']['font_family'] ?? 'Instrument Sans';
        $this->font_preview = $this->font_family;
    }

    private function updateConfigFile(array $data): void
    {
        if (app()->environment('testing')) {
            // Update in-memory config instead of writing to file
            foreach (Arr::dot($data) as $key => $value) {
                config()->set("blogr.{$key}", $value);
            }

            return;
        }

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

    /**
     * Update .env file with mail configuration values.
     */
    private function updateEnvFile(array $values): bool
    {
        $envPath = app()->environmentFilePath();

        if (! File::exists($envPath)) {
            return false;
        }

        $envContent = File::get($envPath);

        foreach ($values as $key => $value) {
            $escaped = str_replace(['\\', '"'], ['\\\\', '\\"'], $value);

            if (preg_match("/^{$key}=/m", $envContent)) {
                // Key exists — replace it
                $envContent = preg_replace(
                    "/^{$key}=.*$/m",
                    "{$key}=\"{$escaped}\"",
                    $envContent
                );
            } else {
                // Key doesn't exist — append it
                $envContent .= "\n{$key}=\"{$escaped}\"\n";
            }
        }

        return File::put($envPath, $envContent) !== false;
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
            $result .= str_repeat('    ', $indent).']';

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

    /**
     * Format bytes to human-readable string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Clean menu items by removing empty children and invalid items
     */
    private function cleanMenuItems(array $menuItems): array
    {
        $cleaned = [];

        foreach ($menuItems as $key => $item) {
            // Skip items with no type or invalid type
            if (empty($item['type'])) {
                continue;
            }

            // Clean children if they exist
            if (isset($item['children']) && is_array($item['children'])) {
                $cleanedChildren = [];

                foreach ($item['children'] as $childKey => $child) {
                    // Only keep children with valid labels and type
                    if (! empty($child['type']) && isset($child['labels']) && ! empty($child['labels'])) {
                        // Filter out null/empty labels
                        $validLabels = array_filter($child['labels'], function ($label) {
                            return ! empty($label['label']);
                        });

                        if (! empty($validLabels)) {
                            $child['labels'] = $validLabels;
                            $cleanedChildren[$childKey] = $child;
                        }
                    }
                }

                $item['children'] = $cleanedChildren;
            }

            $cleaned[$key] = $item;
        }

        return $cleaned;
    }
}
