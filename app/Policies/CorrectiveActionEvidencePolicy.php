<?php

namespace App\Policies;

use App\Models\CorrectiveActionEvidence;
use App\Models\User;

class CorrectiveActionEvidencePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('corrective-action-evidences.view');
    }

    public function view(User $user, CorrectiveActionEvidence $correctiveActionEvidence): bool
    {
        return $user->can('corrective-action-evidences.view')
            && $correctiveActionEvidence->correctiveAction !== null
            && $user->can('view', $correctiveActionEvidence->correctiveAction);
    }

    public function create(User $user): bool
    {
        return $user->can('corrective-action-evidences.create')
            && $user->hasRole('unit_pic')
            && $user->unit_id !== null;
    }

    public function update(User $user, CorrectiveActionEvidence $correctiveActionEvidence): bool
    {
        return $user->can('corrective-action-evidences.create')
            && $correctiveActionEvidence->correctiveAction !== null
            && $user->can('update', $correctiveActionEvidence->correctiveAction);
    }

    public function delete(User $user, CorrectiveActionEvidence $correctiveActionEvidence): bool
    {
        return $user->can('corrective-action-evidences.delete')
            && $correctiveActionEvidence->correctiveAction !== null
            && $user->can('update', $correctiveActionEvidence->correctiveAction);
    }

    public function restore(User $user, CorrectiveActionEvidence $correctiveActionEvidence): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin_lpm']);
    }

    public function forceDelete(User $user, CorrectiveActionEvidence $correctiveActionEvidence): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin_lpm']);
    }
}
