<?php

declare(strict_types=1);

namespace AGC\Domain\Shared\Contracts;

interface Repository
{
    public function findById(int $id): ?object;

    public function save(object $entity): void;

    public function delete(int $id): void;
}
