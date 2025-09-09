<?php

namespace Happytodev\Blogr\Http\Controllers;

use Happytodev\Blogr\Models\Tag;
use Happytodev\Blogr\Helpers\SEOHelper;
use Illuminate\Support\Facades\View;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;

class BlogController
{
    public function index()
    {
        $posts = BlogPost::with(['category', 'tags'])
            ->latest()
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                      ->orWhere('published_at', '<=', now());
            })
            ->take(config('blogr.posts_per_page', 10))
            ->get()
            ->map(function ($post) {
                if ($post->photo) {
                    $post->photo_url = Storage::temporaryUrl(
                        $post->photo,
                        now()->addHours(1) // URL valid for 1 hour
                    );
                }
                return $post;
            });

        $seoData = SEOHelper::forListingPage('index');

        return View::make('blogr::blog.index', [
            'posts' => $posts,
            'seoData' => $seoData
        ]);
    }

    public function show($slug)
    {
        $environment = new Environment([
            'heading_permalink' => [
                'html_class' => 'heading-permalink', // CSS class for permalinks (optional)
                'id_prefix' => '', // Prefix for anchor IDs (default: empty)
                'fragment_prefix' => '', // Prefix for URL fragments (default: empty)
                'insert' => 'before', // Position of the permalink: 'before' or 'after' the heading (default: 'before')
                'min_heading_level' => 1, // Minimum level to add permalinks (default: 1)
                'max_heading_level' => 6, // Maximum level (default: 6)
                'title' => 'Permalink', // Title for the title attribute (default: 'Permalink')
                'symbol' => '#', // Symbol for the permalink (default: '¶')
                'aria_hidden' => true, // Adds aria-hidden="true" for accessibility (default: true)
            ],
            'table_of_contents' => [
                'position' => 'placeholder', // Change to 'placeholder' instead of 'top'
                'placeholder' => '[[TOC]]', // Set a unique placeholder (e.g., [[TOC]])
                'style' => 'bullet',
                'min_heading_level' => 2,
                'max_heading_level' => 6,
                'normalize' => 'relative',
                'html_class' => 'toc',
            ],
        ]);

        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new HeadingPermalinkExtension());
        $environment->addExtension(new TableOfContentsExtension());

        $post = BlogPost::with(['category', 'tags'])
            ->where('slug', $slug)
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                      ->orWhere('published_at', '<=', now());
            })
            ->firstOrFail();

        // Calculate reading time BEFORE adding TOC to content
        $post->reading_time = $post->getFormattedReadingTime();

        $converter = new MarkdownConverter($environment);

        // Get content without frontmatter
        $contentWithoutFrontmatter = $post->getContentWithoutFrontmatter();

        // Only add TOC if it's not disabled
        if (!$post->isTocDisabled()) {
            // This will insert the table of contents at the placeholder [[TOC]]
            $markdownWithToc = "# Table of contents\n\n[[TOC]]\n\n" . $contentWithoutFrontmatter;
            $post->content = $converter->convert($markdownWithToc)->getContent();
        } else {
            // Convert markdown without TOC
            $post->content = $converter->convert($contentWithoutFrontmatter)->getContent();
        }
        if ($post->photo) {
            $post->photo_url = Storage::temporaryUrl(
                $post->photo,
                now()->addHours(1) // URL valid for 1 hour
            );
        }

        $seoData = SEOHelper::forBlogPost($post);

        return View::make('blogr::blog.show', [
            'post' => $post,
            'seoData' => $seoData
        ]);
    }

    public function category($categorySlug)
    {
        $category = Category::where('slug', $categorySlug)->firstOrFail();
        $posts = BlogPost::with(['category', 'tags'])
            ->where('category_id', $category->id)
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                      ->orWhere('published_at', '<=', now());
            })
            ->latest()
            ->take(config('blogr.posts_per_page', 10))
            ->get()
            ->map(function ($post) {
                if ($post->photo) {
                    $post->photo_url = Storage::temporaryUrl($post->photo, now()->addMinutes(5));
                }
                return $post;
            });

        return View::make('blogr::blog.category', [
            'category' => $category,
            'posts' => $posts,
            'seoData' => SEOHelper::forListingPage('category', $category->name)
        ]);
    }

    public function tag($tagSlug)
    {
        $tag = Tag::where('slug', $tagSlug)->firstOrFail();
        $posts = $tag->posts()
            ->with(['category', 'tags'])
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('published_at')
                      ->orWhere('published_at', '<=', now());
            })
            ->latest()
            ->take(config('blogr.posts_per_page', 10))
            ->get()
            ->map(function ($post) {
                if ($post->photo) {
                    $post->photo_url = Storage::temporaryUrl($post->photo, now()->addMinutes(5));
                }
                return $post;
            });

        return View::make('blogr::blog.tag', [
            'tag' => $tag,
            'posts' => $posts,
            'seoData' => SEOHelper::forListingPage('tag', $tag->name)
        ]);
    }
}
