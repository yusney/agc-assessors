<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\ServiceResource\Pages;

use AGC\Filament\Resources\ServiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;
}
