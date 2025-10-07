<?php

namespace Happytodev\Blogr\Filament\Resources\BlogSeriesResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Happytodev\Blogr\Filament\Resources\BlogSeriesResource;

class ListBlogSeries extends ListRecords
{
    protected static string $resource = BlogSeriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
