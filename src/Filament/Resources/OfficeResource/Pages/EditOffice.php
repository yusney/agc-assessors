<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\OfficeResource\Pages;

use AGC\Filament\Resources\OfficeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOffice extends EditRecord
{
    protected static string $resource = OfficeResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
