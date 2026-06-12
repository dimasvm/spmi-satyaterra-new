<?php

namespace App\Policies;

use App\Enums\SubmissionStatus;
use App\Models\IndicatorAchievement;
use App\Models\User;

class IndicatorAchievementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('indicator-achievements.view');
    }

    public function view(User $user, IndicatorAchievement $indicatorAchievement): bool
    {
        if ($user->isAdminLpm() || $user->can('indicator-achievements.review')) {
            return true;
        }

        if ($user->can('indicator-achievements.view') && $user->isPimpinan()) {
            return true;
        }

        return $user->can('indicator-achievements.view')
            && $this->belongsToUserUnit($user, $indicatorAchievement);
    }

    public function create(User $user): bool
    {
        return $user->can('indicator-achievements.create');
    }

    public function update(User $user, IndicatorAchievement $indicatorAchievement): bool
    {
        if ($user->isAdminLpm() || $user->can('indicator-achievements.review')) {
            return true;
        }

        return $user->can('indicator-achievements.update')
            && $this->belongsToUserUnit($user, $indicatorAchievement)
            && in_array($indicatorAchievement->submission_status, [
                SubmissionStatus::Draft,
                SubmissionStatus::Returned,
            ], true);
    }

    public function delete(User $user, IndicatorAchievement $indicatorAchievement): bool
    {
        return $user->can('indicator-achievements.delete') && $user->isAdminLpm();
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('indicator-achievements.delete') && $user->isAdminLpm();
    }

    public function restore(User $user, IndicatorAchievement $indicatorAchievement): bool
    {
        return $user->can('indicator-achievements.update');
    }

    public function forceDelete(User $user, IndicatorAchievement $indicatorAchievement): bool
    {
        return $user->can('indicator-achievements.delete');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('indicator-achievements.delete');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('indicator-achievements.update');
    }

    public function replicate(User $user, IndicatorAchievement $indicatorAchievement): bool
    {
        return $user->can('indicator-achievements.create');
    }

    public function reorder(User $user): bool
    {
        return $user->can('indicator-achievements.update');
    }

    private function belongsToUserUnit(User $user, IndicatorAchievement $indicatorAchievement): bool
    {
        return $indicatorAchievement->assignment !== null
            && $user->canAccessUnit($indicatorAchievement->assignment->unit_id);
    }
}
