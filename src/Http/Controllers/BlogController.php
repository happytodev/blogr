<?php

namespace Happytodev\Blogr\Http\Controllers;

use Illuminate\Support\Facades\View;
use Happytodev\Blogr\Models\BlogPost;
use Illuminate\Support\Facades\Storage;
use League\CommonMark\CommonMarkConverter;

class BlogController
{
    public function index()
    {
        $posts = BlogPost::latest()->take(config('blogr.posts_per_page', 10))->get()->map(function ($post) {
            if ($post->photo) {
                $post->photo_url = Storage::temporaryUrl(
                    $post->photo,
                    now()->addHours(1) // URL valid for 1 hour
                );
            }
            return $post;
        });
        return View::make('blogr::blog.index', ['posts' => $posts]);
    }

    public function show($slug)
    {
        $post = BlogPost::where('slug', $slug)->firstOrFail();
        $converter = new CommonMarkConverter();
        $post->content = $converter->convert($post->content);
        if ($post->photo) {
            $post->photo_url = Storage::temporaryUrl(
                $post->photo,
                now()->addHours(1) // URL valid for 1 hour
            );
        }
        return View::make('blogr::blog.show', ['post' => $post]);
    }
}
