<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\TeamMemberResource\Pages;

use AGC\Filament\Resources\TeamMemberResource;
use Filament\Resources\Pages\ListRecords;

class ListTeamMembers extends ListRecords
{
    protected static string $resource = TeamMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}
