<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\UserResource\Pages;

use AGC\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;

final class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;
}
