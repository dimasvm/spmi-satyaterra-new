<?php

namespace App\Models;

use App\Enums\CorrectiveActionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CorrectiveAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'ami_finding_id',
        'action_plan',
        'root_cause_analysis',
        'pic_user_id',
        'target_date',
        'status',
        'submitted_at',
        'submitted_by',
    ];

    protected $casts = [
        'target_date' => 'date',
        'status' => CorrectiveActionStatus::class,
        'submitted_at' => 'datetime',
    ];

    public function scopeVisibleToUser(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(['super_admin', 'admin_lpm', 'pimpinan'])) {
            return $query;
        }

        if ($user->hasRole('auditor')) {
            return $query->whereHas('finding.audit.auditorAssignments', fn (Builder $auditorQuery): Builder => $auditorQuery
                ->where('user_id', $user->id));
        }

        if ($user->hasRole('unit_pic') && $user->unit_id !== null) {
            return $query->whereHas('finding.audit', fn (Builder $auditQuery): Builder => $auditQuery
                ->where('auditee_unit_id', $user->unit_id));
        }

        return $query->whereRaw('1 = 0');
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->visibleToUser($user);
    }

    public function scopeForUnit(Builder $query, int|string|null $unitId): Builder
    {
        return $query->whereHas('finding.audit', fn (Builder $auditQuery): Builder => $auditQuery
            ->where('auditee_unit_id', $unitId));
    }

    public function isOwnedByUnit(User $user): bool
    {
        return $user->hasRole('unit_pic')
            && $user->unit_id !== null
            && $this->finding?->audit?->auditee_unit_id === $user->unit_id;
    }

    public function isReviewableBy(User $user): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin_lpm'])) {
            return true;
        }

        return $user->hasRole('auditor')
            && $this->finding?->audit !== null
            && $this->finding->audit->auditorAssignments()
                ->where('user_id', $user->id)
                ->exists();
    }

    public function isOverdue(): bool
    {
        if ($this->status === CorrectiveActionStatus::Accepted) {
            return false;
        }

        $deadline = $this->target_date ?? $this->finding?->due_date;

        return $deadline !== null && $deadline->isPast() && ! $deadline->isToday();
    }

    public function finding(): BelongsTo
    {
        return $this->belongsTo(AmiFinding::class, 'ami_finding_id');
    }

    public function picUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(CorrectiveActionEvidence::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CorrectiveActionReview::class);
    }

    public function latestReview(): HasOne
    {
        return $this->hasOne(CorrectiveActionReview::class)->latestOfMany();
    }
}
