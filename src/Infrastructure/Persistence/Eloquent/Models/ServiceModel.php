<?php

declare(strict_types=1);

namespace AGC\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Translatable\HasTranslations;

class ServiceModel extends Model
{
    use HasTranslations;

    protected static function boot(): void
    {
        parent::boot();

        static::saved(function (self $model) {
            DB::update("UPDATE services SET
                search_vector_ca = to_tsvector('catalan', coalesce(?,'')||' '||coalesce(?,'')),
                search_vector_es = to_tsvector('spanish', coalesce(?,'')||' '||coalesce(?,'')),
                search_vector_en = to_tsvector('english',  coalesce(?,'')||' '||coalesce(?,''))
                WHERE id = ?", [
                $model->name['ca'] ?? '', $model->description['ca'] ?? '',
                $model->name['es'] ?? '', $model->description['es'] ?? '',
                $model->name['en'] ?? '', $model->description['en'] ?? '',
                $model->id,
            ]);
        });
    }

    protected $table = 'services';

    public array $translatable = ['name', 'description', 'seo_title', 'seo_description'];

    protected $fillable = [
        'slug', 'name', 'description',
        'seo_title', 'seo_description', 'seo_canonical',
        'sort_order', 'active', 'cover_media_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
