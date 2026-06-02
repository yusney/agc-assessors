<?php

declare(strict_types=1);

namespace App\Policies;

use AGC\Infrastructure\Persistence\Eloquent\Models\ServiceModel;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ServiceModelPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ServiceModel');
    }

    public function view(AuthUser $authUser, ServiceModel $serviceModel): bool
    {
        return $authUser->can('View:ServiceModel');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ServiceModel');
    }

    public function update(AuthUser $authUser, ServiceModel $serviceModel): bool
    {
        return $authUser->can('Update:ServiceModel');
    }

    public function delete(AuthUser $authUser, ServiceModel $serviceModel): bool
    {
        return $authUser->can('Delete:ServiceModel');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ServiceModel');
    }

    public function restore(AuthUser $authUser, ServiceModel $serviceModel): bool
    {
        return $authUser->can('Restore:ServiceModel');
    }

    public function forceDelete(AuthUser $authUser, ServiceModel $serviceModel): bool
    {
        return $authUser->can('ForceDelete:ServiceModel');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ServiceModel');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ServiceModel');
    }

    public function replicate(AuthUser $authUser, ServiceModel $serviceModel): bool
    {
        return $authUser->can('Replicate:ServiceModel');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ServiceModel');
    }
}
