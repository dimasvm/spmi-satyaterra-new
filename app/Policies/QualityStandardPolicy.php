<?php

namespace App\Policies;

use App\Models\QualityStandard;
use App\Models\User;

class QualityStandardPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('quality-standards.view');
    }

    public function view(User $user, QualityStandard $qualityStandard): bool
    {
        return $user->can('quality-standards.view');
    }

    public function create(User $user): bool
    {
        return $user->can('quality-standards.create') && $user->isAdminLpm();
    }

    public function update(User $user, QualityStandard $qualityStandard): bool
    {
        return $user->can('quality-standards.update') && $user->isAdminLpm();
    }

    public function delete(User $user, QualityStandard $qualityStandard): bool
    {
        return $user->can('quality-standards.delete') && $user->isAdminLpm();
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('quality-standards.delete') && $user->isAdminLpm();
    }

    public function restore(User $user, QualityStandard $qualityStandard): bool
    {
        return $user->can('quality-standards.update');
    }

    public function forceDelete(User $user, QualityStandard $qualityStandard): bool
    {
        return $user->can('quality-standards.delete');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('quality-standards.delete');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('quality-standards.update');
    }

    public function replicate(User $user, QualityStandard $qualityStandard): bool
    {
        return $user->can('quality-standards.create');
    }

    public function reorder(User $user): bool
    {
        return $user->can('quality-standards.update');
    }
}
