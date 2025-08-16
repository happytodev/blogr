<?php

namespace Happytodev\Blogr\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class);
    }
}
