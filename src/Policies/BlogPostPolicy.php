<?php

namespace Happytodev\Blogr\Policies;

use Happytodev\Blogr\Models\BlogPost;
use Illuminate\Auth\Access\HandlesAuthorization;

class BlogPostPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any blog posts.
     */
    public function viewAny($user): bool
    {
        return $user->hasRole(['admin', 'writer']);
    }

    /**
     * Determine whether the user can view the blog post.
     */
    public function view($user, BlogPost $blogPost): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('writer')) {
            return $blogPost->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create blog posts.
     */
    public function create($user): bool
    {
        return $user->hasRole(['admin', 'writer']);
    }

    /**
     * Determine whether the user can update the blog post.
     */
    public function update($user, BlogPost $blogPost): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('writer')) {
            return $blogPost->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the blog post.
     */
    public function delete($user, BlogPost $blogPost): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('writer')) {
            return $blogPost->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can publish the blog post.
     */
    public function publish($user, BlogPost $blogPost = null): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the blog post.
     */
    public function restore($user, BlogPost $blogPost): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can permanently delete the blog post.
     */
    public function forceDelete($user, BlogPost $blogPost): bool
    {
        return $user->hasRole('admin');
    }
}