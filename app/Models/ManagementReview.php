<?php

namespace App\Models;

use App\Enums\ManagementReviewStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManagementReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'spmi_period_id',
        'ami_period_id',
        'title',
        'meeting_date',
        'location',
        'agenda',
        'summary',
        'conclusion',
        'status',
        'created_by',
        'finalized_by',
        'finalized_at',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'status' => ManagementReviewStatus::class,
        'finalized_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (ManagementReview $managementReview): void {
            $managementReview->created_by ??= auth()->id();
            $managementReview->status ??= ManagementReviewStatus::Draft;
        });
    }

    public function spmiPeriod(): BelongsTo
    {
        return $this->belongsTo(SpmiPeriod::class);
    }

    public function amiPeriod(): BelongsTo
    {
        return $this->belongsTo(AmiPeriod::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ManagementReviewParticipant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ManagementReviewItem::class);
    }

    public function improvementProposals(): HasMany
    {
        return $this->hasMany(StandardImprovementProposal::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(['super_admin', 'admin_lpm', 'pimpinan', 'viewer'])) {
            return $query;
        }

        if ($user->isAuditor()) {
            return $query->whereHas('amiPeriod.audits.auditorAssignments', fn (Builder $auditorQuery): Builder => $auditorQuery
                ->where('user_id', $user->id));
        }

        if ($user->isUnitPic() && $user->unit_id !== null) {
            return $query->whereHas('amiPeriod.audits', fn (Builder $auditQuery): Builder => $auditQuery
                ->where('auditee_unit_id', $user->unit_id));
        }

        return $query->whereRaw('1 = 0');
    }

    public function canBeManagedBy(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAdminLpm();
    }
}
