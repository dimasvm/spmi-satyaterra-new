<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;

class UnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('units.view');
    }

    public function view(User $user, Unit $unit): bool
    {
        return $user->can('units.view') && ($user->isAdminLpm()
            || $user->isPimpinan()
            || $user->isAuditor()
            || $user->hasRole('viewer')
            || $user->canAccessUnit($unit->id));
    }

    public function create(User $user): bool
    {
        return $user->can('units.create') && $user->isAdminLpm();
    }

    public function update(User $user, Unit $unit): bool
    {
        return $user->can('units.update') && $user->isAdminLpm();
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $user->can('units.delete') && $user->isAdminLpm();
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('units.delete') && $user->isAdminLpm();
    }

    public function restore(User $user, Unit $unit): bool
    {
        return $user->can('units.update');
    }

    public function forceDelete(User $user, Unit $unit): bool
    {
        return $user->can('units.delete');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('units.delete');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('units.update');
    }

    public function replicate(User $user, Unit $unit): bool
    {
        return $user->can('units.create');
    }

    public function reorder(User $user): bool
    {
        return $user->can('units.update');
    }
}
