<?php

namespace Happytodev\Blogr\Filament\Resources\BlogPosts;

use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Happytodev\Blogr\Models\Category;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Happytodev\Blogr\Models\BlogPost;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        if ($state) {
                            $set('slug', Str::slug($state));
                        }
                    })
                    ->helperText('255 characters maximum.'),
                FileUpload::make('photo')
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
                    ->nullable(),
                Select::make('category_id')
                    ->label('Category')
                    ->options(Category::pluck('name', 'id'))
                    ->default(function () {
                        return Category::where('is_default', true)->first()->id;
                    })
                    ->required(),
                Select::make('tags')
                    ->multiple()
                    ->relationship('tags', 'name')
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->unique()
                            ->maxLength(255),
                    ]),
                MarkdownEditor::make('content')
                    ->required()
                    ->columnSpanFull()
                    ->helperText('Use Markdown syntax for formatting.')
                    ->default(function ($record) {
                        // For existing records, show content without frontmatter
                        if ($record) {
                            return $record->getContentWithoutFrontmatter();
                        }
                        return null;
                    })
                    ->afterStateHydrated(function ($state, $set, $record) {
                        // For existing records, ensure content is loaded without frontmatter
                        if ($record) {
                            $set('content', $record->getContentWithoutFrontmatter());
                        }
                    })
                    ->afterStateUpdated(function ($state, $set, $record) {
                        // When content changes, update the toggle to reflect current frontmatter
                        if ($record && BlogPost::isTocToggleEditableStatic()) {
                            try {
                                $document = \Spatie\YamlFrontMatter\YamlFrontMatter::parse($state);
                                $frontmatter = $document->matter();
                                $isTocDisabled = $frontmatter['disable_toc'] ?? false;

                                // Convert to boolean if it's a string
                                if (is_string($isTocDisabled)) {
                                    $isTocDisabled = filter_var($isTocDisabled, FILTER_VALIDATE_BOOLEAN);
                                }

                                $set('disable_toc', (bool) $isTocDisabled);
                            } catch (\Exception $e) {
                                // If parsing fails, assume no frontmatter
                                $set('disable_toc', false);
                            }
                        }
                    })
                    ->dehydrateStateUsing(function ($state, $record) {
                        // When saving, we need to ensure frontmatter is properly handled
                        if ($record && BlogPost::isTocToggleEditableStatic()) {
                            // Get the current content (which might not have frontmatter due to accessor)
                            $currentContent = $record->getRawOriginal('content');
                            $contentWithoutFrontmatter = $record->getContentWithoutFrontmatter();

                            // If the content in the form is the same as content without frontmatter,
                            // we need to add frontmatter if the toggle is enabled
                            if ($state === $contentWithoutFrontmatter) {
                                $isTocDisabled = $record->isTocDisabled();
                                if ($isTocDisabled) {
                                    return self::updateFrontmatterInContent($state, ['disable_toc' => true]);
                                }
                            }
                        }

                        return $state;
                    }),
                Toggle::make('disable_toc')
                    ->label('Disable Table of Contents')
                    ->default(function ($record) {
                        // Load value from frontmatter if record exists
                        if ($record) {
                            return $record->isTocDisabled();
                        }
                        // Otherwise use default based on global settings
                        return BlogPost::getDefaultTocDisabled();
                    })
                    ->afterStateHydrated(function ($state, $set, $record) {
                        // Ensure the toggle reflects the current frontmatter state
                        if ($record) {
                            $set('disable_toc', $record->isTocDisabled());
                        }
                    })
                    ->disabled(function () {
                        // In strict mode, the toggle is not editable
                        return !BlogPost::isTocToggleEditableStatic();
                    })
                    ->helperText(function () {
                        $strictMode = config('blogr.toc.strict_mode', false);
                        if ($strictMode) {
                            $globalTocEnabled = config('blogr.toc.enabled', true);
                            $statusMessage = $globalTocEnabled
                                ? 'Currently, table of contents are always displayed for all posts.'
                                : 'Currently, table of contents are always disabled for all posts.';

                            return 'TOC setting is controlled globally and cannot be changed per post. ' . $statusMessage;
                        }
                        return 'Disable the automatic table of contents generation for this post.';
                    })
                    ->afterStateUpdated(function (Set $set, Get $get, $state, $record) {
                        if ($record && BlogPost::isTocToggleEditableStatic()) {
                            // Update the frontmatter in the content when toggle changes
                            $content = $get('content');
                            if ($content) {
                                $updatedContent = self::updateFrontmatterInContent($content, ['disable_toc' => $state]);
                                $set('content', $updatedContent);
                            }
                        }
                    })
                    ->dehydrateStateUsing(function ($state, $record) {
                        // Don't save this as a separate field, it's handled in content
                        return $state;
                    }),
                TextInput::make('slug')
                    ->required()
                    ->unique()
                    ->maxLength(255)
                    ->helperText('Autogenerated slug for the blog post but still modifiable. Must be unique.'),
                Hidden::make('user_id')
                    ->default(fn() => Filament::auth()->user()->id),
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
                    ->rules([
                        'nullable',
                        'date',
                        function ($attribute, $value, $fail) {
                            // Allow past dates for existing records (editing published posts)
                            // Only require future dates for new posts or when scheduling
                            if ($value && request()->route() && str_contains(request()->route()->getName(), 'create')) {
                                // For new posts, require future dates or null for immediate publication
                                if (\Carbon\Carbon::parse($value)->isPast()) {
                                    $fail('Publish date must be in the future for new posts.');
                                }
                            }
                            // For existing posts, allow any date (past dates are preserved)
                        }
                    ])
                    ->helperText('Leave empty for immediate publication, or set a future date to schedule publication.'),
                TextInput::make('meta_title')
                    ->label('Meta Title')
                    ->nullable()
                    ->helperText('SEO title for the blog post.'),
                TextInput::make('meta_description')
                    ->label('Meta Description')
                    ->nullable()
                    ->helperText('SEO description for the blog post.'),
                TextInput::make('meta_keywords')
                    ->label('Meta Keywords')
                    ->nullable()
                    ->helperText('SEO keywords for the blog post, separated by commas.'),
                Textarea::make('tldr')
                    ->label('TL;DR')
                    ->columnSpanFull()
                    ->maxLength(255)
                    ->nullable()
                    ->live()
                    ->helperText(function ($state, Textarea $component) {
                        $max = $component->getMaxLength();
                        $remaining = $max - strlen($state);
                        $text = "A brief summary of the blog post, displayed at the top. Remaining characters : $remaining / $max.";
                        return $text;
                    })
            ]);
    }

    /**
     * Update frontmatter in content
     *
     * @param string $content
     * @param array $updates
     * @return string
     */
    protected static function updateFrontmatterInContent(string $content, array $updates): string
    {
        // Extract existing frontmatter
        $lines = explode("\n", $content);
        $frontmatterLines = [];
        $contentLines = [];
        $inFrontmatter = false;
        $frontmatterEndIndex = 0;

        foreach ($lines as $index => $line) {
            if ($line === '---') {
                if (!$inFrontmatter) {
                    $inFrontmatter = true;
                } else {
                    $frontmatterEndIndex = $index;
                    $contentLines = array_slice($lines, $index + 1);
                    break;
                }
            } elseif ($inFrontmatter) {
                $frontmatterLines[] = $line;
            } else {
                $contentLines[] = $line;
            }
        }

        // Parse existing frontmatter
        $originalFrontmatter = [];
        if (!empty($frontmatterLines)) {
            $yaml = implode("\n", $frontmatterLines);
            try {
                $originalFrontmatter = \Symfony\Component\Yaml\Yaml::parse($yaml) ?: [];
            } catch (\Exception $e) {
                $originalFrontmatter = [];
            }
        }

        // Update frontmatter with new values, but only keep meaningful values
        $updatedFrontmatter = array_merge($originalFrontmatter, $updates);

        // Remove disable_toc if it's false and wasn't originally present
        if (isset($updates['disable_toc']) && $updates['disable_toc'] === false && !isset($originalFrontmatter['disable_toc'])) {
            unset($updatedFrontmatter['disable_toc']);
        }

        // If no frontmatter remains, return content without frontmatter
        if (empty($updatedFrontmatter)) {
            return implode("\n", $contentLines);
        }

        // Generate new YAML
        try {
            $newYaml = \Symfony\Component\Yaml\Yaml::dump($updatedFrontmatter, 2, 2);
            return "---\n" . $newYaml . "---\n\n" . implode("\n", $contentLines);
        } catch (\Exception $e) {
            return $content;
        }
    }
}
