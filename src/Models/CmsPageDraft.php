<?php

namespace Happytodev\Blogr\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsPageDraft extends Model
{
    protected $table = 'cms_page_drafts';

    protected $fillable = [
        'cms_page_translation_id',
        'draft_data',
    ];

    protected function casts(): array
    {
        return [
            'draft_data' => 'array',
        ];
    }

    public function translation(): BelongsTo
    {
        return $this->belongsTo(CmsPageTranslation::class, 'cms_page_translation_id');
    }
}
