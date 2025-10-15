<?php

namespace Happytodev\Blogr\Commands;

use Illuminate\Console\Command;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\BlogPostTranslation;

class MigratePostsToTranslations extends Command
{
    protected $signature = 'blogr:migrate-translations';
    
    protected $description = 'Migrate blog posts without translations to create default translations';

    public function handle()
    {
        $postsWithoutTranslations = BlogPost::doesntHave('translations')->get();
        
        if ($postsWithoutTranslations->isEmpty()) {
            $this->info('No posts need migration. All posts have translations.');
            return Command::SUCCESS;
        }
        
        $this->info("Found {$postsWithoutTranslations->count()} post(s) without translations.");
        
        $defaultLocale = config('blogr.locales.default', 'en');
        $migratedCount = 0;
        
        foreach ($postsWithoutTranslations as $post) {
            // Check if post has data in the base table
            if (!$post->title || !$post->slug) {
                $this->warn("Post ID {$post->id} is missing title or slug. Skipping.");
                continue;
            }
            
            // Create translation from base table data
            BlogPostTranslation::create([
                'blog_post_id' => $post->id,
                'locale' => $defaultLocale,
                'title' => $post->getAttributes()['title'] ?? $post->title,
                'slug' => $post->getAttributes()['slug'] ?? $post->slug,
                'content' => $post->getAttributes()['content'] ?? $post->content,
                'excerpt' => $post->getAttributes()['excerpt'] ?? null,
                'tldr' => $post->getAttributes()['tldr'] ?? null,
                'seo_title' => $post->getAttributes()['meta_title'] ?? null,
                'seo_description' => $post->getAttributes()['meta_description'] ?? null,
                'seo_keywords' => $post->getAttributes()['meta_keywords'] ?? null,
            ]);
            
            $migratedCount++;
            $this->info("✓ Migrated post ID {$post->id}: {$post->title}");
        }
        
        $this->info("Migration complete! Migrated {$migratedCount} post(s).");
        
        return Command::SUCCESS;
    }
}
