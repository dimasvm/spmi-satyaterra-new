<?php

namespace App\Policies;

use App\Enums\CorrectiveActionStatus;
use App\Models\CorrectiveAction;
use App\Models\User;

class CorrectiveActionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('corrective-actions.view')
            && ($user->isAdminLpm() || $user->isPimpinan() || $user->isAuditor() || $user->isUnitPic());
    }

    public function view(User $user, CorrectiveAction $correctiveAction): bool
    {
        if (! $user->can('corrective-actions.view')) {
            return false;
        }

        if ($user->isAdminLpm() || $user->isPimpinan()) {
            return true;
        }

        if ($correctiveAction->isReviewableBy($user)) {
            return true;
        }

        return $correctiveAction->isOwnedByUnit($user);
    }

    public function create(User $user): bool
    {
        return $user->can('corrective-actions.create')
            && $user->isUnitPic()
            && $user->unit_id !== null;
    }

    public function update(User $user, CorrectiveAction $correctiveAction): bool
    {
        return $user->can('corrective-actions.update')
            && $correctiveAction->isOwnedByUnit($user)
            && in_array($correctiveAction->status, [CorrectiveActionStatus::Draft, CorrectiveActionStatus::NeedRevision], true);
    }

    public function delete(User $user, CorrectiveAction $correctiveAction): bool
    {
        return $user->isAdminLpm()
            || ($user->can('corrective-actions.update')
                && $correctiveAction->isOwnedByUnit($user)
                && in_array($correctiveAction->status, [CorrectiveActionStatus::Draft, CorrectiveActionStatus::NeedRevision], true));
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdminLpm();
    }

    public function restore(User $user, CorrectiveAction $correctiveAction): bool
    {
        return $user->isAdminLpm();
    }

    public function forceDelete(User $user, CorrectiveAction $correctiveAction): bool
    {
        return $user->isAdminLpm();
    }

    public function review(User $user, CorrectiveAction $correctiveAction): bool
    {
        return $user->can('corrective-actions.review')
            && $correctiveAction->isReviewableBy($user);
    }

    public function submit(User $user, CorrectiveAction $correctiveAction): bool
    {
        return $user->can('corrective-actions.submit')
            && $correctiveAction->isOwnedByUnit($user)
            && in_array($correctiveAction->status, [CorrectiveActionStatus::Draft, CorrectiveActionStatus::NeedRevision], true);
    }
}
