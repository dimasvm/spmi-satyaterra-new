<?php

namespace App\Policies;

use App\Enums\AmiAuditStatus;
use App\Models\AmiFinding;
use App\Models\User;

class AmiFindingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdminLpm() || $user->isPimpinan() || $user->isAuditor() || $user->isUnitPic();
    }

    public function view(User $user, AmiFinding $amiFinding): bool
    {
        if ($user->isAdminLpm() || $user->isPimpinan()) {
            return true;
        }

        if ($user->isAuditor()) {
            return $this->isAssignedAuditor($user, $amiFinding);
        }

        return $user->isUnitPic()
            && $user->canAccessUnit($amiFinding->audit?->auditee_unit_id)
            && $amiFinding->audit?->status === AmiAuditStatus::Finalized;
    }

    public function create(User $user): bool
    {
        return $user->can('ami-findings.create')
            && ($user->isAdminLpm() || $user->isAuditor());
    }

    public function update(User $user, AmiFinding $amiFinding): bool
    {
        if ($user->isAdminLpm()) {
            return true;
        }

        return $amiFinding->audit?->status !== AmiAuditStatus::Finalized
            && $this->isAssignedAuditor($user, $amiFinding);
    }

    public function delete(User $user, AmiFinding $amiFinding): bool
    {
        if ($user->isAdminLpm()) {
            return true;
        }

        return $amiFinding->audit?->status !== AmiAuditStatus::Finalized
            && $this->isAssignedAuditor($user, $amiFinding);
    }

    public function restore(User $user, AmiFinding $amiFinding): bool
    {
        return $user->isAdminLpm();
    }

    public function forceDelete(User $user, AmiFinding $amiFinding): bool
    {
        return $user->isAdminLpm();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdminLpm();
    }

    private function isAssignedAuditor(User $user, AmiFinding $amiFinding): bool
    {
        return $user->isAuditor()
            && $amiFinding->audit !== null
            && $amiFinding->audit->auditorAssignments()
                ->where('user_id', $user->id)
                ->exists();
    }
}
