<?php

namespace App\Policies;

use App\Models\AmiPeriod;
use App\Models\User;

class AmiPeriodPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewAmi($user);
    }

    public function view(User $user, AmiPeriod $amiPeriod): bool
    {
        if ($this->canManageAmi($user) || $user->isPimpinan()) {
            return true;
        }

        if ($user->isAuditor()) {
            return $amiPeriod->audits()
                ->whereHas('auditorAssignments', fn ($query) => $query->where('user_id', $user->id))
                ->exists();
        }

        return $user->isUnitPic()
            && $user->unit_id !== null
            && $amiPeriod->audits()->where('auditee_unit_id', $user->unit_id)->exists();
    }

    public function create(User $user): bool
    {
        return $this->canManageAmi($user);
    }

    public function update(User $user, AmiPeriod $amiPeriod): bool
    {
        return $this->canManageAmi($user);
    }

    public function delete(User $user, AmiPeriod $amiPeriod): bool
    {
        return $this->canManageAmi($user);
    }

    public function restore(User $user, AmiPeriod $amiPeriod): bool
    {
        return $this->canManageAmi($user);
    }

    public function forceDelete(User $user, AmiPeriod $amiPeriod): bool
    {
        return $this->canManageAmi($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->canManageAmi($user);
    }

    private function canManageAmi(User $user): bool
    {
        return $user->isAdminLpm();
    }

    private function canViewAmi(User $user): bool
    {
        return $user->isAdminLpm() || $user->isPimpinan() || $user->isAuditor() || $user->isUnitPic();
    }
}
