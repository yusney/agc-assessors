<?php

declare(strict_types=1);

namespace App\Policies;

use AGC\Infrastructure\Persistence\Eloquent\Models\HomeSection;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class HomeSectionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HomeSection');
    }

    public function view(AuthUser $authUser, HomeSection $homeSection): bool
    {
        return $authUser->can('View:HomeSection');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HomeSection');
    }

    public function update(AuthUser $authUser, HomeSection $homeSection): bool
    {
        return $authUser->can('Update:HomeSection');
    }

    public function delete(AuthUser $authUser, HomeSection $homeSection): bool
    {
        return $authUser->can('Delete:HomeSection');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:HomeSection');
    }

    public function restore(AuthUser $authUser, HomeSection $homeSection): bool
    {
        return $authUser->can('Restore:HomeSection');
    }

    public function forceDelete(AuthUser $authUser, HomeSection $homeSection): bool
    {
        return $authUser->can('ForceDelete:HomeSection');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:HomeSection');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:HomeSection');
    }

    public function replicate(AuthUser $authUser, HomeSection $homeSection): bool
    {
        return $authUser->can('Replicate:HomeSection');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:HomeSection');
    }
}
