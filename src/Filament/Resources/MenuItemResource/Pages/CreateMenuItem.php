<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\MenuItemResource\Pages;

use AGC\Filament\Resources\MenuItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMenuItem extends CreateRecord
{
    protected static string $resource = MenuItemResource::class;
}
