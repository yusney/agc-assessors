<?php

declare(strict_types=1);

namespace AGC\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class PageModel extends Model implements HasMedia
{
    use HasTranslations;
    use InteractsWithMedia;
    use SoftDeletes;

    protected static function boot(): void
    {
        parent::boot();

        static::saved(function (self $model) {
            DB::update("UPDATE pages SET
                search_vector_ca = to_tsvector('catalan', coalesce(?,'')||' '||coalesce(?,'')),
                search_vector_es = to_tsvector('spanish', coalesce(?,'')||' '||coalesce(?,'')),
                search_vector_en = to_tsvector('english',  coalesce(?,'')||' '||coalesce(?,''))
                WHERE id = ?", [
                $model->title['ca'] ?? '', $model->content['ca'] ?? '',
                $model->title['es'] ?? '', $model->content['es'] ?? '',
                $model->title['en'] ?? '', $model->content['en'] ?? '',
                $model->id,
            ]);
        });
    }

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
