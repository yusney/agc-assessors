<?php

declare(strict_types=1);

namespace AGC\Filament\Resources\UserResource\Pages;

use AGC\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;

final class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['email_verified'] = filled($data['email_verified_at'] ?? null);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! empty($data['email_verified'])) {
            $data['email_verified_at'] = now();
        } else {
            $data['email_verified_at'] = null;
        }

        unset($data['email_verified']);

        return $data;
    }
}
