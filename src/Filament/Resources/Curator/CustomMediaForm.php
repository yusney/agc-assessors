<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\Curator;

use Awcodes\Curator\Models\Media;
use Awcodes\Curator\Resources\Media\Schemas\MediaForm;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomMediaForm extends MediaForm
{
    public static function getAdditionalInformationFormSchema(): array
    {
        $parentSchema = parent::getAdditionalInformationFormSchema();

        // Get existing directories from storage + database
        $directories = self::getExistingDirectories();

        return array_merge(
            [
                TextInput::make('directory')
                    ->label('Carpeta')
                    ->placeholder('Ej: logos, team, noticias')
                    ->helperText('Escribí el nombre de la carpeta. Dejar vacío para la raíz.')
                    ->datalist($directories)
                    ->dehydrateStateUsing(function ($component, $state) {
                        $cleaned = $state ? Str::trim($state, '/') : null;
                        $component->state($cleaned);

                        return $cleaned;
                    }),
            ],
            $parentSchema
        );
    }

    /**
     * Get all existing directories from storage and database.
     *
     * @return array<string, string>
     */
    private static function getExistingDirectories(): array
    {
        $directories = [];

        // From database
        $dbDirs = Media::query()
            ->select('directory')
            ->whereNotNull('directory')
            ->distinct()
            ->pluck('directory')
            ->toArray();

        foreach ($dbDirs as $dir) {
            if ($dir) {
                $directories[$dir] = Str::of($dir)->replace('/', ' › ')->title()->toString();
            }
        }

        // From storage (physical directories)
        $storageDirs = Storage::disk(config('curator.default_disk', 'public'))->allDirectories();
        foreach ($storageDirs as $dir) {
            if (! isset($directories[$dir])) {
                $directories[$dir] = Str::of($dir)->replace('/', ' › ')->title()->toString();
            }
        }

        ksort($directories);

        return $directories;
    }
}
