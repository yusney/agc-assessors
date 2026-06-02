<?php

declare(strict_types=1);

namespace App\Policies;

use AGC\Infrastructure\Persistence\Eloquent\Models\EloquentOffice;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class EloquentOfficePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EloquentOffice');
    }

    public function view(AuthUser $authUser, EloquentOffice $eloquentOffice): bool
    {
        return $authUser->can('View:EloquentOffice');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EloquentOffice');
    }

    public function update(AuthUser $authUser, EloquentOffice $eloquentOffice): bool
    {
        return $authUser->can('Update:EloquentOffice');
    }

    public function delete(AuthUser $authUser, EloquentOffice $eloquentOffice): bool
    {
        return $authUser->can('Delete:EloquentOffice');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:EloquentOffice');
    }

    public function restore(AuthUser $authUser, EloquentOffice $eloquentOffice): bool
    {
        return $authUser->can('Restore:EloquentOffice');
    }

    public function forceDelete(AuthUser $authUser, EloquentOffice $eloquentOffice): bool
    {
        return $authUser->can('ForceDelete:EloquentOffice');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EloquentOffice');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EloquentOffice');
    }

    public function replicate(AuthUser $authUser, EloquentOffice $eloquentOffice): bool
    {
        return $authUser->can('Replicate:EloquentOffice');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EloquentOffice');
    }
}
