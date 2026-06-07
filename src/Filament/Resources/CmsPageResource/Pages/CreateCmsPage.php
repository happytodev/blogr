<?php

namespace Happytodev\Blogr\Filament\Resources\CmsPageResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Happytodev\Blogr\Filament\Resources\CmsPageResource;

class CreateCmsPage extends CreateRecord
{
    protected static string $resource = CmsPageResource::class;
    
    // Les traductions sont ajoutées après création via les liens dans l'en-tête
}
