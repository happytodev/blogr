<?php

namespace Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Happytodev\Blogr\Filament\Resources\BlogPostResource;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;
}