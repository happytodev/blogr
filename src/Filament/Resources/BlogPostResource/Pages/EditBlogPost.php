<?php

namespace Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Happytodev\Blogr\Filament\Resources\BlogPostResource;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;
    
    // Notification is dispatched from BlogPost model's created() hook for new posts
    // For updates, admins don't receive notifications (as per original design)
}

