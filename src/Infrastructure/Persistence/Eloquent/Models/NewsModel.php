<?php

declare(strict_types=1);

namespace AGC\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class NewsModel extends Model
{
    use HasTranslations;
    use SoftDeletes;

    protected $table = 'news_articles';

    public array $translatable = ['title', 'excerpt', 'body', 'seo_title', 'seo_description'];

    protected $fillable = [
        'slug', 'title', 'excerpt', 'body',
        'seo_title', 'seo_description', 'seo_canonical',
        'published', 'published_at', 'cover_media_id',
    ];

    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
    ];
}
