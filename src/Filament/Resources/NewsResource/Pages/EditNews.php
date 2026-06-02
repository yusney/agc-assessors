<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\NewsResource\Pages;

use AGC\Filament\Resources\NewsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNews extends EditRecord
{
    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
