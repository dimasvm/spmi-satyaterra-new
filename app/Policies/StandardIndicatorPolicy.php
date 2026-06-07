<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\StandardIndicator;
use Illuminate\Auth\Access\HandlesAuthorization;

class StandardIndicatorPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:StandardIndicator');
    }

    public function view(AuthUser $authUser, StandardIndicator $standardIndicator): bool
    {
        return $authUser->can('View:StandardIndicator');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:StandardIndicator');
    }

    public function update(AuthUser $authUser, StandardIndicator $standardIndicator): bool
    {
        return $authUser->can('Update:StandardIndicator');
    }

    public function delete(AuthUser $authUser, StandardIndicator $standardIndicator): bool
    {
        return $authUser->can('Delete:StandardIndicator');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:StandardIndicator');
    }

    public function restore(AuthUser $authUser, StandardIndicator $standardIndicator): bool
    {
        return $authUser->can('Restore:StandardIndicator');
    }

    public function forceDelete(AuthUser $authUser, StandardIndicator $standardIndicator): bool
    {
        return $authUser->can('ForceDelete:StandardIndicator');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:StandardIndicator');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:StandardIndicator');
    }

    public function replicate(AuthUser $authUser, StandardIndicator $standardIndicator): bool
    {
        return $authUser->can('Replicate:StandardIndicator');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:StandardIndicator');
    }

}