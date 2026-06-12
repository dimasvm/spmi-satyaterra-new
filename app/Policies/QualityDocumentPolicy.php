<?php

namespace App\Policies;

use App\Enums\QualityDocumentStatus;
use App\Models\QualityDocument;
use App\Models\User;

class QualityDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('quality-documents.view');
    }

    public function view(User $user, QualityDocument $qualityDocument): bool
    {
        if (! $user->can('quality-documents.view')) {
            return false;
        }

        if ($user->isAdminLpm() || $user->isPimpinan()) {
            return true;
        }

        return $qualityDocument->status === QualityDocumentStatus::Active;
    }

    public function create(User $user): bool
    {
        return $user->can('quality-documents.create')
            && $user->isAdminLpm();
    }

    public function update(User $user, QualityDocument $qualityDocument): bool
    {
        return $user->can('quality-documents.update')
            && $user->isAdminLpm();
    }

    public function delete(User $user, QualityDocument $qualityDocument): bool
    {
        return $user->can('quality-documents.delete')
            && $user->isAdminLpm();
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('quality-documents.delete')
            && $user->isAdminLpm();
    }

    public function approve(User $user, QualityDocument $qualityDocument): bool
    {
        return $user->can('quality-documents.approve')
            && ($user->isAdminLpm() || $user->isPimpinan());
    }

    public function archive(User $user, QualityDocument $qualityDocument): bool
    {
        return $user->can('quality-documents.update')
            && $user->isAdminLpm();
    }

    public function restore(User $user, QualityDocument $qualityDocument): bool
    {
        return $user->can('quality-documents.update')
            && $user->isAdminLpm();
    }

    public function forceDelete(User $user, QualityDocument $qualityDocument): bool
    {
        return $user->can('quality-documents.delete')
            && $user->isAdminLpm();
    }
}
