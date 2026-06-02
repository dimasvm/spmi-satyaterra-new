<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
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
class User extends Authenticatable
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

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
