<?php

namespace Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages;

use BackedEnum;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Tables\Actions\CreateAction;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Filament\Resources\Pages\ManageRelatedRecords;
use Happytodev\Blogr\Filament\Resources\BlogPostResource;

class ManageBlogPostTranslations extends ManageRelatedRecords
{
    protected static string $resource = BlogPostResource::class;

    protected static string $relationship = 'translations';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-language';

    public static function getNavigationLabel(): string
    {
        return 'Translations';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Select::make('locale')
                    ->label('Language')
                    ->options([
                        'en' => 'English',
                        'fr' => 'Français',
                        'es' => 'Español',
                        'de' => 'Deutsch',
                        'it' => 'Italiano',
                        'pt' => 'Português',
                    ])
                    ->required()
                    ->unique(ignorable: fn ($record) => $record)
                    ->disabled(fn (?BlogPostTranslation $record) => $record !== null)
                    ->helperText('Language cannot be changed after creation'),

                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->helperText('URL-friendly version of the title'),

                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('tldr')
                    ->label('TL;DR')
                    ->maxLength(500)
                    ->rows(3)
                    ->helperText('A brief summary of the post (optional)'),

                Forms\Components\MarkdownEditor::make('content')
                    ->required()
                    ->columnSpanFull()
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
                    ]),

                Forms\Components\Textarea::make('seo_description')
                    ->label('SEO Description')
                    ->maxLength(160)
                    ->rows(2)
                    ->helperText('Recommended: 150-160 characters'),

                Forms\Components\TextInput::make('reading_time')
                    ->label('Reading Time (minutes)')
                    ->numeric()
                    ->minValue(1)
                    ->helperText('Estimated reading time in minutes')
                    ->default(fn (Forms\Get $get) => $this->calculateReadingTime($get('content'))),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('locale')
                    ->label('Language')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'en' => 'English',
                        'fr' => 'Français',
                        'es' => 'Español',
                        'de' => 'Deutsch',
                        'it' => 'Italiano',
                        'pt' => 'Português',
                        default => $state,
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->copyable(),

                Tables\Columns\TextColumn::make('reading_time')
                    ->label('Reading Time')
                    ->suffix(' min')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('locale')
                    ->label('Language')
                    ->options([
                        'en' => 'English',
                        'fr' => 'Français',
                        'es' => 'Español',
                        'de' => 'Deutsch',
                        'it' => 'Italiano',
                        'pt' => 'Português',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (empty($data['reading_time']) && !empty($data['content'])) {
                            $data['reading_time'] = $this->calculateReadingTime($data['content']);
                        }
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (!empty($data['content'])) {
                            $data['reading_time'] = $this->calculateReadingTime($data['content']);
                        }
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No translations yet')
            ->emptyStateDescription('Create translations of this blog post in different languages.')
            ->emptyStateIcon('heroicon-o-language');
    }

    protected function calculateReadingTime(?string $content): int
    {
        if (empty($content)) {
            return 1;
        }

        $wordCount = str_word_count(strip_tags($content));
        return max(1, (int) ceil($wordCount / 200));
    }
}
