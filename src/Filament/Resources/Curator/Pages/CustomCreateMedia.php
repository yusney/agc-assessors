<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\Curator\Pages;

use AGC\Filament\Resources\Curator\CustomMediaResource;
use Awcodes\Curator\Resources\Media\Pages\CreateMedia;
use Illuminate\Support\Facades\Storage;

class CustomCreateMedia extends CreateMedia
{
    protected static string $resource = CustomMediaResource::class;
    protected function afterCreate(): void
    {
        $record = $this->record;
        $directory = $record->directory;

        if (blank($directory)) {
            return;
        }

        $storage = Storage::disk($record->disk);

        // If the file is not in the correct directory, move it
        if ($record->path !== $directory.'/'.$record->name.'.'.$record->ext) {
            $newPath = $directory.'/'.$record->name.'.'.$record->ext;

            // Ensure directory exists
            if (! $storage->exists($directory)) {
                $storage->makeDirectory($directory);
            }

            if ($storage->exists($record->path)) {
                $storage->move($record->path, $newPath);
                $record->update(['path' => $newPath]);
            }
        }
    }
}
