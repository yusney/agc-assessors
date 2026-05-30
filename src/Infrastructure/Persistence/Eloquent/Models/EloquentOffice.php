<?php

declare(strict_types=1);

namespace AGC\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class EloquentOffice extends Model
{
    use HasTranslations;

    protected $table = 'offices';

    public array $translatable = ['name', 'address', 'city'];

    protected $fillable = [
        'name',
        'address',
        'city',
        'phone',
        'email',
        'lat',
        'lng',
        'is_active',
    ];

    protected $casts = [
        'lat'       => 'float',
        'lng'       => 'float',
        'is_active' => 'boolean',
        'name'      => 'array',
        'address'   => 'array',
        'city'      => 'array',
    ];
}
