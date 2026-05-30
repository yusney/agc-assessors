<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\HomeSectionResource\Pages;

use AGC\Filament\Resources\HomeSectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHomeSection extends EditRecord
{
    protected static string $resource = HomeSectionResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
