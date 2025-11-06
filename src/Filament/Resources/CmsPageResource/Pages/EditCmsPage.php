<?php

namespace Happytodev\Blogr\Filament\Resources\CmsPageResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Happytodev\Blogr\Filament\Resources\CmsPageResource;

class EditCmsPage extends EditRecord
{
    protected static string $resource = CmsPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    // Le Repeater avec relationship() gère automatiquement les traductions
}
