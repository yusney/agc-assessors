<?php

declare(strict_types=1);

namespace Database\Seeders;

use AGC\Infrastructure\Persistence\Eloquent\Models\EloquentOffice;
use Illuminate\Database\Seeder;

final class OfficeSeoSeeder extends Seeder
{
    public function run(): void
    {
        $payload = json_decode(
            (string) file_get_contents(
                storage_path('backups/offices_seo_content.json')
            ),
            true
        );

        if (! is_array($payload)) {
            $this->command?->error('offices_seo_content.json not found or invalid.');

            return;
        }

        $updated = 0;
        $skipped = 0;
        foreach ($payload as $cityKey => $fields) {
            $cityTranslations = $this->cityTranslationsFor($cityKey);

            $office = EloquentOffice::query()
                ->where(function ($q) use ($cityTranslations): void {
                    foreach ($cityTranslations as $locale => $value) {
                        $q->orWhereRaw('LOWER(city->>?) = ?', [$locale, mb_strtolower($value)]);
                    }
                })
                ->first();

            if ($office === null) {
                $this->command?->warn("Office not found for key '{$cityKey}'. Skipping.");
                $skipped++;
                continue;
            }

            // Idempotent: only update SEO fields, preserve core data.
            foreach ($fields as $attribute => $value) {
                $office->setTranslation($attribute, 'ca', $value['ca'] ?? '');
                $office->setTranslation($attribute, 'es', $value['es'] ?? '');
                $office->setTranslation($attribute, 'en', $value['en'] ?? '');
            }
            $office->save();
            $updated++;
        }

        $this->command?->info("Updated {$updated} offices with SEO content ({$skipped} skipped).");

        $this->command?->info("Updated {$updated} offices with SEO content.");
    }

    /**
     * @return array<string, string>
     */
    private function cityTranslationsFor(string $cityKey): array
    {
        return match ($cityKey) {
            'caldes-de-montbui' => ['ca' => 'Caldes de Montbui', 'es' => 'Caldes de Montbui', 'en' => 'Caldes de Montbui'],
            'sant-celoni'       => ['ca' => 'Sant Celoni',       'es' => 'Sant Celoni',       'en' => 'Sant Celoni'],
            'mollet-del-valles' => ['ca' => 'Mollet del Vallès', 'es' => 'Mollet del Vallès', 'en' => 'Mollet del Vallès'],
            'granollers'        => ['ca' => 'Granollers',        'es' => 'Granollers',        'en' => 'Granollers'],
            'prats-de-llucanes' => ['ca' => 'Prats de Lluçanès', 'es' => 'Prats de Lluçanès', 'en' => 'Prats de Lluçanès'],
            'manlleu'           => ['ca' => 'Manlleu',           'es' => 'Manlleu',           'en' => 'Manlleu'],
            default             => [],
        };
    }

    /**
     * The translatable cast on EloquentOffice already serializes arrays to JSON
     * when saving, so we can hand it the raw per-locale arrays as-is.
     *
     * @param  array<string, array<string, string>>  $fields
     * @return array<string, array<string, string>>
     */
    private function flattenTranslatables(array $fields): array
    {
        return $fields;
    }
}
