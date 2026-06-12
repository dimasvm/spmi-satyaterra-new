<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('roles.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('roles.view');
    }

    public function create(User $user): bool
    {
        return $user->can('roles.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('roles.update');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can('roles.delete');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('roles.delete');
    }

    public function restore(User $user, Role $role): bool
    {
        return $user->can('roles.update');
    }

    public function forceDelete(User $user, Role $role): bool
    {
        return $user->can('roles.delete');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('roles.delete');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('roles.update');
    }

    public function replicate(User $user, Role $role): bool
    {
        return $user->can('roles.create');
    }

    public function reorder(User $user): bool
    {
        return $user->can('roles.update');
    }
}
