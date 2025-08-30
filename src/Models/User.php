<?php

namespace Happytodev\Blogr\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class);
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        // For dev only, full access
        return true;
    }
}
