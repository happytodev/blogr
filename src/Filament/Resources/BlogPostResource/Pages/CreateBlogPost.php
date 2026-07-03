<?php

namespace Happytodev\Blogr\Filament\Resources\BlogPostResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Happytodev\Blogr\Filament\Resources\BlogPostResource;
use Happytodev\Blogr\Filament\Resources\BlogPosts\BlogPostForm;
use Happytodev\Blogr\Jobs\SendPostNotificationJob;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Services\VersioningService;
use Happytodev\Blogr\Traits\AutoSave;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CreateBlogPost extends CreateRecord
{
    use AutoSave;

    protected static string $resource = BlogPostResource::class;

    public function areFormActionsSticky(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        $schema = BlogPostForm::configure($schema);
        $components = $schema->getComponents();
        $components[] = View::make('blogr::components.auto-save-indicator');

        return $schema->components($components);
    }

    public function mount(): void
    {
        parent::mount();
        $this->initializeAutoSave();
    }

    protected function handleRecordCreation(array $data): BlogPost
    {
        if ($this->record && $this->record->exists) {
            $draft = app(VersioningService::class)->getPostDraft($this->record);
            if ($draft) {
                $draft->delete();
            }
            $this->record->update($data);

            return $this->record;
        }

        return parent::handleRecordCreation($data);
    }

    protected function getRedirectUrl(): string
    {
        if ($this->record && $this->record->exists) {
            return BlogPostResource::getUrl('edit', ['record' => $this->record]);
        }

        return parent::getRedirectUrl();
    }

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

        // Normalize photo fields
        $data = $this->normalizePhotoForCreate($data, 'photo');
        if (isset($data['translations']) && is_array($data['translations'])) {
            foreach ($data['translations'] as $key => $translation) {
                $data['translations'][$key] = $this->normalizePhotoForCreate($translation, 'photo');
            }
        }

        return $data;
    }

    protected function normalizePhotoForCreate(array $data, string $field): array
    {
        if (! array_key_exists($field, $data)) {
            return $data;
        }

        $value = $data[$field];

        if ($value instanceof TemporaryUploadedFile) {
            try {
                $data[$field] = $value->store('blog-photos', ['disk' => 'public']);
            } catch (\Throwable) {
                unset($data[$field]);
            }

            return $data;
        }

        if (is_array($value) && empty($value)) {
            unset($data[$field]);
        } elseif (is_array($value) && count($value) === 1 && is_string($value[0])) {
            $data[$field] = $value[0];
        }

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
