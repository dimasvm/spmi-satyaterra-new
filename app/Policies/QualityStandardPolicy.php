<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\QualityStandard;
use Illuminate\Auth\Access\HandlesAuthorization;

class QualityStandardPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:QualityStandard');
    }

    public function view(AuthUser $authUser, QualityStandard $qualityStandard): bool
    {
        return $authUser->can('View:QualityStandard');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:QualityStandard');
    }

    public function update(AuthUser $authUser, QualityStandard $qualityStandard): bool
    {
        return $authUser->can('Update:QualityStandard');
    }

    public function delete(AuthUser $authUser, QualityStandard $qualityStandard): bool
    {
        return $authUser->can('Delete:QualityStandard');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:QualityStandard');
    }

    public function restore(AuthUser $authUser, QualityStandard $qualityStandard): bool
    {
        return $authUser->can('Restore:QualityStandard');
    }

    public function forceDelete(AuthUser $authUser, QualityStandard $qualityStandard): bool
    {
        return $authUser->can('ForceDelete:QualityStandard');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:QualityStandard');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:QualityStandard');
    }

    public function replicate(AuthUser $authUser, QualityStandard $qualityStandard): bool
    {
        return $authUser->can('Replicate:QualityStandard');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:QualityStandard');
    }

}