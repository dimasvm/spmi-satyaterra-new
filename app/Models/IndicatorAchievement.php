<?php

namespace App\Models;

use App\Enums\AchievementStatus;
use App\Enums\SubmissionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IndicatorAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'realization_value',
        'realization_text',
        'achievement_status',
        'notes',
        'submission_status',
        'submitted_at',
        'submitted_by',
    ];

    protected $casts = [
        'realization_value' => 'decimal:2',
        'achievement_status' => AchievementStatus::class,
        'submission_status' => SubmissionStatus::class,
        'submitted_at' => 'datetime',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(IndicatorUnitAssignment::class, 'assignment_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(AchievementEvidence::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(AchievementReview::class);
    }

    public function latestReview(): HasOne
    {
        return $this->hasOne(AchievementReview::class)->latestOfMany();
    }

    /**
     * @return Attribute<StandardIndicator|null, never>
     */
    protected function standardIndicator(): Attribute
    {
        return Attribute::make(
            get: fn (): ?StandardIndicator => $this->assignment?->standardIndicator,
        )->withoutObjectCaching();
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin() || $user->isAdminLpm() || $user->isPimpinan() || $user->can('indicator-achievements.review')) {
            return $query;
        }

        if ($user->isUnitPic() && $user->unit_id !== null) {
            return $query->whereHas('assignment', fn (Builder $assignmentQuery): Builder => $assignmentQuery
                ->where('unit_id', $user->unit_id));
        }

        if ($user->isAuditor()) {
            return $query->whereHas('assignment', fn (Builder $assignmentQuery): Builder => $assignmentQuery
                ->whereIn(
                    'unit_id',
                    AmiAudit::query()
                        ->select('auditee_unit_id')
                        ->forUser($user),
                ));
        }

        return $query->whereRaw('1 = 0');
    }

    public function scopeForUnit(Builder $query, int|string|null $unitId): Builder
    {
        return $query->whereHas('assignment', fn (Builder $assignmentQuery): Builder => $assignmentQuery
            ->where('unit_id', $unitId));
    }

    public function scopeForActivePeriod(Builder $query): Builder
    {
        return $query->whereHas('assignment.spmiPeriod', fn (Builder $periodQuery): Builder => $periodQuery->active());
    }
}
