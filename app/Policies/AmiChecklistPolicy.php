<?php

namespace App\Policies;

use App\Enums\AmiAuditorRole;
use App\Enums\AmiAuditStatus;
use App\Models\AmiChecklist;
use App\Models\User;

class AmiChecklistPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canViewAmiChecklist($user);
    }

    public function view(User $user, AmiChecklist $amiChecklist): bool
    {
        if ($user->isAdminLpm() || $user->isPimpinan()) {
            return true;
        }

        if ($user->isAuditor()) {
            return $amiChecklist->audit
                ? $this->auditorRole($user, $amiChecklist) !== null
                : false;
        }

        return $user->isUnitPic()
            && $user->canAccessUnit($amiChecklist->audit?->auditee_unit_id)
            && $amiChecklist->audit?->status === AmiAuditStatus::Finalized;
    }

    public function create(User $user): bool
    {
        return $user->isAdminLpm();
    }

    public function update(User $user, AmiChecklist $amiChecklist): bool
    {
        if ($amiChecklist->audit?->status === AmiAuditStatus::Finalized) {
            return false;
        }

        return in_array($this->auditorRole($user, $amiChecklist), [
            AmiAuditorRole::Lead,
            AmiAuditorRole::Member,
        ], true);
    }

    public function delete(User $user, AmiChecklist $amiChecklist): bool
    {
        return $user->isAdminLpm();
    }

    public function restore(User $user, AmiChecklist $amiChecklist): bool
    {
        return $user->isAdminLpm();
    }

    public function forceDelete(User $user, AmiChecklist $amiChecklist): bool
    {
        return $user->isAdminLpm();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdminLpm();
    }

    private function canViewAmiChecklist(User $user): bool
    {
        return $user->isAdminLpm() || $user->isPimpinan() || $user->isAuditor() || $user->isUnitPic();
    }

    private function auditorRole(User $user, AmiChecklist $amiChecklist): ?AmiAuditorRole
    {
        if (! $user->isAuditor()) {
            return null;
        }

        return $amiChecklist->audit
            ? $amiChecklist->audit->auditorAssignments()
                ->where('user_id', $user->id)
                ->first()
                ?->role
            : null;
    }
}
