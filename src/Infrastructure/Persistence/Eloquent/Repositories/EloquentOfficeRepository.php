<?php

declare(strict_types=1);

namespace AGC\Infrastructure\Persistence\Eloquent\Repositories;

use AGC\Domain\Offices\Entities\Office;
use AGC\Domain\Offices\Repositories\OfficeRepositoryInterface;
use AGC\Domain\Shared\ValueObjects\TranslatableString;
use AGC\Infrastructure\Persistence\Eloquent\Models\EloquentOffice;

final class EloquentOfficeRepository implements OfficeRepositoryInterface
{
    public function findAllActive(): array
    {
        return EloquentOffice::where('is_active', true)
            ->get()
            ->map(fn (EloquentOffice $m) => $this->map($m))
            ->all();
    }

    public function findAll(): array
    {
        return EloquentOffice::all()
            ->map(fn (EloquentOffice $m) => $this->map($m))
            ->all();
    }

    private function map(EloquentOffice $model): Office
    {
        return new Office(
            id: $model->id,
            name: new TranslatableString($model->getTranslations('name')),
            address: new TranslatableString($model->getTranslations('address')),
            city: new TranslatableString($model->getTranslations('city')),
            phone: $model->phone,
            email: $model->email,
            lat: $model->lat,
            lng: $model->lng,
            isActive: (bool) $model->is_active,
        );
    }
}
