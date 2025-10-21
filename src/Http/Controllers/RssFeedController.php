<?php

namespace Happytodev\Blogr\Http\Controllers;

use Illuminate\Http\Response;
use Happytodev\Blogr\Models\BlogPost;
use Happytodev\Blogr\Models\Category;
use Happytodev\Blogr\Models\Tag;

class RssFeedController
{
    public function index(?string $locale = null): Response
    {
        $locale = $locale ?? config('blogr.locales.default', 'en');
        $posts = $this->getPosts($locale);
        $xml = $this->generateRssFeed($posts, $locale);
        return $this->xmlResponse($xml);
    }
    
    public function category(string $locale, string $categorySlug): Response
    {
        $category = Category::where('slug', $categorySlug)->firstOrFail();
        $posts = $this->getPosts($locale, $category->id);
        $xml = $this->generateRssFeed($posts, $locale, $category);
        return $this->xmlResponse($xml);
    }
    
    public function tag(string $locale, string $tagSlug): Response
    {
        $tag = Tag::where('slug', $tagSlug)->firstOrFail();
        $posts = $this->getPosts($locale, null, $tag->id);
        $xml = $this->generateRssFeed($posts, $locale, null, $tag);
        return $this->xmlResponse($xml);
    }
    
    protected function getPosts(string $locale, ?int $categoryId = null, ?int $tagId = null)
    {
        $limit = config('blogr.rss.items_limit', 20);
        
        $query = BlogPost::with(['user', 'category', 'tags', 'translations'])
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('published_at', 'desc')
            ->limit($limit);
        
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        if ($tagId) {
            $query->whereHas('tags', function ($q) use ($tagId) {
                $q->where('tags.id', $tagId);
            });
        }
        
        return $query->get();
    }
    
    protected function generateRssFeed($posts, string $locale, ?Category $category = null, ?Tag $tag = null): string
    {
        $siteName = config('app.name', 'Blog');
        $blogUrl = $this->getBlogUrl($locale);
        
        $title = $siteName;
        $description = config('blogr.rss.description', 'Latest blog posts');
        
        if ($category) {
            $categoryTranslation = $category->translate($locale);
            $categoryName = $categoryTranslation ? $categoryTranslation->name : $category->name;
            $title .= " - {$categoryName}";
            $description = "Latest posts in {$categoryName} category";
        } elseif ($tag) {
            $tagTranslation = $tag->translate($locale);
            $tagName = $tagTranslation ? $tagTranslation->name : $tag->name;
            $title .= " - {$tagName}";
            $description = "Latest posts tagged with {$tagName}";
        }
        
        $lastBuildDate = $posts->isNotEmpty() 
            ? $posts->first()->published_at->toRfc2822String()
            : now()->toRfc2822String();
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:dc="http://purl.org/dc/elements/1.1/">' . "\n";
        $xml .= '  <channel>' . "\n";
        $xml .= '    <title>' . $this->escapeXml($title) . '</title>' . "\n";
        $xml .= '    <link>' . $this->escapeXml($blogUrl) . '</link>' . "\n";
        $xml .= '    <description>' . $this->escapeXml($description) . '</description>' . "\n";
        $xml .= '    <language>' . $this->escapeXml($locale) . '</language>' . "\n";
        $xml .= '    <lastBuildDate>' . $lastBuildDate . '</lastBuildDate>' . "\n";
        $xml .= '    <atom:link href="' . $this->escapeXml(url()->current()) . '" rel="self" type="application/rss+xml" />' . "\n";
        
        foreach ($posts as $post) {
            $translation = $post->translations->firstWhere('locale', $locale);
            if (!$translation) continue;
            
            $postUrl = route('blog.show', ['locale' => $locale, 'slug' => $translation->slug]);
            
            $xml .= '    <item>' . "\n";
            $xml .= '      <title>' . $this->escapeXml($translation->title) . '</title>' . "\n";
            $xml .= '      <link>' . $this->escapeXml($postUrl) . '</link>' . "\n";
            $xml .= '      <guid isPermaLink="true">' . $this->escapeXml($postUrl) . '</guid>' . "\n";
            $xml .= '      <pubDate>' . $post->published_at->toRfc2822String() . '</pubDate>' . "\n";
            
            if ($post->user) {
                $xml .= '      <dc:creator>' . $this->escapeXml($post->user->name) . '</dc:creator>' . "\n";
            }
            
            if ($post->category) {
                $catTranslation = $post->category->translate($locale);
                $catName = $catTranslation ? $catTranslation->name : $post->category->name;
                $xml .= '      <category>' . $this->escapeXml($catName) . '</category>' . "\n";
            }
            
            $description = $translation->tldr ?? mb_substr(strip_tags($translation->content), 0, 300) . '...';
            $xml .= '      <description>' . $this->escapeXml($description) . '</description>' . "\n";
            $xml .= '    </item>' . "\n";
        }
        $xml .= '  </channel>' . "\n";
        $xml .= '</rss>';
        
        return $xml;
    }
    
    protected function getBlogUrl(string $locale): string
    {
        $prefix = trim(config('blogr.route.prefix', 'blog'), '/');
        $isHomepage = config('blogr.route.homepage', false);
        $localesEnabled = config('blogr.locales.enabled', false);
        
        if ($localesEnabled) {
            return $isHomepage ? url("/{$locale}") : url("/{$locale}/{$prefix}");
        }
        return $isHomepage ? url('/') : url("/{$prefix}");
    }
    
    protected function escapeXml(string $string): string
    {
        return htmlspecialchars($string, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
    
    protected function xmlResponse(string $xml): Response
    {
        return response($xml, 200)
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
