<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\PageResource\Pages;

use AGC\Filament\Resources\PageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;
}
