<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\UserResource\Pages;

use AGC\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
