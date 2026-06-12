<?php

namespace App\Policies;

use App\Models\AmiAudit;
use App\Models\User;

class AmiAuditPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewAmi($user);
    }

    public function view(User $user, AmiAudit $amiAudit): bool
    {
        if ($this->canManageAmi($user) || $user->isPimpinan()) {
            return true;
        }

        if ($user->isAuditor()) {
            return $amiAudit->auditorAssignments()->where('user_id', $user->id)->exists();
        }

        return $user->canAccessUnit($amiAudit->auditee_unit_id);
    }

    public function create(User $user): bool
    {
        return $this->canManageAmi($user);
    }

    public function update(User $user, AmiAudit $amiAudit): bool
    {
        return $this->canManageAmi($user);
    }

    public function delete(User $user, AmiAudit $amiAudit): bool
    {
        return $this->canManageAmi($user);
    }

    public function restore(User $user, AmiAudit $amiAudit): bool
    {
        return $this->canManageAmi($user);
    }

    public function forceDelete(User $user, AmiAudit $amiAudit): bool
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
