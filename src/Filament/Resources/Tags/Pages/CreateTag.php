<?php

namespace Happytodev\Blogr\Filament\Resources\Tags\Pages;

use Happytodev\Blogr\Filament\Resources\Tags\TagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTag extends CreateRecord
{
    protected static string $resource = TagResource::class;
}
