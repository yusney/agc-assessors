<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\MenuItemResource\Pages;

use AGC\Filament\Resources\MenuItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMenuItems extends ListRecords
{
    protected static string $resource = MenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
