<?php

namespace Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Happytodev\Blogr\Filament\Resources\BlogPostResource;
use Happytodev\Blogr\Jobs\SendPostNotificationJob;
use Happytodev\Blogr\Models\BlogPost;
use Illuminate\Support\Facades\Log;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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

    protected function afterCreate(): void
    {
        /** @var BlogPost $record */
        $record = $this->record;

        Log::info('CreateBlogPost::afterCreate - post created', [
            'post_id' => $record->id,
            'user_id' => $record->user_id,
        ]);

        // Load fresh translations to ensure they're saved by Filament
        $record->load('translations');

        Log::info('CreateBlogPost::afterCreate - translations loaded', [
            'post_id' => $record->id,
            'translations_count' => $record->translations->count(),
        ]);

        // Now execute notification job with fresh post data
        $job = new SendPostNotificationJob($record->id);

        try {
            $job->handle();
            Log::info('CreateBlogPost::afterCreate - notification job executed successfully');
        } catch (\Throwable $e) {
            Log::error('CreateBlogPost::afterCreate - Failed to execute notification job', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
