<?php

namespace Happytodev\Blogr\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Happytodev\Blogr\Tests\Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    use HasFactory, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'slug',
        'avatar',
        'avatar_url',
        'bio',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'bio' => 'array',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }

    public function blogPosts()
    {
        return $this->hasMany(BlogPost::class);
    }

    public function getGravatarUrlAttribute(): ?string
    {
        if (! $this->email) {
            return null;
        }

        $hash = md5(strtolower(trim($this->email)));

        return 'https://www.gravatar.com/avatar/'.$hash.'?s=80&d=mp';
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if ($this->avatar_url) {
            return url('storage/' . $this->avatar_url);
        }

        if ($this->avatar) {
            return url('storage/' . $this->avatar);
        }

        return $this->gravatar_url;
    }

    public function guardName()
    {
        return 'web';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // For dev only, full access
        return true;
    }
}
