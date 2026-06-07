<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\IndicatorAchievement;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class IndicatorAchievementPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:IndicatorAchievement');
    }

    public function view(AuthUser $authUser, IndicatorAchievement $indicatorAchievement): bool
    {
        return $authUser->can('View:IndicatorAchievement');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:IndicatorAchievement');
    }

    public function update(AuthUser $authUser, IndicatorAchievement $indicatorAchievement): bool
    {
        return $authUser->can('Update:IndicatorAchievement');
    }

    public function delete(AuthUser $authUser, IndicatorAchievement $indicatorAchievement): bool
    {
        return $authUser->can('Delete:IndicatorAchievement');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:IndicatorAchievement');
    }

    public function restore(AuthUser $authUser, IndicatorAchievement $indicatorAchievement): bool
    {
        return $authUser->can('Restore:IndicatorAchievement');
    }

    public function forceDelete(AuthUser $authUser, IndicatorAchievement $indicatorAchievement): bool
    {
        return $authUser->can('ForceDelete:IndicatorAchievement');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:IndicatorAchievement');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:IndicatorAchievement');
    }

    public function replicate(AuthUser $authUser, IndicatorAchievement $indicatorAchievement): bool
    {
        return $authUser->can('Replicate:IndicatorAchievement');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:IndicatorAchievement');
    }
}
