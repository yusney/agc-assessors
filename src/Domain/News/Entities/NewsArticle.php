<?php

declare(strict_types=1);

namespace AGC\Domain\News\Entities;

use AGC\Domain\Shared\ValueObjects\SEOData;
use AGC\Domain\Shared\ValueObjects\Slug;
use AGC\Domain\Shared\ValueObjects\TranslatableString;

final class NewsArticle
{
    public function __construct(
        private readonly ?int $id,
        private TranslatableString $title,
        private TranslatableString $excerpt,
        private TranslatableString $body,
        private Slug $slug,
        private SEOData $seo,
        private bool $published = false,
        private ?\DateTimeImmutable $publishedAt = null,
        private readonly ?\DateTimeImmutable $createdAt = null,
        private ?\DateTimeImmutable $updatedAt = null,
        private ?string $coverUrl = null,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function title(): TranslatableString
    {
        return $this->title;
    }

    public function excerpt(): TranslatableString
    {
        return $this->excerpt;
    }

    public function body(): TranslatableString
    {
        return $this->body;
    }

    public function slug(): Slug
    {
        return $this->slug;
    }

    public function seo(): SEOData
    {
        return $this->seo;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function publishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function coverUrl(): ?string
    {
        return $this->coverUrl;
    }

    public function publish(): void
    {
        $this->published = true;
        $this->publishedAt ??= new \DateTimeImmutable;
    }

    public function unpublish(): void
    {
        $this->published = false;
    }
}
