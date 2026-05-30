<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\ServiceResource\Pages;

use AGC\Filament\Resources\ServiceResource;
use Filament\Resources\Pages\ListRecords;

class ListServices extends ListRecords
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}
