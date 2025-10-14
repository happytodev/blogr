<?php

namespace Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Happytodev\Blogr\Filament\Resources\BlogPostResource;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;
    
    /**
     * Called on every Livewire request - this ensures the hook persists
     */
    public function booted(): void
    {
        // Register hook to display relation managers AFTER the header (breadcrumb + title)
        // This is called on EVERY Livewire update (including file uploads)
        FilamentView::registerRenderHook(
            PanelsRenderHook::PAGE_HEADER_WIDGETS_BEFORE,
            fn (): View => view('blogr::filament.resources.blog-post-resource.pages.partials.translations-first', [
                'record' => $this->record,
                'relationManagers' => static::getResource()::getRelations(),
            ]),
            scopes: static::class,
        );
    }
    
    /**
     * Override to return empty array so relation managers don't appear
     * in their default position (after form). We display them via hook instead.
     */
    public function getRelationManagers(): array
    {
        return [];
    }
}
