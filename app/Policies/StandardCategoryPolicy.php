<?php

namespace App\Policies;

use App\Models\StandardCategory;
use App\Models\User;

class StandardCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('standard-categories.view');
    }

    public function view(User $user, StandardCategory $standardCategory): bool
    {
        return $user->can('standard-categories.view');
    }

    public function create(User $user): bool
    {
        return $user->can('standard-categories.create');
    }

    public function update(User $user, StandardCategory $standardCategory): bool
    {
        return $user->can('standard-categories.update');
    }

    public function delete(User $user, StandardCategory $standardCategory): bool
    {
        return $user->can('standard-categories.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('standard-categories.delete');
    }

    public function restore(User $user, StandardCategory $standardCategory): bool
    {
        return $user->can('standard-categories.update');
    }

    public function forceDelete(User $user, StandardCategory $standardCategory): bool
    {
        return $user->can('standard-categories.delete');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('standard-categories.delete');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('standard-categories.update');
    }

    public function replicate(User $user, StandardCategory $standardCategory): bool
    {
        return $user->can('standard-categories.create');
    }

    public function reorder(User $user): bool
    {
        return $user->can('standard-categories.update');
    }
}
