<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['unit_id', 'name', 'email', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function approvedQualityStandards(): HasMany
    {
        return $this->hasMany(QualityStandard::class, 'approved_by');
    }

    public function submittedAchievements(): HasMany
    {
        return $this->hasMany(IndicatorAchievement::class, 'submitted_by');
    }

    public function uploadedAchievementEvidences(): HasMany
    {
        return $this->hasMany(AchievementEvidence::class, 'uploaded_by');
    }

    public function achievementReviews(): HasMany
    {
        return $this->hasMany(AchievementReview::class, 'reviewer_id');
    }

    public function amiAuditorAssignments(): HasMany
    {
        return $this->hasMany(AmiAuditor::class);
    }

    public function finalizedAmiAudits(): HasMany
    {
        return $this->hasMany(AmiAudit::class, 'finalized_by');
    }

    public function createdAmiFindings(): HasMany
    {
        return $this->hasMany(AmiFinding::class, 'created_by');
    }

    public function correctiveActionsAsPic(): HasMany
    {
        return $this->hasMany(CorrectiveAction::class, 'pic_user_id');
    }

    public function submittedCorrectiveActions(): HasMany
    {
        return $this->hasMany(CorrectiveAction::class, 'submitted_by');
    }

    public function uploadedCorrectiveActionEvidences(): HasMany
    {
        return $this->hasMany(CorrectiveActionEvidence::class, 'uploaded_by');
    }

    public function correctiveActionReviews(): HasMany
    {
        return $this->hasMany(CorrectiveActionReview::class, 'reviewer_id');
    }

    public function uploadedQualityDocuments(): HasMany
    {
        return $this->hasMany(QualityDocument::class, 'uploaded_by');
    }

    public function approvedQualityDocuments(): HasMany
    {
        return $this->hasMany(QualityDocument::class, 'approved_by');
    }

    public function createdManagementReviews(): HasMany
    {
        return $this->hasMany(ManagementReview::class, 'created_by');
    }

    public function finalizedManagementReviews(): HasMany
    {
        return $this->hasMany(ManagementReview::class, 'finalized_by');
    }

    public function proposedStandardImprovements(): HasMany
    {
        return $this->hasMany(StandardImprovementProposal::class, 'proposed_by');
    }

    public function reviewedStandardImprovements(): HasMany
    {
        return $this->hasMany(StandardImprovementProposal::class, 'reviewed_by');
    }

    public function implementedStandardImprovements(): HasMany
    {
        return $this->hasMany(StandardImprovementProposal::class, 'implemented_by');
    }

    public function standardRevisionHistories(): HasMany
    {
        return $this->hasMany(StandardRevisionHistory::class, 'revised_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isAdminLpm(): bool
    {
        return $this->hasRole('admin_lpm');
    }

    public function isPimpinan(): bool
    {
        return $this->hasRole('pimpinan');
    }

    public function isUnitPic(): bool
    {
        return $this->hasRole('unit_pic');
    }

    public function isAuditor(): bool
    {
        return $this->hasRole('auditor');
    }

    public function canAccessUnit(int|string|null $unitId): bool
    {
        if ($this->isSuperAdmin() || $this->isAdminLpm() || $this->isPimpinan()) {
            return true;
        }

        return $this->isUnitPic()
            && $this->unit_id !== null
            && (int) $this->unit_id === (int) $unitId;
    }

    /**
     * @return array<int, int>
     */
    public function assignedAmiAuditIds(): array
    {
        return $this->amiAuditorAssignments()
            ->pluck('ami_audit_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();
    }

    public function canManageOperationalData(): bool
    {
        return $this->isSuperAdmin() || $this->isAdminLpm();
    }
}
