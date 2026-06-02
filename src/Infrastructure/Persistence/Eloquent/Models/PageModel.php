<?php

declare(strict_types=1);

namespace AGC\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class PageModel extends Model implements HasMedia
{
    use HasTranslations;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $table = 'pages';

    public array $translatable = ['title', 'content', 'seo_title', 'seo_description'];

    protected $fillable = [
        'slug', 'title', 'content',
        'seo_title', 'seo_description', 'seo_canonical',
        'published', 'published_at',
    ];

    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
    ];
}
