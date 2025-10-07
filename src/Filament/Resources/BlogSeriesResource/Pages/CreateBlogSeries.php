<?php

namespace Happytodev\Blogr\Filament\Resources\BlogSeriesResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Happytodev\Blogr\Filament\Resources\BlogSeriesResource;

class CreateBlogSeries extends CreateRecord
{
    protected static string $resource = BlogSeriesResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
