<?php

declare(strict_types=1);

namespace App\Policies;

use AGC\Infrastructure\Persistence\Eloquent\Models\TeamMemberModel;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TeamMemberModelPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TeamMemberModel');
    }

    public function view(AuthUser $authUser, TeamMemberModel $teamMemberModel): bool
    {
        return $authUser->can('View:TeamMemberModel');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TeamMemberModel');
    }

    public function update(AuthUser $authUser, TeamMemberModel $teamMemberModel): bool
    {
        return $authUser->can('Update:TeamMemberModel');
    }

    public function delete(AuthUser $authUser, TeamMemberModel $teamMemberModel): bool
    {
        return $authUser->can('Delete:TeamMemberModel');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TeamMemberModel');
    }

    public function restore(AuthUser $authUser, TeamMemberModel $teamMemberModel): bool
    {
        return $authUser->can('Restore:TeamMemberModel');
    }

    public function forceDelete(AuthUser $authUser, TeamMemberModel $teamMemberModel): bool
    {
        return $authUser->can('ForceDelete:TeamMemberModel');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TeamMemberModel');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TeamMemberModel');
    }

    public function replicate(AuthUser $authUser, TeamMemberModel $teamMemberModel): bool
    {
        return $authUser->can('Replicate:TeamMemberModel');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TeamMemberModel');
    }
}
