<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\OfficeResource\Pages;

use AGC\Filament\Resources\OfficeResource;
use Filament\Resources\Pages\ListRecords;

class ListOffices extends ListRecords
{
    protected static string $resource = OfficeResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}
