<?php

declare(strict_types=1);

namespace AGC\Domain\Offices\Entities;

use AGC\Domain\Shared\ValueObjects\TranslatableString;

final class Office
{
    public function __construct(
        private readonly int $id,
        private readonly TranslatableString $name,
        private readonly TranslatableString $address,
        private readonly TranslatableString $city,
        private readonly TranslatableString $description,
        private readonly ?TranslatableString $openingHours,
        private readonly ?TranslatableString $serviceArea,
        private readonly ?TranslatableString $imageAlt,
        private readonly ?string $phone,
        private readonly ?string $email,
        private readonly ?float $lat,
        private readonly ?float $lng,
        private readonly ?string $coverUrl,
        private readonly bool $isActive,
    ) {}

    public function id(): int
    {
        return $this->id;
    }

    public function name(): TranslatableString
    {
        return $this->name;
    }

    public function address(): TranslatableString
    {
        return $this->address;
    }

    public function city(): TranslatableString
    {
        return $this->city;
    }

    public function description(): TranslatableString
    {
        return $this->description;
    }

    public function openingHours(): ?TranslatableString
    {
        return $this->openingHours;
    }

    public function serviceArea(): ?TranslatableString
    {
        return $this->serviceArea;
    }

    public function imageAlt(): ?TranslatableString
    {
        return $this->imageAlt;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function lat(): ?float
    {
        return $this->lat;
    }

    public function lng(): ?float
    {
        return $this->lng;
    }

    public function coverUrl(): ?string
    {
        return $this->coverUrl;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return array<int, string>
     */
    public function serviceAreaList(string $locale): array
    {
        if ($this->serviceArea === null) {
            return [];
        }

        $value = $this->serviceArea->get($locale);
        if ($value === '') {
            return [];
        }

        $parts = preg_split('/[\n,]+/', $value) ?: [];

        return array_values(array_filter(array_map('trim', $parts), static fn (string $v) => $v !== ''));
    }
}
