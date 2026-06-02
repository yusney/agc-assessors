<?php

declare(strict_types=1);

namespace AGC\Domain\Service\Repositories;

use AGC\Domain\Service\Entities\Service;
use AGC\Domain\Shared\ValueObjects\Slug;

interface ServiceRepository
{
    public function findById(int $id): ?Service;

    public function findBySlug(Slug $slug): ?Service;

    /** @return Service[] */
    public function findAllActive(): array;

    public function save(Service $service): void;

    public function delete(int $id): void;
}
