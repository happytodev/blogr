<?php

namespace Happytodev\Blogr\Services;

use Happytodev\Blogr\Contracts\ExportableExtension;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;
use Happytodev\Blogr\Models\BlogSeries;
use Happytodev\Blogr\Models\BlogSeriesTranslation;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\CategoryTranslation;
use Happytodev\Blogr\Models\CmsPage;
use Happytodev\Blogr\Models\CmsPageTranslation;
use Happytodev\Blogr\Models\Tag;
use Happytodev\Blogr\Models\TagTranslation;
use Happytodev\Blogr\Models\UserTranslation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class BlogrExportService
{
    const FORMAT_VERSION = 1;

    public function export(array $options = []): array
    {
        $registry = app(ExtensionRegistry::class);

        $data = [
            'version' => config('blogr.version', 'unknown'),
            'exported_at' => now()->toIso8601String(),
            'format_version' => self::FORMAT_VERSION,
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
            'users' => $this->exportUsers(),
            'cms_pages' => CmsPage::all()->toArray(),
            'cms_page_translations' => CmsPageTranslation::all()->toArray(),
            'extension_states' => collect($registry->getDisabledIds())
                ->mapWithKeys(fn (string $id) => [
                    $id => ['disabled_at' => now()->toIso8601String()],
                ])
                ->toArray(),
            'extensions' => collect($registry->getExportableExtensions())
                ->mapWithKeys(fn (ExportableExtension $ext) => [
                    $ext->getExportKey() => [
                        'version' => '1.0.0',
                        'data' => $ext->getExportData(),
                        'media_files' => $ext->getExportMediaPaths(),
                    ],
                ])
                ->toArray(),
        ];

        // Include media files if requested
        if (($options['include_media'] ?? true)) {
            $data['media_files'] = $this->collectMediaFiles($data);
        }

        // Collect extension media files
        /** @var array<string, array{media_files: list<string>}> $extensionData */
        $extensionData = $data['extensions'];
        foreach ($extensionData as $extKey => $extData) {
            foreach ($extData['media_files'] as $mediaPath) {
                $data['media_files'][] = $mediaPath;
            }
        }
        if (isset($data['media_files'])) {
            $data['media_files'] = array_values(array_unique($data['media_files']));
        }

        // Apply only/skip filters
        $alwaysIncluded = ['version', 'exported_at', 'format_version'];
        if (isset($options['only'])) {
            $data = array_intersect_key($data, array_flip(array_merge($alwaysIncluded, $options['only'])));
        }
        if (isset($options['skip'])) {
            $data = array_diff_key($data, array_flip($options['skip']));
        }

        return $data;
    }

    private function exportUsers(): array
    {
        // Try to get users from the appropriate model
        $userClass = class_exists('App\\Models\\User') ? 'App\\Models\\User' : 'Happytodev\\Blogr\\Models\\User';
        $users = call_user_func([$userClass, 'all']);

        return $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->password, // Keep encrypted password
                'roles' => $user->roles ? $user->roles->pluck('name')->toArray() : [], // Get role names
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        })->toArray();
    }

    public function exportToFile(?string $path = null, array $options = []): string
    {
        $data = $this->export($options);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $dir = storage_path('app/blogr-exports');
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        // Create ZIP file if media files are included
        if (isset($data['media_files']) && ! empty($data['media_files'])) {
            return $this->createZipExport($data, $json, $path);
        }

        // Regular JSON export
        if (! $path) {
            $path = $dir.'/blogr-backup-'.now()->format('Ymd_His').'.json';
        }
        File::put($path, $json);

        return $path;
    }

    private function collectMediaFiles(array $data): array
    {
        $mediaFiles = [];

        // Collect post photos from main table
        foreach ($data['posts'] as $post) {
            if (! empty($post['photo'])) {
                $mediaFiles[] = $post['photo'];
            }
        }

        // Collect post photos from translations
        if (isset($data['post_translations'])) {
            foreach ($data['post_translations'] as $translation) {
                if (! empty($translation['photo'])) {
                    $mediaFiles[] = $translation['photo'];
                }
            }
        }

        // Collect series photos from main table
        foreach ($data['series'] as $series) {
            if (! empty($series['photo'])) {
                $mediaFiles[] = $series['photo'];
            }
        }

        // Collect series photos from translations
        if (isset($data['series_translations'])) {
            foreach ($data['series_translations'] as $translation) {
                if (! empty($translation['photo'])) {
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
        if (! $path) {
            $path = $dir.'/blogr-backup-'.now()->format('Ymd_His').'.zip';
        }

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Cannot create ZIP file');
        }

        // Add JSON data
        $zip->addFromString('data.json', $json);

        // Add media files
        foreach ($data['media_files'] as $mediaPath) {
            $fullPath = Storage::disk('public')->path($mediaPath);
            if (File::exists($fullPath)) {
                $zip->addFile($fullPath, 'media/'.basename($mediaPath));
            }
        }

        $zip->close();

        return $path;
    }
}
