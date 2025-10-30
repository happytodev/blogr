<?php

namespace Happytodev\Blogr\Services;

use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\BlogSeriesTranslation;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\CategoryTranslation;
use Happytodev\Blogr\Models\Tag;
use Happytodev\Blogr\Models\TagTranslation;
use Happytodev\Blogr\Models\UserTranslation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BlogrExportService
{
    public function export(array $options = []): array
    {
        $data = [
            'version' => config('blogr.version', 'unknown'),
            'exported_at' => now()->toIso8601String(),
            'posts' => BlogPost::all()->toArray(),
            'post_translations' => BlogPostTranslation::all()->toArray(),
            'series' => BlogSeries::all()->toArray(),
            'series_translations' => BlogSeriesTranslation::all()->toArray(),
            'categories' => Category::all()->toArray(),
            'category_translations' => CategoryTranslation::all()->toArray(),
            'tags' => Tag::all()->toArray(),
            'tag_translations' => TagTranslation::all()->toArray(),
            'user_translations' => UserTranslation::all()->toArray(),
            'post_translation_categories' => DB::table('blog_post_translation_category')->get()->toArray(),
            'post_translation_tags' => DB::table('blog_post_translation_tag')->get()->toArray(),
        ];

        // Include media files if requested
        if (($options['include_media'] ?? true)) {
            $data['media_files'] = $this->collectMediaFiles($data);
        }

        return $data;
    }
    
    public function exportToFile(?string $path = null, array $options = []): string
    {
        $data = $this->export($options);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $dir = storage_path('app/blogr-exports');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        // Create ZIP file if media files are included
        if (isset($data['media_files']) && !empty($data['media_files'])) {
            return $this->createZipExport($data, $json, $path);
        }

        // Regular JSON export
        if (!$path) {
            $path = $dir . '/blogr-backup-' . now()->format('Ymd_His') . '.json';
        }
        File::put($path, $json);
        return $path;
    }

    private function collectMediaFiles(array $data): array
    {
        $mediaFiles = [];

        // Collect post photos from main table
        foreach ($data['posts'] as $post) {
            if (!empty($post['photo'])) {
                $mediaFiles[] = $post['photo'];
            }
        }

        // Collect post photos from translations
        if (isset($data['post_translations'])) {
            foreach ($data['post_translations'] as $translation) {
                if (!empty($translation['photo'])) {
                    $mediaFiles[] = $translation['photo'];
                }
            }
        }

        // Collect series photos from main table
        foreach ($data['series'] as $series) {
            if (!empty($series['photo'])) {
                $mediaFiles[] = $series['photo'];
            }
        }

        // Collect series photos from translations
        if (isset($data['series_translations'])) {
            foreach ($data['series_translations'] as $translation) {
                if (!empty($translation['photo'])) {
                    $mediaFiles[] = $translation['photo'];
                }
            }
        }

        // Remove duplicates and return
        return array_unique($mediaFiles);
    }

    private function createZipExport(array $data, string $json, ?string $path = null): string
    {
        $dir = storage_path('app/blogr-exports');
        if (!$path) {
            $path = $dir . '/blogr-backup-' . now()->format('Ymd_His') . '.zip';
        }

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Cannot create ZIP file');
        }

        // Add JSON data
        $zip->addFromString('data.json', $json);

        // Add media files
        foreach ($data['media_files'] as $mediaPath) {
            $fullPath = Storage::disk('public')->path($mediaPath);
            if (File::exists($fullPath)) {
                $zip->addFile($fullPath, 'media/' . basename($mediaPath));
            }
        }

        $zip->close();
        return $path;
    }
}
