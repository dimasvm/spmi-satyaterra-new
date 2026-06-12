<?php

namespace App\Policies;

use App\Models\AmiAuditor;
use App\Models\User;

class AmiAuditorPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewAmi($user);
    }

    public function view(User $user, AmiAuditor $amiAuditor): bool
    {
        if ($this->canManageAmi($user) || $user->isPimpinan()) {
            return true;
        }

        if ($user->isAuditor()) {
            return $amiAuditor->user_id === $user->id;
        }

        return $user->canAccessUnit($amiAuditor->audit?->auditee_unit_id);
    }

    public function create(User $user): bool
    {
        return $this->canManageAmi($user);
    }

    public function update(User $user, AmiAuditor $amiAuditor): bool
    {
        return $this->canManageAmi($user);
    }

    public function delete(User $user, AmiAuditor $amiAuditor): bool
    {
        return $this->canManageAmi($user);
    }

    public function restore(User $user, AmiAuditor $amiAuditor): bool
    {
        return $this->canManageAmi($user);
    }

    public function forceDelete(User $user, AmiAuditor $amiAuditor): bool
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
