<?php

declare(strict_types=1);

namespace AGC\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;

class HomeSection extends Model implements HasMedia
{
    use HasTranslations;
    use InteractsWithMedia;

    protected $table = 'home_sections';

    public array $translatable = [
        'title',
        'eyebrow',
        'subtitle',
        'body',
        'cta_label',
        'secondary_cta_label',
    ];

    protected $fillable = [
        'type',
        'slug',
        'title',
        'eyebrow',
        'subtitle',
        'body',
        'cta_label',
        'cta_url',
        'secondary_cta_label',
        'secondary_cta_url',
        'image_url',
        'carousel_items',
        'settings',
        'sort_order',
        'is_active',
        'main_image_media_id',
    ];

    protected $casts = [
        'carousel_items' => 'array',
        'settings' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function localized(string $field, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $translation = $this->getTranslation($field, $locale, useFallbackLocale: true);

        return is_string($translation) ? $translation : '';
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings ?? [], $key, $default);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main_image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

        $this->addMediaCollection('carousel')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 400, 250)
            ->performOnCollections('main_image');

        $this->addMediaConversion('web')
            ->fit(Fit::Max, 1920, 800)
            ->withResponsiveImages()
            ->performOnCollections('main_image', 'carousel');
    }
}
