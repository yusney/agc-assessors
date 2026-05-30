<?php

declare(strict_types=1);

namespace AGC\Domain\Shared\ValueObjects;

final class SEOData
{
    public function __construct(
        private readonly TranslatableString $title,
        private readonly TranslatableString $description,
        private readonly ?string $canonicalUrl = null,
        private readonly array $keywords = [],
    ) {}

    public function title(): TranslatableString
    {
        return $this->title;
    }

    public function description(): TranslatableString
    {
        return $this->description;
    }

    public function canonicalUrl(): ?string
    {
        return $this->canonicalUrl;
    }

    public function keywords(): array
    {
        return $this->keywords;
    }
}
