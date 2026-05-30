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
}
