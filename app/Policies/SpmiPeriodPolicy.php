<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SpmiPeriod;
use Illuminate\Auth\Access\HandlesAuthorization;

class SpmiPeriodPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SpmiPeriod');
    }

    public function view(AuthUser $authUser, SpmiPeriod $spmiPeriod): bool
    {
        return $authUser->can('View:SpmiPeriod');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SpmiPeriod');
    }

    public function update(AuthUser $authUser, SpmiPeriod $spmiPeriod): bool
    {
        return $authUser->can('Update:SpmiPeriod');
    }

    public function delete(AuthUser $authUser, SpmiPeriod $spmiPeriod): bool
    {
        return $authUser->can('Delete:SpmiPeriod');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SpmiPeriod');
    }

    public function restore(AuthUser $authUser, SpmiPeriod $spmiPeriod): bool
    {
        return $authUser->can('Restore:SpmiPeriod');
    }

    public function forceDelete(AuthUser $authUser, SpmiPeriod $spmiPeriod): bool
    {
        return $authUser->can('ForceDelete:SpmiPeriod');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SpmiPeriod');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SpmiPeriod');
    }

    public function replicate(AuthUser $authUser, SpmiPeriod $spmiPeriod): bool
    {
        return $authUser->can('Replicate:SpmiPeriod');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SpmiPeriod');
    }

}