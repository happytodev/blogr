<?php

namespace Happytodev\Blogr\Filament\Resources\CmsPageResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Happytodev\Blogr\Filament\Resources\CmsPageResource;
use Happytodev\Blogr\Models\CmsPageTranslation;

class CreateCmsPage extends CreateRecord
{
    protected static string $resource = CmsPageResource::class;

    protected function afterCreate(): void
    {
        $record = $this->getRecord();

        CmsPageTranslation::create([
            'cms_page_id' => $record->id,
            'locale' => $record->default_locale,
            'slug' => $record->slug,
            'title' => $record->slug,
            'blocks' => $record->template->defaultBlocks(),
        ]);
    }
}
