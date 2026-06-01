<?php

declare(strict_types=1);

namespace AGC\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

final class JobApplication extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'phone',
        'department',
        'message',
        'cv_path',
        'privacy_accepted',
        'ip_address',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'privacy_accepted' => 'boolean',
    ];
}
