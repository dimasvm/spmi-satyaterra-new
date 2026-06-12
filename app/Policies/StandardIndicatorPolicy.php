<?php

namespace App\Policies;

use App\Models\StandardIndicator;
use App\Models\User;

class StandardIndicatorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('standard-indicators.view');
    }

    public function view(User $user, StandardIndicator $standardIndicator): bool
    {
        return $user->can('standard-indicators.view');
    }

    public function create(User $user): bool
    {
        return $user->can('standard-indicators.create') && $user->isAdminLpm();
    }

    public function update(User $user, StandardIndicator $standardIndicator): bool
    {
        return $user->can('standard-indicators.update') && $user->isAdminLpm();
    }

    public function delete(User $user, StandardIndicator $standardIndicator): bool
    {
        return $user->can('standard-indicators.delete') && $user->isAdminLpm();
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('standard-indicators.delete') && $user->isAdminLpm();
    }

    public function restore(User $user, StandardIndicator $standardIndicator): bool
    {
        return $user->can('standard-indicators.update');
    }

    public function forceDelete(User $user, StandardIndicator $standardIndicator): bool
    {
        return $user->can('standard-indicators.delete');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('standard-indicators.delete');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('standard-indicators.update');
    }

    public function replicate(User $user, StandardIndicator $standardIndicator): bool
    {
        return $user->can('standard-indicators.create');
    }

    public function reorder(User $user): bool
    {
        return $user->can('standard-indicators.update');
    }
}
