<?php

declare(strict_types=1);

namespace App\Policies;

use AGC\Infrastructure\Persistence\Eloquent\Models\PageModel;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PageModelPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:PageModel');
    }

    public function view(AuthUser $authUser, PageModel $pageModel): bool
    {
        return $authUser->can('View:PageModel');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:PageModel');
    }

    public function update(AuthUser $authUser, PageModel $pageModel): bool
    {
        return $authUser->can('Update:PageModel');
    }

    public function delete(AuthUser $authUser, PageModel $pageModel): bool
    {
        return $authUser->can('Delete:PageModel');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:PageModel');
    }

    public function restore(AuthUser $authUser, PageModel $pageModel): bool
    {
        return $authUser->can('Restore:PageModel');
    }

    public function forceDelete(AuthUser $authUser, PageModel $pageModel): bool
    {
        return $authUser->can('ForceDelete:PageModel');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:PageModel');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:PageModel');
    }

    public function replicate(AuthUser $authUser, PageModel $pageModel): bool
    {
        return $authUser->can('Replicate:PageModel');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:PageModel');
    }
}
