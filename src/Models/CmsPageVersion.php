<?php

namespace Happytodev\Blogr\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CmsPageVersion extends Model
{
    protected $table = 'cms_page_versions';

    protected $fillable = [
        'cms_page_translation_id',
        'version_number',
        'title', 'slug', 'content',
        'seo_title', 'seo_description', 'seo_keywords',
        'blocks',
        'categories',
    ];

    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'categories' => 'array',
        ];
    }

    public function translation(): BelongsTo
    {
        return $this->belongsTo(CmsPageTranslation::class, 'cms_page_translation_id');
    }
}
