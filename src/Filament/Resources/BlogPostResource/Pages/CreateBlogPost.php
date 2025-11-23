<?php

namespace Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Happytodev\Blogr\Filament\Resources\BlogPostResource;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Jobs\SendPostNotificationJob;
use Illuminate\Support\Facades\Log;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;
    
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
