<?php

declare(strict_types=1);

namespace AGC\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\Translatable\HasTranslations;

class NewsModel extends Model
{
    use HasTranslations;
    use SoftDeletes;

    protected static function boot(): void
    {
        parent::boot();

        static::saved(function (self $model) {
            DB::update("UPDATE news_articles SET
                search_vector_ca = to_tsvector('catalan', coalesce(?,'')||' '||coalesce(?,'')||' '||coalesce(?,'')),
                search_vector_es = to_tsvector('spanish', coalesce(?,'')||' '||coalesce(?,'')||' '||coalesce(?,'')),
                search_vector_en = to_tsvector('english',  coalesce(?,'')||' '||coalesce(?,'')||' '||coalesce(?,''))
                WHERE id = ?", [
                $model->title['ca'] ?? '', $model->excerpt['ca'] ?? '', $model->body['ca'] ?? '',
                $model->title['es'] ?? '', $model->excerpt['es'] ?? '', $model->body['es'] ?? '',
                $model->title['en'] ?? '', $model->excerpt['en'] ?? '', $model->body['en'] ?? '',
                $model->id,
            ]);
        });
    }

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
