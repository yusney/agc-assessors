<?php

declare(strict_types=1);

namespace AGC\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class TeamMemberModel extends Model
{
    use HasTranslations;

    protected $table = 'team_members';

    public array $translatable = ['role', 'bio'];

    protected $fillable = [
        'name', 'email', 'role', 'bio',
        'sort_order', 'active', 'photo_media_id',
    ];

    protected $casts = [
        'active'     => 'boolean',
        'sort_order' => 'integer',
    ];
}
