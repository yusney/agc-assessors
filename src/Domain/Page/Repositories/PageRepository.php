<?php

declare(strict_types=1);

namespace AGC\Domain\Page\Repositories;

use AGC\Domain\Page\Entities\Page;
use AGC\Domain\Shared\ValueObjects\Slug;

interface PageRepository
{
    public function findById(int $id): ?Page;
    public function findBySlug(Slug $slug): ?Page;
    /** @return Page[] */
    public function findAllPublished(): array;
    public function save(Page $page): void;
    public function delete(int $id): void;
}
