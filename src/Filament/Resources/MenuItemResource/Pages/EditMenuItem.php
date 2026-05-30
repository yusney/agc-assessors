<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\MenuItemResource\Pages;

use AGC\Filament\Resources\MenuItemResource;
use Filament\Resources\Pages\EditRecord;

class EditMenuItem extends EditRecord
{
    protected static string $resource = MenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\DeleteAction::make()];
    }
}