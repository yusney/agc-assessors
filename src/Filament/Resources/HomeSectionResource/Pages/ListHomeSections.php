<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\HomeSectionResource\Pages;

use AGC\Filament\Resources\HomeSectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHomeSections extends ListRecords
{
    protected static string $resource = HomeSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
