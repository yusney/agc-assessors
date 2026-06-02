<?php

declare(strict_types=1);

namespace AGC\Domain\Page\Entities;

use AGC\Domain\Shared\ValueObjects\SEOData;
use AGC\Domain\Shared\ValueObjects\Slug;
use AGC\Domain\Shared\ValueObjects\TranslatableString;

final class Page
{
    public function __construct(
        private readonly ?int $id,
        private TranslatableString $title,
        private TranslatableString $content,
        private Slug $slug,
        private SEOData $seo,
        private bool $published = false,
        private readonly ?\DateTimeImmutable $createdAt = null,
        private ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function title(): TranslatableString
    {
        return $this->title;
    }

    public function content(): TranslatableString
    {
        return $this->content;
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

    public function createdAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function publish(): void
    {
        $this->published = true;
    }

    public function unpublish(): void
    {
        $this->published = false;
    }

    public function updateContent(TranslatableString $title, TranslatableString $content): void
    {
        $this->title = $title;
        $this->content = $content;
        $this->updatedAt = new \DateTimeImmutable;
    }
}
