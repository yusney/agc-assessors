<?php

declare(strict_types=1);

namespace AGC\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ServiceModel extends Model
{
    use HasTranslations;

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
