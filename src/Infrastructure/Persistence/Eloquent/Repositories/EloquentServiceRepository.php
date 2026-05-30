<?php

declare(strict_types=1);

namespace AGC\Infrastructure\Persistence\Eloquent\Repositories;

use AGC\Domain\Service\Entities\Service;
use AGC\Domain\Service\Repositories\ServiceRepository;
use AGC\Domain\Shared\ValueObjects\SEOData;
use AGC\Domain\Shared\ValueObjects\Slug;
use AGC\Domain\Shared\ValueObjects\TranslatableString;
use AGC\Infrastructure\Persistence\Eloquent\Models\ServiceModel;
use Awcodes\Curator\Models\Media;

final class EloquentServiceRepository implements ServiceRepository
{
    public function findById(int $id): ?Service
    {
        $model = ServiceModel::find($id);
        return $model ? $this->toDomain($model) : null;
    }

    public function findBySlug(Slug $slug): ?Service
    {
        $model = ServiceModel::where('slug', $slug->value())->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function findAllActive(): array
    {
        return ServiceModel::where('active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($m) => $this->toDomain($m))
            ->all();
    }

    public function save(Service $service): void
    {
        $data = [
            'slug'            => $service->slug()->value(),
            'name'            => $service->name()->toArray(),
            'description'     => $service->description()->toArray(),
            'seo_title'       => $service->seo()->title()->toArray(),
            'seo_description' => $service->seo()->description()->toArray(),
            'seo_canonical'   => $service->seo()->canonicalUrl(),
            'sort_order'      => $service->sortOrder(),
            'active'          => $service->isActive(),
        ];

        if ($service->id()) {
            ServiceModel::where('id', $service->id())->update($data);
        } else {
            ServiceModel::create($data);
        }
    }

    public function delete(int $id): void
    {
        ServiceModel::destroy($id);
    }

    private function toDomain(ServiceModel $model): Service
    {
        return new Service(
            id: $model->id,
            name: new TranslatableString($model->getTranslations('name')),
            description: new TranslatableString($model->getTranslations('description')),
            slug: new Slug($model->slug),
            seo: new SEOData(
                title: new TranslatableString($model->getTranslations('seo_title') ?: []),
                description: new TranslatableString($model->getTranslations('seo_description') ?: []),
                canonicalUrl: $model->seo_canonical,
            ),
            sortOrder: (int) $model->sort_order,
            active: (bool) $model->active,
            coverUrl: $model->cover_media_id
                ? Media::find($model->cover_media_id)?->url
                : null,
        );
    }
}
