<?php

declare(strict_types=1);

namespace AGC\Domain\Service\Entities;

use AGC\Domain\Shared\ValueObjects\Slug;
use AGC\Domain\Shared\ValueObjects\SEOData;
use AGC\Domain\Shared\ValueObjects\TranslatableString;

final class Service
{
    public function __construct(
        private readonly ?int $id,
        private TranslatableString $name,
        private TranslatableString $description,
        private Slug $slug,
        private SEOData $seo,
        private int $sortOrder = 0,
        private bool $active = true,
        private readonly ?\DateTimeImmutable $createdAt = null,
        private ?string $coverUrl = null,
    ) {}

    public function id(): ?int { return $this->id; }
    public function name(): TranslatableString { return $this->name; }
    public function description(): TranslatableString { return $this->description; }
    public function slug(): Slug { return $this->slug; }
    public function seo(): SEOData { return $this->seo; }
    public function sortOrder(): int { return $this->sortOrder; }
    public function isActive(): bool { return $this->active; }
    public function coverUrl(): ?string { return $this->coverUrl; }

    public function reorder(int $position): void { $this->sortOrder = $position; }
    public function deactivate(): void { $this->active = false; }
}
