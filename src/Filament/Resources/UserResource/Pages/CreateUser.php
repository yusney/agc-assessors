<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\UserResource\Pages;

use AGC\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! empty($data['email_verified'])) {
            $data['email_verified_at'] = now();
        }

        unset($data['email_verified']);

        return $data;
    }
}
