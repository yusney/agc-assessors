<?php

declare(strict_types=1);

namespace AGC\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

final class SiteSetting extends Model
{
    protected $table = 'site_settings';

    protected $fillable = ['key', 'value'];

    protected $casts = ['value' => 'array'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $record = self::where('key', $key)->first();

        return $record?->value ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
