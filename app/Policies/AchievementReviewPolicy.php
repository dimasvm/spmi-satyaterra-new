<?php

namespace App\Policies;

use App\Models\AchievementReview;
use App\Models\User;

class AchievementReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('indicator-achievements.view');
    }

    public function view(User $user, AchievementReview $achievementReview): bool
    {
        if (! $user->can('indicator-achievements.view')) {
            return false;
        }

        if ($user->isAdminLpm() || $user->isPimpinan() || $user->can('indicator-achievements.review')) {
            return true;
        }

        return $user->isUnitPic()
            && $achievementReview->achievement?->assignment !== null
            && $user->canAccessUnit($achievementReview->achievement->assignment->unit_id);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, AchievementReview $achievementReview): bool
    {
        return $user->can('indicator-achievements.review')
            && ($user->isAdminLpm() || $user->isSuperAdmin());
    }

    public function delete(User $user, AchievementReview $achievementReview): bool
    {
        return $user->isAdminLpm();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdminLpm();
    }
}
