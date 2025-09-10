<?php

namespace Happytodev\Blogr\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\File;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Artisan;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;

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
    public ?string $colors_primary = null;
    public ?string $blog_index_cards_colors_background = null;
    public ?string $blog_index_cards_colors_top_border = null;
    public ?int $reading_speed_words_per_minute = null;
    public ?string $reading_time_text_format = null;
    public ?bool $reading_time_enabled = null;
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

    public function mount(): void
    {
        // Load current config values
        $config = config('blogr', []);

        // Set form properties from config
        $this->posts_per_page = $config['posts_per_page'] ?? 10;
        $this->route_prefix = $config['route']['prefix'] ?? 'blog';
        $this->colors_primary = $config['colors']['primary'] ?? '#3b82f6';
        $this->blog_index_cards_colors_background = $config['blog_index']['cards']['colors']['background'] ?? 'bg-white';
        $this->blog_index_cards_colors_top_border = $config['blog_index']['cards']['colors']['top_border'] ?? 'border-t-4 border-blue-500';
        $this->reading_speed_words_per_minute = $config['reading_speed']['words_per_minute'] ?? 200;
        $this->reading_time_text_format = $config['reading_time']['text_format'] ?? 'Reading time: {time} min';
        $this->reading_time_enabled = $config['reading_time']['enabled'] ?? true;
        $this->seo_site_name = $config['seo']['site_name'] ?? env('APP_NAME', 'My Blog');
        $this->seo_default_title = $config['seo']['default_title'] ?? 'Blog';
        $this->seo_default_description = $config['seo']['default_description'] ?? 'Discover our latest articles and insights';
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
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
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
                    ->description('Reading time calculation settings')
                    ->schema([
                        TextInput::make('reading_speed_words_per_minute')
                            ->label('Words Per Minute')
                            ->numeric()
                            ->minValue(100)
                            ->default(200)
                            ->maxValue(400)
                            ->step(50)
                            ->required(),
                        TextInput::make('reading_time_text_format')
                            ->label('Text Format')
                            ->placeholder('Reading time: {time} min')
                            ->required(),
                        Toggle::make('reading_time_enabled')
                            ->label('Enable Reading Time Display')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('SEO Settings')
                    ->description('Search engine optimization configuration')
                    ->schema([
                        TextInput::make('seo_site_name')
                            ->label('Site Name')
                            ->placeholder('My Blog')
                            ->required(),
                        TextInput::make('seo_default_title')
                            ->label('Default Title')
                            ->placeholder('Blog')
                            ->required(),
                        Textarea::make('seo_default_description')
                            ->label('Default Description')
                            ->placeholder('Discover our latest articles and insights')
                            ->rows(2)
                            ->required()
                            ->columnSpan(2),
                        TextInput::make('seo_twitter_handle')
                            ->label('Twitter Handle')
                            ->placeholder('@yourhandle'),
                        TextInput::make('seo_facebook_app_id')
                            ->label('Facebook App ID'),
                    ])
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
            ]);
    }

    public function save(): void
    {
        $data = [
            'posts_per_page' => $this->posts_per_page,
            'route' => [
                'prefix' => $this->route_prefix,
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
                'text_format' => $this->reading_time_text_format,
                'enabled' => $this->reading_time_enabled,
            ],
            'toc' => [
                'enabled' => $this->toc_enabled,
                'strict_mode' => $this->toc_strict_mode,
            ],
            'seo' => [
                'site_name' => $this->seo_site_name,
                'default_title' => $this->seo_default_title,
                'default_description' => $this->seo_default_description,
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
