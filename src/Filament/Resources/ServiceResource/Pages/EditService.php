<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\ServiceResource\Pages;

use AGC\Filament\Resources\ServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
