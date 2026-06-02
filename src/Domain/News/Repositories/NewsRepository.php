<?php

declare(strict_types=1);

namespace AGC\Domain\News\Repositories;

use AGC\Domain\News\Entities\NewsArticle;
use AGC\Domain\Shared\ValueObjects\Slug;

interface NewsRepository
{
    public function findById(int $id): ?NewsArticle;

    public function findBySlug(Slug $slug): ?NewsArticle;

    /** @return NewsArticle[] */
    public function findPublished(int $limit = 10, int $offset = 0): array;

    public function countPublished(): int;

    public function save(NewsArticle $article): void;

    public function delete(int $id): void;
}
