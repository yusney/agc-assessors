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
        private readonly ?TranslatableString $slug,
        private readonly ?TranslatableString $managerName,
        private readonly ?TranslatableString $managerRole,
        private readonly ?TranslatableString $managerBio,
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

    public function slug(): ?TranslatableString
    {
        return $this->slug;
    }

    public function managerName(): ?TranslatableString
    {
        return $this->managerName;
    }

    public function managerRole(): ?TranslatableString
    {
        return $this->managerRole;
    }

    public function managerBio(): ?TranslatableString
    {
        return $this->managerBio;
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
     * Resolve the public URL slug for the current locale, falling back to a
     * slug built from the city name when no explicit slug is configured.
     */
    public function publicSlug(string $locale): string
    {
        if ($this->slug !== null) {
            $value = $this->slug->get($locale);
            if ($value !== '') {
                return $value;
            }
        }

        return \Illuminate\Support\Str::slug($this->city()->get($locale) ?: $this->city()->get('ca'));
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
