<?php

namespace App\Models;

use App\Enums\AmiAssessmentResult;
use App\Enums\AmiAuditStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AmiChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'ami_audit_id',
        'standard_indicator_id',
        'assessment_result',
        'auditor_notes',
    ];

    protected $casts = [
        'assessment_result' => AmiAssessmentResult::class,
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(AmiAudit::class, 'ami_audit_id');
    }

    public function standardIndicator(): BelongsTo
    {
        return $this->belongsTo(StandardIndicator::class);
    }

    public function findings(): HasMany
    {
        return $this->hasMany(AmiFinding::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin() || $user->isAdminLpm() || $user->isPimpinan()) {
            return $query;
        }

        if ($user->isAuditor()) {
            return $query->whereHas('audit.auditorAssignments', fn (Builder $auditorQuery): Builder => $auditorQuery
                ->where('user_id', $user->id));
        }

        if ($user->isUnitPic() && $user->unit_id !== null) {
            return $query->whereHas('audit', fn (Builder $auditQuery): Builder => $auditQuery
                ->where('auditee_unit_id', $user->unit_id)
                ->where('status', AmiAuditStatus::Finalized->value));
        }

        return $query->whereRaw('1 = 0');
    }

    public function scopeForUnit(Builder $query, int|string|null $unitId): Builder
    {
        return $query->whereHas('audit', fn (Builder $auditQuery): Builder => $auditQuery
            ->where('auditee_unit_id', $unitId));
    }
}
