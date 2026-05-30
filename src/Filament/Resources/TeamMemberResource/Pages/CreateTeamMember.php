<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\TeamMemberResource\Pages;

use AGC\Filament\Resources\TeamMemberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTeamMember extends CreateRecord
{
    protected static string $resource = TeamMemberResource::class;
}
