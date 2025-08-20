<?php

namespace Happytodev\Blogr\Filament\Resources\Categories\Pages;

use Happytodev\Blogr\Filament\Resources\Categories\CategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
}
