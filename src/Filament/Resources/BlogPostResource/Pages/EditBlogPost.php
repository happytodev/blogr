<?php

namespace Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Happytodev\Blogr\Filament\Resources\BlogPostResource;
use Happytodev\Blogr\Models\BlogPost;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;

    // Notification is dispatched from BlogPost model's created() hook for new posts
    // For updates, admins don't receive notifications (as per original design)

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['blog_series_id']) && isset($data['series_position'])) {
            if ($data['series_position'] === 'auto-top') {
                BlogPost::where('blog_series_id', $data['blog_series_id'])
                    ->increment('series_position');
                $data['series_position'] = 1;
            } elseif ($data['series_position'] === 'auto-bottom') {
                $data['series_position'] = null;
            } elseif ($data['series_position'] === 'custom') {
                $data['series_position'] = $data['series_position_custom'] ?? null;
            }
        }
        unset($data['series_position_custom']);

        return $data;
    }
}
