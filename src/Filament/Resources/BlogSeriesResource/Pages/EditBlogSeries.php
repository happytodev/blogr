<?php

namespace Happytodev\Blogr\Filament\Resources\BlogSeriesResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Happytodev\Blogr\Filament\Resources\BlogSeriesResource;

class EditBlogSeries extends EditRecord
{
    protected static string $resource = BlogSeriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
