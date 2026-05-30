<?php

declare(strict_types=1);

namespace AGC\Domain\Shared\ValueObjects;

final class TranslatableString
{
    private array $translations;

    private const SUPPORTED_LOCALES = ['ca', 'es', 'en'];

    public function __construct(array $translations)
    {
        $this->translations = array_intersect_key($translations, array_flip(self::SUPPORTED_LOCALES));
    }

    public function get(string $locale = 'ca'): string
    {
        if (isset($this->translations[$locale]) && $this->translations[$locale] !== '') {
            return $this->translations[$locale];
        }

        // Fallback chain: ca -> es -> en
        foreach (['ca', 'es', 'en'] as $fallback) {
            if (isset($this->translations[$fallback]) && $this->translations[$fallback] !== '') {
                return $this->translations[$fallback];
            }
        }

        return '';
    }

    public function toArray(): array
    {
        return $this->translations;
    }

    public function isEmpty(): bool
    {
        return empty(array_filter($this->translations));
    }
}
