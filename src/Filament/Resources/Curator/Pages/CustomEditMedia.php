<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\Curator\Pages;

use AGC\Filament\Resources\Curator\CustomMediaResource;
use Awcodes\Curator\Resources\Media\Pages\EditMedia;
use Illuminate\Support\Facades\Storage;

class CustomEditMedia extends EditMedia
{
    protected static string $resource = CustomMediaResource::class;

    private ?string $originalDirectory = null;

    protected function beforeSave(): void
    {
        $this->originalDirectory = $this->record->getOriginal('directory');
    }

    protected function afterSave(): void
    {
        parent::afterSave();

        $record = $this->record;
        $newDirectory = $record->directory;

        // If directory changed, move the file
        if ($this->originalDirectory !== $newDirectory) {
            $storage = Storage::disk($record->disk);
            $newPath = filled($newDirectory)
                ? $newDirectory.'/'.$record->name.'.'.$record->ext
                : $record->name.'.'.$record->ext;

            if (filled($newDirectory) && ! $storage->exists($newDirectory)) {
                $storage->makeDirectory($newDirectory);
            }

            if ($storage->exists($record->path)) {
                $storage->move($record->path, $newPath);
                $record->update(['path' => $newPath]);
            }
        }
    }
}
