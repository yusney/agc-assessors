<?php

declare(strict_types=1);

namespace AGC\Domain\Offices\Repositories;

use AGC\Domain\Offices\Entities\Office;

interface OfficeRepositoryInterface
{
    /** @return array<Office> */
    public function findAllActive(): array;

    /** @return array<Office> */
    public function findAll(): array;

    /**
     * Find a single active office by its public slug in a given locale.
     * Falls back to slug built from city name if the office has no explicit slug.
     */
    public function findActiveBySlug(string $slug, string $locale): ?Office;
}
