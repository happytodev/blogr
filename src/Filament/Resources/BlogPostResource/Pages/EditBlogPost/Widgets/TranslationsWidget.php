<?php

namespace Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages\EditBlogPost\Widgets;

use Filament\Widgets\Widget;

class TranslationsWidget extends Widget
{
    protected string $view = 'blogr::filament.resources.blog-post-resource.pages.partials.translations-widget';
    
    public $record;
    
    public function mount($record = null): void
    {
        $this->record = $record;
    }
}
