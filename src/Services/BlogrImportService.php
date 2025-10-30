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
use Happytodev\Blogr\Models\User;
use Happytodev\Blogr\Models\UserTranslation;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class BlogrImportService
{
    public function validateImportData(array $data): array
    {
        $errors = [];
        
        if (!isset($data['version'])) {
            $errors[] = 'Missing version field';
        }
        
        if (!isset($data['exported_at'])) {
            $errors[] = 'Missing exported_at field';
        }
        
        // Required sections (legacy compatibility)
        $requiredSections = ['posts', 'series', 'categories', 'tags'];
        foreach ($requiredSections as $section) {
            if (!isset($data[$section]) || !is_array($data[$section])) {
                $errors[] = "Missing or invalid {$section} section";
            }
        }
        
        // Optional sections (for newer exports with translations)
        $optionalSections = [
            'post_translations', 'series_translations', 'category_translations',
            'tag_translations', 'user_translations', 'post_translation_categories', 'post_translation_tags'
        ];
        foreach ($optionalSections as $section) {
            if (isset($data[$section]) && !is_array($data[$section])) {
                $errors[] = "Invalid {$section} section (must be array)";
            }
        }
        
        return $errors;
    }
    
    public function import(array $data, array $options = []): array
    {
        $errors = $this->validateImportData($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        
        DB::beginTransaction();
        
        try {
            $skipExisting = $options['skip_existing'] ?? false;
            $overwrite = $options['overwrite'] ?? false;
            
            // If overwrite is enabled, delete all blog data (except users)
            if ($overwrite) {
                Log::info('BlogrImportService: Overwrite mode enabled, deleting all blog data except users');
                $this->deleteAllBlogData();
            }
            
            // Extract media files if present
            if (isset($data['media_files']) && !empty($data['media_files'])) {
                $this->restoreMediaFiles($data['media_files'], $options);
            }
            
            $defaultAuthorId = $options['default_author_id'] ?? null;
            
            $results = [
                'categories' => $this->importCategories($data['categories'], $skipExisting),
                'category_translations' => isset($data['category_translations']) ? $this->importCategoryTranslations($data['category_translations'], $skipExisting) : ['imported' => 0, 'skipped' => 0],
                'tags' => $this->importTags($data['tags'], $skipExisting),
                'tag_translations' => isset($data['tag_translations']) ? $this->importTagTranslations($data['tag_translations'], $skipExisting) : ['imported' => 0, 'skipped' => 0],
                'user_translations' => isset($data['user_translations']) ? $this->importUserTranslations($data['user_translations'], $skipExisting) : ['imported' => 0, 'skipped' => 0],
                'series' => $this->importSeries($data['series'], $skipExisting),
                'series_translations' => isset($data['series_translations']) ? $this->importSeriesTranslations($data['series_translations'], $skipExisting) : ['imported' => 0, 'skipped' => 0],
                'posts' => $this->importPosts($data['posts'], $skipExisting, $defaultAuthorId),
                'post_translations' => isset($data['post_translations']) ? $this->importPostTranslations($data['post_translations'], $skipExisting) : ['imported' => 0, 'skipped' => 0],
                'post_translation_categories' => isset($data['post_translation_categories']) ? $this->importPostTranslationCategories($data['post_translation_categories'], $skipExisting) : ['imported' => 0, 'skipped' => 0],
                'post_translation_tags' => isset($data['post_translation_tags']) ? $this->importPostTranslationTags($data['post_translation_tags'], $skipExisting) : ['imported' => 0, 'skipped' => 0],
            ];
            
            DB::commit();
            
            return [
                'success' => true,
                'results' => $results,
                'version' => $data['version'],
                'exported_at' => $data['exported_at']
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'errors' => [$e->getMessage()]];
        }
    }

    public function importFromFile(string $filePath, array $options = []): array
    {
        Log::info('BlogrImportService: Starting import from file', [
            'filePath' => $filePath,
            'fileExists' => File::exists($filePath),
            'options' => $options,
        ]);
        
        if (!File::exists($filePath)) {
            Log::error('BlogrImportService: File does not exist', [
                'filePath' => $filePath,
                'cwd' => getcwd(),
                'storage_path' => storage_path('app'),
            ]);
            return ['success' => false, 'errors' => ['File does not exist']];
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        Log::info('BlogrImportService: File extension detected', [
            'extension' => $extension,
            'filePath' => $filePath,
        ]);

        if ($extension === 'zip') {
            return $this->importFromZip($filePath, $options);
        } elseif ($extension === 'json') {
            $json = File::get($filePath);
            $data = json_decode($json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('BlogrImportService: Invalid JSON file', [
                    'filePath' => $filePath,
                    'json_error' => json_last_error_msg(),
                ]);
                return ['success' => false, 'errors' => ['Invalid JSON file: ' . json_last_error_msg()]];
            }
            
            Log::info('BlogrImportService: JSON parsed successfully', [
                'dataKeys' => array_keys($data),
            ]);
            
            return $this->import($data, $options);
        } else {
            Log::error('BlogrImportService: Unsupported file format', [
                'extension' => $extension,
                'filePath' => $filePath,
            ]);
            return ['success' => false, 'errors' => ['Unsupported file format. Use .json or .zip files']];
        }
    }

    private function importFromZip(string $zipPath, array $options = []): array
    {
        Log::info('BlogrImportService: Starting ZIP import', [
            'zipPath' => $zipPath,
            'fileSize' => File::size($zipPath),
        ]);
        
        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            Log::error('BlogrImportService: Cannot open ZIP file', [
                'zipPath' => $zipPath,
            ]);
            return ['success' => false, 'errors' => ['Cannot open ZIP file']];
        }

        // Extract data.json
        $jsonContent = $zip->getFromName('data.json');
        if (!$jsonContent) {
            $zip->close();
            Log::error('BlogrImportService: data.json not found in ZIP', [
                'zipPath' => $zipPath,
                'numFiles' => $zip->numFiles,
            ]);
            return ['success' => false, 'errors' => ['data.json not found in ZIP file']];
        }

        $data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $zip->close();
            Log::error('BlogrImportService: Invalid data.json in ZIP', [
                'zipPath' => $zipPath,
                'json_error' => json_last_error_msg(),
            ]);
            return ['success' => false, 'errors' => ['Invalid data.json in ZIP file: ' . json_last_error_msg()]];
        }

        Log::info('BlogrImportService: ZIP data.json parsed successfully', [
            'dataKeys' => array_keys($data),
            'numMediaFiles' => $zip->numFiles - 1,
        ]);

        // Extract media files to temp directory
        $tempDir = storage_path('app/temp/blogr-import-' . now()->timestamp);
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (str_starts_with($filename, 'media/')) {
                $fileContent = $zip->getFromIndex($i);
                $localPath = $tempDir . '/' . basename($filename);
                File::put($localPath, $fileContent);
            }
        }

        $zip->close();

        // Add temp directory to options for media restoration
        $options['temp_media_dir'] = $tempDir;

        try {
            $result = $this->import($data, $options);
            // Clean up temp directory
            File::deleteDirectory($tempDir);
            return $result;
        } catch (\Exception $e) {
            File::deleteDirectory($tempDir);
            throw $e;
        }
    }

    private function restoreMediaFiles(array $mediaFiles, array $options = []): void
    {
        $tempDir = $options['temp_media_dir'] ?? null;

        foreach ($mediaFiles as $mediaPath) {
            if ($tempDir && File::exists($tempDir . '/' . basename($mediaPath))) {
                // Copy from temp directory
                $sourcePath = $tempDir . '/' . basename($mediaPath);
                $targetPath = Storage::disk('public')->path($mediaPath);
                $targetDir = dirname($targetPath);

                if (!File::exists($targetDir)) {
                    File::makeDirectory($targetDir, 0755, true);
                }

                File::copy($sourcePath, $targetPath);
            } elseif (File::exists(Storage::disk('public')->path($mediaPath))) {
                // File already exists, skip
                continue;
            } else {
                // Log missing file (could be handled differently)
                // For now, we'll just skip missing files
            }
        }
    }
    
    private function importCategories(array $categories, bool $skipExisting = false): array
    {
        $imported = 0;
        $skipped = 0;
        $updated = 0;
        
        foreach ($categories as $categoryData) {
            // Check by ID first if provided, then by slug
            $existing = null;
            if (isset($categoryData['id'])) {
                $existing = Category::where('id', $categoryData['id'])->first();
            }
            if (!$existing) {
                $existing = Category::where('slug', $categoryData['slug'])->first();
            }
            
            if ($existing) {
                if ($skipExisting) {
                    $skipped++;
                    Log::info('BlogrImportService: Category skipped (already exists)', [
                        'id' => $categoryData['id'] ?? 'N/A',
                        'slug' => $categoryData['slug'],
                    ]);
                    continue;
                } else {
                    // Update existing category
                    $existing->update($categoryData);
                    $updated++;
                    Log::info('BlogrImportService: Category updated', [
                        'id' => $existing->id,
                        'slug' => $categoryData['slug'],
                    ]);
                    continue;
                }
            }
            
            // Create with original ID if provided
            if (isset($categoryData['id'])) {
                // Use insert to preserve original ID
                $categoryData['created_at'] = $categoryData['created_at'] ?? now();
                $categoryData['updated_at'] = $categoryData['updated_at'] ?? now();
                DB::table('categories')->insert($categoryData);
            } else {
                Category::create($categoryData);
            }
            
            $imported++;
            Log::info('BlogrImportService: Category created', [
                'id' => $categoryData['id'] ?? 'auto',
                'slug' => $categoryData['slug'],
            ]);
        }
        
        return ['imported' => $imported, 'skipped' => $skipped, 'updated' => $updated];
    }
    
    private function importTags(array $tags, bool $skipExisting = false): array
    {
        $imported = 0;
        $skipped = 0;
        $updated = 0;
        
        foreach ($tags as $tagData) {
            // Check by ID first if provided, then by slug
            $existing = null;
            if (isset($tagData['id'])) {
                $existing = Tag::where('id', $tagData['id'])->first();
            }
            if (!$existing) {
                $existing = Tag::where('slug', $tagData['slug'])->first();
            }
            
            if ($existing) {
                if ($skipExisting) {
                    $skipped++;
                    Log::info('BlogrImportService: Tag skipped (already exists)', [
                        'id' => $tagData['id'] ?? 'N/A',
                        'slug' => $tagData['slug'],
                    ]);
                    continue;
                } else {
                    // Update existing tag
                    $existing->update($tagData);
                    $updated++;
                    Log::info('BlogrImportService: Tag updated', [
                        'id' => $existing->id,
                        'slug' => $tagData['slug'],
                    ]);
                    continue;
                }
            }
            
            // Use insert to preserve original ID
            if (isset($tagData['id'])) {
                $tagData['created_at'] = $tagData['created_at'] ?? now();
                $tagData['updated_at'] = $tagData['updated_at'] ?? now();
                DB::table('tags')->insert($tagData);
            } else {
                Tag::create($tagData);
            }
            
            $imported++;
            Log::info('BlogrImportService: Tag created', [
                'id' => $tagData['id'] ?? 'auto',
                'slug' => $tagData['slug'],
            ]);
        }
        
        return ['imported' => $imported, 'skipped' => $skipped, 'updated' => $updated];
    }
    
    private function importSeries(array $series, bool $skipExisting = false): array
    {
        $imported = 0;
        $skipped = 0;
        $updated = 0;
        
        foreach ($series as $seriesData) {
            $existing = BlogSeries::where('id', $seriesData['id'])->first();
            
            if ($existing) {
                if ($skipExisting) {
                    $skipped++;
                    Log::info('BlogrImportService: Series skipped (already exists)', [
                        'id' => $seriesData['id'],
                    ]);
                    continue;
                } else {
                    // Update existing series
                    $existing->update($seriesData);
                    $updated++;
                    Log::info('BlogrImportService: Series updated', [
                        'id' => $seriesData['id'],
                    ]);
                    continue;
                }
            }
            
            // Use insert to preserve original ID
            if (isset($seriesData['id'])) {
                $seriesData['created_at'] = $seriesData['created_at'] ?? now();
                $seriesData['updated_at'] = $seriesData['updated_at'] ?? now();
                DB::table('blog_series')->insert($seriesData);
            } else {
                BlogSeries::create($seriesData);
            }
            
            $imported++;
            Log::info('BlogrImportService: Series created', [
                'id' => $seriesData['id'] ?? 'auto',
            ]);
        }
        
        return ['imported' => $imported, 'skipped' => $skipped, 'updated' => $updated];
    }

    private function importCategoryTranslations(array $translations, bool $skipExisting = false): array
    {
        Log::info('BlogrImportService: Starting category translations import', [
            'count' => count($translations),
            'skipExisting' => $skipExisting,
        ]);

        $imported = 0;
        $skipped = 0;

        foreach ($translations as $translationData) {
            // Check uniqueness based on locale + slug (as per database constraint)
            $exists = CategoryTranslation::where('locale', $translationData['locale'])
                                         ->where('slug', $translationData['slug'])
                                         ->exists();

            if ($exists) {
                Log::info('BlogrImportService: Category translation already exists, skipping', [
                    'locale' => $translationData['locale'],
                    'slug' => $translationData['slug'],
                ]);
                $skipped++;
                continue;
            }

            // Find the target category by slug
            $targetCategory = Category::where('slug', $translationData['slug'])->first();
            
            if (!$targetCategory) {
                Log::warning('BlogrImportService: Target category not found for translation', [
                    'slug' => $translationData['slug'],
                ]);
                $skipped++;
                continue;
            }

            // Create translation with the correct category_id
            CategoryTranslation::create([
                'category_id' => $targetCategory->id,
                'locale' => $translationData['locale'],
                'name' => $translationData['name'],
                'slug' => $translationData['slug'],
                'description' => $translationData['description'] ?? null,
            ]);
            
            Log::info('BlogrImportService: Category translation imported', [
                'category_id' => $targetCategory->id,
                'locale' => $translationData['locale'],
                'slug' => $translationData['slug'],
            ]);
            
            $imported++;
        }

        Log::info('BlogrImportService: Category translations import completed', [
            'imported' => $imported,
            'skipped' => $skipped,
        ]);

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    private function importTagTranslations(array $translations, bool $skipExisting = false): array
    {
        Log::info('BlogrImportService: Starting tag translations import', [
            'count' => count($translations),
            'skipExisting' => $skipExisting,
        ]);

        $imported = 0;
        $skipped = 0;

        foreach ($translations as $translationData) {
            // Check uniqueness based on locale + slug (as per database constraint)
            $exists = TagTranslation::where('locale', $translationData['locale'])
                                    ->where('slug', $translationData['slug'])
                                    ->exists();

            if ($exists) {
                Log::info('BlogrImportService: Tag translation already exists, skipping', [
                    'locale' => $translationData['locale'],
                    'slug' => $translationData['slug'],
                ]);
                $skipped++;
                continue;
            }

            // Find the target tag by slug
            $targetTag = Tag::where('slug', $translationData['slug'])->first();
            
            if (!$targetTag) {
                Log::warning('BlogrImportService: Target tag not found for translation', [
                    'slug' => $translationData['slug'],
                ]);
                $skipped++;
                continue;
            }

            // Create translation with the correct tag_id
            TagTranslation::create([
                'tag_id' => $targetTag->id,
                'locale' => $translationData['locale'],
                'name' => $translationData['name'],
                'slug' => $translationData['slug'],
                'description' => $translationData['description'] ?? null,
            ]);
            
            Log::info('BlogrImportService: Tag translation imported', [
                'tag_id' => $targetTag->id,
                'locale' => $translationData['locale'],
                'slug' => $translationData['slug'],
            ]);
            
            $imported++;
        }

        Log::info('BlogrImportService: Tag translations import completed', [
            'imported' => $imported,
            'skipped' => $skipped,
        ]);

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    private function importUserTranslations(array $translations, bool $skipExisting = false): array
    {
        $imported = 0;
        $skipped = 0;

        foreach ($translations as $translationData) {
            if ($skipExisting && UserTranslation::where('user_id', $translationData['user_id'])
                                               ->where('locale', $translationData['locale'])->exists()) {
                $skipped++;
                continue;
            }

            UserTranslation::create($translationData);
            $imported++;
        }

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    private function importSeriesTranslations(array $translations, bool $skipExisting = false): array
    {
        $imported = 0;
        $skipped = 0;

        foreach ($translations as $translationData) {
            // ALWAYS check uniqueness by locale + slug to avoid constraint violation
            $existing = BlogSeriesTranslation::where('locale', $translationData['locale'])
                                            ->where('slug', $translationData['slug'])->first();
            
            if ($existing) {
                if ($skipExisting) {
                    $skipped++;
                    Log::info('BlogrImportService: Series translation skipped (already exists)', [
                        'locale' => $translationData['locale'],
                        'slug' => $translationData['slug'],
                    ]);
                    continue;
                } else {
                    // Update existing translation
                    $existing->update($translationData);
                    Log::info('BlogrImportService: Series translation updated', [
                        'locale' => $translationData['locale'],
                        'slug' => $translationData['slug'],
                    ]);
                    $skipped++; // Count as skipped since not newly imported
                    continue;
                }
            }

            // Find the target series by slug to get the correct blog_series_id
            $series = BlogSeries::where('slug', $translationData['slug'])->first();
            if ($series) {
                $translationData['blog_series_id'] = $series->id;
                BlogSeriesTranslation::create($translationData);
                $imported++;
                Log::info('BlogrImportService: Series translation created', [
                    'locale' => $translationData['locale'],
                    'slug' => $translationData['slug'],
                    'blog_series_id' => $series->id,
                ]);
            } else {
                Log::warning('BlogrImportService: Series translation skipped (series not found)', [
                    'slug' => $translationData['slug'],
                ]);
                $skipped++;
            }
        }

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    private function importPostTranslations(array $translations, bool $skipExisting = false): array
    {
        $imported = 0;
        $skipped = 0;

        foreach ($translations as $translationData) {
            // ALWAYS check uniqueness by locale + slug to avoid constraint violation
            $existing = BlogPostTranslation::where('locale', $translationData['locale'])
                                          ->where('slug', $translationData['slug'])->first();
            
            if ($existing) {
                if ($skipExisting) {
                    $skipped++;
                    Log::info('BlogrImportService: Post translation skipped (already exists)', [
                        'locale' => $translationData['locale'],
                        'slug' => $translationData['slug'],
                    ]);
                    continue;
                } else {
                    // Update existing translation
                    $existing->update($translationData);
                    Log::info('BlogrImportService: Post translation updated', [
                        'locale' => $translationData['locale'],
                        'slug' => $translationData['slug'],
                    ]);
                    $skipped++; // Count as skipped since not newly imported
                    continue;
                }
            }

            // Find the target post by checking if blog_post_id exists
            $post = BlogPost::find($translationData['blog_post_id']);
            if ($post) {
                BlogPostTranslation::create($translationData);
                $imported++;
                Log::info('BlogrImportService: Post translation created', [
                    'locale' => $translationData['locale'],
                    'slug' => $translationData['slug'],
                    'blog_post_id' => $translationData['blog_post_id'],
                ]);
            } else {
                Log::warning('BlogrImportService: Post translation skipped (post not found)', [
                    'blog_post_id' => $translationData['blog_post_id'],
                    'slug' => $translationData['slug'],
                ]);
                $skipped++;
            }
        }

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    private function importPostTranslationCategories(array $relations, bool $skipExisting = false): array
    {
        $imported = 0;
        $skipped = 0;

        foreach ($relations as $relationData) {
            if ($skipExisting && DB::table('blog_post_translation_category')
                                  ->where('blog_post_translation_id', $relationData['blog_post_translation_id'])
                                  ->where('category_id', $relationData['category_id'])->exists()) {
                $skipped++;
                continue;
            }

            DB::table('blog_post_translation_category')->insert($relationData);
            $imported++;
        }

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    private function importPostTranslationTags(array $relations, bool $skipExisting = false): array
    {
        $imported = 0;
        $skipped = 0;

        foreach ($relations as $relationData) {
            if ($skipExisting && DB::table('blog_post_translation_tag')
                                  ->where('blog_post_translation_id', $relationData['blog_post_translation_id'])
                                  ->where('tag_id', $relationData['tag_id'])->exists()) {
                $skipped++;
                continue;
            }

            DB::table('blog_post_translation_tag')->insert($relationData);
            $imported++;
        }

        return ['imported' => $imported, 'skipped' => $skipped];
    }
    
    private function importPosts(array $posts, bool $skipExisting = false, ?int $defaultAuthorId = null): array
    {
        $imported = 0;
        $skipped = 0;
        $updated = 0;
        
        foreach ($posts as $postData) {
            // Check if user exists, if not use default author or skip
            if (isset($postData['user_id'])) {
                $userExists = User::where('id', $postData['user_id'])->exists();
                
                if (!$userExists) {
                    if ($defaultAuthorId) {
                        Log::info('BlogrImportService: Post author not found, using default author', [
                            'original_user_id' => $postData['user_id'],
                            'default_author_id' => $defaultAuthorId,
                            'post_slug' => $postData['slug'] ?? 'N/A',
                        ]);
                        $postData['user_id'] = $defaultAuthorId;
                    } else {
                        Log::warning('BlogrImportService: Post skipped (author not found and no default author specified)', [
                            'user_id' => $postData['user_id'],
                            'post_slug' => $postData['slug'] ?? 'N/A',
                        ]);
                        $skipped++;
                        continue;
                    }
                }
            }
            
            $existing = BlogPost::where('id', $postData['id'])->first();
            
            if ($existing) {
                if ($skipExisting) {
                    $skipped++;
                    Log::info('BlogrImportService: Post skipped (already exists)', [
                        'id' => $postData['id'],
                        'slug' => $postData['slug'] ?? 'N/A',
                    ]);
                    continue;
                } else {
                    // Update existing post
                    $existing->update($postData);
                    $updated++;
                    Log::info('BlogrImportService: Post updated', [
                        'id' => $postData['id'],
                        'slug' => $postData['slug'] ?? 'N/A',
                    ]);
                    continue;
                }
            }
            
            // Check if blog_series_id exists in the database
            if (isset($postData['blog_series_id']) && $postData['blog_series_id']) {
                $seriesExists = BlogSeries::where('id', $postData['blog_series_id'])->exists();
                
                if (!$seriesExists) {
                    Log::warning('BlogrImportService: Post series not found, setting to null', [
                        'blog_series_id' => $postData['blog_series_id'],
                        'post_slug' => $postData['slug'] ?? 'N/A',
                    ]);
                    $postData['blog_series_id'] = null;
                }
            }
            
            // Check if category_id exists in the database
            if (isset($postData['category_id']) && $postData['category_id']) {
                $categoryExists = Category::where('id', $postData['category_id'])->exists();
                
                if (!$categoryExists) {
                    Log::warning('BlogrImportService: Post category not found, setting to null', [
                        'category_id' => $postData['category_id'],
                        'post_slug' => $postData['slug'] ?? 'N/A',
                    ]);
                    $postData['category_id'] = null;
                }
            }
            
            // Use insert to preserve original ID
            if (isset($postData['id'])) {
                $postData['created_at'] = $postData['created_at'] ?? now();
                $postData['updated_at'] = $postData['updated_at'] ?? now();
                DB::table('blog_posts')->insert($postData);
            } else {
                BlogPost::create($postData);
            }
            
            $imported++;
            Log::info('BlogrImportService: Post created', [
                'id' => $postData['id'] ?? 'auto',
                'slug' => $postData['slug'] ?? 'N/A',
            ]);
        }
        
        return ['imported' => $imported, 'skipped' => $skipped, 'updated' => $updated];
    }

    /**
     * Delete all blog data except users
     * This includes: posts, categories, tags, series and all their translations
     */
    private function deleteAllBlogData(): void
    {
        Log::info('BlogrImportService: Starting deletion of all blog data');

        // Delete in the correct order to respect foreign key constraints
        // 1. Delete pivot tables first
        DB::table('blog_post_translation_category')->delete();
        DB::table('blog_post_translation_tag')->delete();
        
        // 2. Delete translations
        BlogPostTranslation::query()->delete();
        BlogSeriesTranslation::query()->delete();
        CategoryTranslation::query()->delete();
        TagTranslation::query()->delete();
        UserTranslation::query()->delete();
        
        // 3. Delete main records (cascade will handle related records)
        BlogPost::query()->delete();
        BlogSeries::query()->delete();
        Category::query()->delete();
        Tag::query()->delete();
        
        Log::info('BlogrImportService: All blog data deleted successfully');
    }
}
