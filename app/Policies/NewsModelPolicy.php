<?php

declare(strict_types=1);

namespace App\Policies;

use AGC\Infrastructure\Persistence\Eloquent\Models\NewsModel;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class NewsModelPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:NewsModel');
    }

    public function view(AuthUser $authUser, NewsModel $newsModel): bool
    {
        return $authUser->can('View:NewsModel');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:NewsModel');
    }

    public function update(AuthUser $authUser, NewsModel $newsModel): bool
    {
        return $authUser->can('Update:NewsModel');
    }

    public function delete(AuthUser $authUser, NewsModel $newsModel): bool
    {
        return $authUser->can('Delete:NewsModel');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:NewsModel');
    }

    public function restore(AuthUser $authUser, NewsModel $newsModel): bool
    {
        return $authUser->can('Restore:NewsModel');
    }

    public function forceDelete(AuthUser $authUser, NewsModel $newsModel): bool
    {
        return $authUser->can('ForceDelete:NewsModel');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:NewsModel');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:NewsModel');
    }

    public function replicate(AuthUser $authUser, NewsModel $newsModel): bool
    {
        return $authUser->can('Replicate:NewsModel');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:NewsModel');
    }
}
