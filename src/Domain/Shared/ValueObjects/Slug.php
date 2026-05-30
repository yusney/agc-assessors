<?php

declare(strict_types=1);

namespace AGC\Domain\Shared\ValueObjects;

final class Slug
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));
        $normalized = preg_replace('/[^a-z0-9\-\/]/', '', $normalized);
        $normalized = preg_replace('/-+/', '-', $normalized);

        if ($normalized === '' || $normalized === '-') {
            throw new \InvalidArgumentException("Invalid slug: '{$value}'");
        }

        $this->value = $normalized;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
