<?php

namespace Happytodev\Blogr\Filament\Resources;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Happytodev\Blogr\Enums\CmsPageTemplate;
use Happytodev\Blogr\Filament\Resources\CmsPageResource\Pages;
use Happytodev\Blogr\Filament\Resources\CmsPageResource\Pages\EditCmsPageTranslation;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Services\LocaleService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

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
        return __('blogr::resources.cms_page.navigation_label') ?? 'Pages CMS';
    }

    public static function getPluralLabel(): string
    {
        return __('blogr::resources.cms_page.plural_label') ?? 'Pages CMS';
    }

    public static function getLabel(): string
    {
        return __('blogr::resources.cms_page.label') ?? 'Page CMS';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Informations générales'))
                    ->description(__('Configurez les paramètres généraux de votre page'))
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->label(__('Slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->rule(Rule::notIn(config('blogr.cms.reserved_slugs', [])))
                            ->helperText(__('URL de la page (ex: a-propos, contact)'))
                            ->maxLength(255)
                            ->columnSpan(2),

                        Forms\Components\Select::make('template')
                            ->label(__('Template'))
                            ->required()
                            ->options(CmsPageTemplate::class)
                            ->default(CmsPageTemplate::DEFAULT)
                            ->helperText(__('Mise en page de la page'))
                            ->columnSpan(2),

                        Forms\Components\Select::make('default_locale')
                            ->label(__('Langue par défaut'))
                            ->options(function () {
                                $localeService = app(LocaleService::class);
                                $locales = $localeService->getAvailable();

                                return collect($locales)->mapWithKeys(fn ($locale) => [$locale => $localeService->localeLabel($locale)]);
                            })
                            ->default(config('blogr.locales.default', 'fr'))
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_homepage')
                            ->label(__('Page d\'accueil'))
                            ->default(false)
                            ->inline(false)
                            ->helperText(__('Définir cette page comme page d\'accueil du site'))
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label(__('Date de publication'))
                            ->default(now())
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_published')
                            ->label(__('Publié'))
                            ->default(false)
                            ->inline(false)
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

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

    public static function getGloballySearchableAttributes(): array
    {
        return ['slug', 'translations.title'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with('translations');
    }

    public static function getGlobalSearchResultTitle($record): string
    {
        $translation = $record->translations->first();
        $title = $translation ? $translation->title : $record->slug;

        return "{$title} ({$record->slug})";
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
            'edit-translation' => EditCmsPageTranslation::route('/{record}/translations/{translation}/edit'),
        ];
    }
}
