<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\NewsResource\Pages;

use AGC\Filament\Resources\NewsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNews extends CreateRecord
{
    protected static string $resource = NewsResource::class;
}
