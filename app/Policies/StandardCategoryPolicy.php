<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\StandardCategory;
use Illuminate\Auth\Access\HandlesAuthorization;

class StandardCategoryPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StandardCategory');
    }

    public function view(AuthUser $authUser, StandardCategory $standardCategory): bool
    {
        return $authUser->can('View:StandardCategory');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StandardCategory');
    }

    public function update(AuthUser $authUser, StandardCategory $standardCategory): bool
    {
        return $authUser->can('Update:StandardCategory');
    }

    public function delete(AuthUser $authUser, StandardCategory $standardCategory): bool
    {
        return $authUser->can('Delete:StandardCategory');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:StandardCategory');
    }

    public function restore(AuthUser $authUser, StandardCategory $standardCategory): bool
    {
        return $authUser->can('Restore:StandardCategory');
    }

    public function forceDelete(AuthUser $authUser, StandardCategory $standardCategory): bool
    {
        return $authUser->can('ForceDelete:StandardCategory');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StandardCategory');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StandardCategory');
    }

    public function replicate(AuthUser $authUser, StandardCategory $standardCategory): bool
    {
        return $authUser->can('Replicate:StandardCategory');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StandardCategory');
    }

}