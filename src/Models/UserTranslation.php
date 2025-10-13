<?php

namespace Happytodev\Blogr\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTranslation extends Model
{
    protected $fillable = [
        'user_id',
        'locale',
        'bio',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class));
    }
}
