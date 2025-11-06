<?php

namespace Happytodev\Blogr\Filament\Resources\CmsPageResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Happytodev\Blogr\Filament\Resources\CmsPageResource;

class CreateCmsPage extends CreateRecord
{
    protected static string $resource = CmsPageResource::class;
    
    // Le Repeater avec relationship() gère automatiquement les traductions
}
