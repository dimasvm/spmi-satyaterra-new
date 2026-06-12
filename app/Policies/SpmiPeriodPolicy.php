<?php

namespace App\Policies;

use App\Models\SpmiPeriod;
use App\Models\User;

class SpmiPeriodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('spmi-periods.view');
    }

    public function view(User $user, SpmiPeriod $spmiPeriod): bool
    {
        return $user->can('spmi-periods.view');
    }

    public function create(User $user): bool
    {
        return $user->can('spmi-periods.create') && $user->isAdminLpm();
    }

    public function update(User $user, SpmiPeriod $spmiPeriod): bool
    {
        return $user->can('spmi-periods.update') && $user->isAdminLpm();
    }

    public function delete(User $user, SpmiPeriod $spmiPeriod): bool
    {
        return $user->can('spmi-periods.delete') && $user->isAdminLpm();
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('spmi-periods.delete') && $user->isAdminLpm();
    }

    public function restore(User $user, SpmiPeriod $spmiPeriod): bool
    {
        return $user->can('spmi-periods.update');
    }

    public function forceDelete(User $user, SpmiPeriod $spmiPeriod): bool
    {
        return $user->can('spmi-periods.delete');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('spmi-periods.delete');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('spmi-periods.update');
    }

    public function replicate(User $user, SpmiPeriod $spmiPeriod): bool
    {
        return $user->can('spmi-periods.create');
    }

    public function reorder(User $user): bool
    {
        return $user->can('spmi-periods.update');
    }
}
