<?php

namespace App\Policies;

use App\Models\IndicatorUnitAssignment;
use App\Models\User;

class IndicatorUnitAssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('indicator-assignments.view');
    }

    public function view(User $user, IndicatorUnitAssignment $indicatorUnitAssignment): bool
    {
        return $user->can('indicator-assignments.view')
            && ($user->isAdminLpm() || $user->isPimpinan() || $user->canAccessUnit($indicatorUnitAssignment->unit_id));
    }

    public function create(User $user): bool
    {
        return $user->can('indicator-assignments.create') && $user->isAdminLpm();
    }

    public function update(User $user, IndicatorUnitAssignment $indicatorUnitAssignment): bool
    {
        return $user->can('indicator-assignments.update') && $user->isAdminLpm();
    }

    public function delete(User $user, IndicatorUnitAssignment $indicatorUnitAssignment): bool
    {
        return $user->can('indicator-assignments.delete') && $user->isAdminLpm();
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('indicator-assignments.delete') && $user->isAdminLpm();
    }

    public function restore(User $user, IndicatorUnitAssignment $indicatorUnitAssignment): bool
    {
        return $user->can('indicator-assignments.update');
    }

    public function forceDelete(User $user, IndicatorUnitAssignment $indicatorUnitAssignment): bool
    {
        return $user->can('indicator-assignments.delete');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('indicator-assignments.delete');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('indicator-assignments.update');
    }

    public function replicate(User $user, IndicatorUnitAssignment $indicatorUnitAssignment): bool
    {
        return $user->can('indicator-assignments.create');
    }

    public function reorder(User $user): bool
    {
        return $user->can('indicator-assignments.update');
    }
}
