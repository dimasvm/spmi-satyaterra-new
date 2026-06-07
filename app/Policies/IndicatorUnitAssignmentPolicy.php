<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\IndicatorUnitAssignment;
use Illuminate\Auth\Access\HandlesAuthorization;

class IndicatorUnitAssignmentPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:IndicatorUnitAssignment');
    }

    public function view(AuthUser $authUser, IndicatorUnitAssignment $indicatorUnitAssignment): bool
    {
        return $authUser->can('View:IndicatorUnitAssignment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:IndicatorUnitAssignment');
    }

    public function update(AuthUser $authUser, IndicatorUnitAssignment $indicatorUnitAssignment): bool
    {
        return $authUser->can('Update:IndicatorUnitAssignment');
    }

    public function delete(AuthUser $authUser, IndicatorUnitAssignment $indicatorUnitAssignment): bool
    {
        return $authUser->can('Delete:IndicatorUnitAssignment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:IndicatorUnitAssignment');
    }

    public function restore(AuthUser $authUser, IndicatorUnitAssignment $indicatorUnitAssignment): bool
    {
        return $authUser->can('Restore:IndicatorUnitAssignment');
    }

    public function forceDelete(AuthUser $authUser, IndicatorUnitAssignment $indicatorUnitAssignment): bool
    {
        return $authUser->can('ForceDelete:IndicatorUnitAssignment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:IndicatorUnitAssignment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:IndicatorUnitAssignment');
    }

    public function replicate(AuthUser $authUser, IndicatorUnitAssignment $indicatorUnitAssignment): bool
    {
        return $authUser->can('Replicate:IndicatorUnitAssignment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:IndicatorUnitAssignment');
    }

}