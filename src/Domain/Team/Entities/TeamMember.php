<?php

declare(strict_types=1);

namespace AGC\Domain\Team\Entities;

use AGC\Domain\Shared\ValueObjects\TranslatableString;

final class TeamMember
{
    public function __construct(
        private readonly ?int $id,
        private string $name,
        private TranslatableString $role,
        private TranslatableString $bio,
        private string $email,
        private int $sortOrder = 0,
        private bool $active = true,
        private readonly ?\DateTimeImmutable $createdAt = null,
        private ?string $photoUrl = null,
    ) {}

    public function id(): ?int { return $this->id; }
    public function name(): string { return $this->name; }
    public function role(): TranslatableString { return $this->role; }
    public function bio(): TranslatableString { return $this->bio; }
    public function email(): string { return $this->email; }
    public function sortOrder(): int { return $this->sortOrder; }
    public function isActive(): bool { return $this->active; }
    public function photoUrl(): ?string { return $this->photoUrl; }

    public function reorder(int $position): void { $this->sortOrder = $position; }
}
