<?php

namespace Happytodev\Blogr\Filament\Resources\CmsPageResource\Pages;

use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Happytodev\Blogr\Filament\Resources\CmsPages\CmsBlockBuilder;
use Happytodev\Blogr\Filament\Resources\CmsPageResource;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\CmsPageTranslation;

class EditCmsPageTranslation extends EditRecord
{
    protected static string $resource = CmsPageResource::class;

    public ?CmsPage $cmsPage = null;

    public function mount(int | string $record = '', int | string $translation = ''): void
    {
        static::authorizeResourceAccess();

        $this->cmsPage = CmsPage::findOrFail($record);
        $this->record = CmsPageTranslation::findOrFail($translation);

        abort_unless(static::getResource()::canEdit($this->cmsPage), 403);

        $this->fillForm();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Placeholder::make('locale_display')
                    ->label(__('Locale'))
                    ->content(fn () => strtoupper($this->record?->locale ?? '')),

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

                Section::make(__('Content Blocks'))
                    ->description(__('Ajoutez des blocs de contenu pour cette langue'))
                    ->schema([
                        CmsBlockBuilder::make(),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columnSpan(2),
            ])
            ->columns(2);
    }

    protected function authorizeAccess(): void
    {
        static::authorizeResourceAccess();
        abort_unless(static::getResource()::canEdit($this->cmsPage), 403);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label(__('Retour à la page'))
                ->url(fn () => CmsPageResource::getUrl('edit', ['record' => $this->cmsPage]))
                ->color('gray'),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return CmsPageResource::getUrl('edit', ['record' => $this->cmsPage]);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        $locale = strtoupper($this->record?->locale ?? '');
        $title = $this->record?->title ?? '';
        return "Traduction {$locale} — {$title} enregistrée";
    }

    public function getBreadcrumbs(): array
    {
        $resource = static::getResource();
        $breadcrumbs = [
            $resource::getUrl() => $resource::getBreadcrumb(),
            $resource::getUrl('edit', ['record' => $this->cmsPage]) => $this->cmsPage->slug,
        ];

        if ($this->record) {
            $breadcrumbs[] = strtoupper($this->record->locale) . ' — ' . ($this->record->title ?? '');
        }

        return $breadcrumbs;
    }
}
