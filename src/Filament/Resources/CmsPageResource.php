<?php

namespace Happytodev\Blogr\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Enums\CmsPageTemplate;
use Happytodev\Blogr\Filament\Resources\CmsPageResource\Pages;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater;
use Illuminate\Support\HtmlString;
use Happytodev\Blogr\Filament\Resources\CmsPages\CmsBlockBuilder;

class CmsPageResource extends Resource
{
    protected static ?string $model = CmsPage::class;

    protected static ?int $navigationSort = 1;
    
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-document-text';
    }
    
    public static function getNavigationGroup(): ?string
    {
        return __('CMS');
    }

    public static function getNavigationLabel(): string
    {
        return __('Pages CMS');
    }

    public static function getPluralLabel(): string
    {
        return __('Pages CMS');
    }

    public static function getLabel(): string
    {
        return __('Page CMS');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Informations générales'))
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText(__('URL de la page (ex: a-propos, contact)'))
                            ->maxLength(255),

                        Forms\Components\Select::make('template')
                            ->label(__('Template'))
                            ->required()
                            ->options(CmsPageTemplate::class)
                            ->default(CmsPageTemplate::DEFAULT)
                            ->helperText(__('Mise en page de la page')),

                        Forms\Components\Toggle::make('is_published')
                            ->label(__('Publié'))
                            ->default(false)
                            ->inline(false),

                        Forms\Components\Toggle::make('is_homepage')
                            ->label(__('Page d\'accueil'))
                            ->default(false)
                            ->inline(false)
                            ->helperText(__('Définir cette page comme page d\'accueil du site')),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label(__('Date de publication'))
                            ->default(now())
                            ->required(),

                        Forms\Components\Select::make('default_locale')
                            ->label(__('Langue par défaut'))
                            ->options(function () {
                                $locales = config('blogr.locales.available', ['fr', 'en']);
                                return array_combine($locales, array_map('strtoupper', $locales));
                            })
                            ->default(config('blogr.locales.default', 'fr'))
                            ->required(),
                    ])
                    ->columns(2),

                Section::make(__('Traductions'))
                    ->description(__('Ajoutez des traductions pour différentes langues'))
                    ->schema([
                        Repeater::make('translations')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('locale')
                                    ->label(__('Langue'))
                                    ->options([
                                        'fr' => 'Français',
                                        'en' => 'English',
                                        'es' => 'Español',
                                        'de' => 'Deutsch',
                                    ])
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('title')
                                    ->label(__('Titre'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('slug')
                                    ->label(__('Slug'))
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText(__('URL traduite de la page'))
                                    ->columnSpan(2),

                                Forms\Components\Textarea::make('excerpt')
                                    ->label(__('Extrait'))
                                    ->maxLength(500)
                                    ->rows(3)
                                    ->columnSpan(2),

                                Forms\Components\MarkdownEditor::make('content')
                                    ->label(__('Contenu'))
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('seo_title')
                                    ->label(__('Titre SEO'))
                                    ->maxLength(255)
                                    ->helperText(__('Titre pour les moteurs de recherche (60 caractères max)'))
                                    ->columnSpan(2),

                                Forms\Components\Textarea::make('seo_description')
                                    ->label(__('Description SEO'))
                                    ->maxLength(160)
                                    ->rows(2)
                                    ->helperText(__('Description pour les moteurs de recherche (160 caractères max)'))
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('seo_keywords')
                                    ->label(__('Mots-clés SEO'))
                                    ->maxLength(255)
                                    ->helperText(__('Mots-clés séparés par des virgules'))
                                    ->columnSpan(2),

                                // Content Blocks pour cette traduction
                                Section::make(__('Content Blocks'))
                                    ->description(__('Ajoutez des blocs de contenu pour cette langue'))
                                    ->schema([
                                        CmsBlockBuilder::make(),
                                    ])
                                    ->collapsible()
                                    ->collapsed()
                                    ->columnSpan(2),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->itemLabel(function (array $state): HtmlString {
                                $locale = $state['locale'] ?? 'new';
                                $title = $state['title'] ?? '';
                                
                                $flags = [
                                    'en' => '🇬🇧',
                                    'fr' => '🇫🇷',
                                    'es' => '🇪🇸',
                                    'de' => '🇩🇪',
                                ];
                                
                                $flag = $flags[$locale] ?? '🌐';
                                $localeUpper = strtoupper($locale === 'new' ? 'NEW' : $locale);
                                
                                $label = "<span style='font-size: 1.1rem; font-weight: 600; color: #6366f1;'>{$flag} {$localeUpper}</span>";
                                
                                if ($title) {
                                    $label .= "<span style='color: #374151; margin-left: 0.5rem;'>- {$title}</span>";
                                }
                                
                                return new HtmlString($label);
                            })
                            ->addActionLabel(__('Ajouter une traduction'))
                            ->defaultItems(0),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('Slug'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('translations.title')
                    ->label(__('Titre'))
                    ->searchable()
                    ->getStateUsing(function (CmsPage $record) {
                        $locale = app()->getLocale();
                        $translation = $record->translations()->where('locale', $locale)->first();
                        return $translation?->title ?? $record->translations()->first()?->title ?? '-';
                    }),

                Tables\Columns\TextColumn::make('template')
                    ->label(__('Template'))
                    ->badge()
                    ->formatStateUsing(fn (CmsPageTemplate $state) => $state->label()),

                Tables\Columns\IconColumn::make('is_published')
                    ->label(__('Publié'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_homepage')
                    ->label(__('Accueil'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label(__('Date de publication'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Créé le'))
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label(__('Publié'))
                    ->placeholder(__('Tous'))
                    ->trueLabel(__('Publié'))
                    ->falseLabel(__('Brouillon')),

                Tables\Filters\TernaryFilter::make('is_homepage')
                    ->label(__('Page d\'accueil'))
                    ->placeholder(__('Toutes'))
                    ->trueLabel(__('Oui'))
                    ->falseLabel(__('Non')),

                Tables\Filters\SelectFilter::make('template')
                    ->label(__('Template'))
                    ->options(CmsPageTemplate::class),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCmsPages::route('/'),
            'create' => Pages\CreateCmsPage::route('/create'),
            'edit' => Pages\EditCmsPage::route('/{record}/edit'),
        ];
    }
}
