<?php

namespace App\Models;

use App\Enums\AmiAuditStatus;
use App\Enums\AmiFindingCategory;
use App\Enums\AmiFindingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AmiFinding extends Model
{
    use HasFactory;

    protected $fillable = [
        'ami_audit_id',
        'ami_checklist_id',
        'standard_indicator_id',
        'finding_number',
        'category',
        'description',
        'root_cause',
        'recommendation',
        'due_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'category' => AmiFindingCategory::class,
        'due_date' => 'date',
        'status' => AmiFindingStatus::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (AmiFinding $finding): void {
            $finding->status ??= AmiFindingStatus::Open;
            $finding->created_by ??= auth()->id();
            $finding->finding_number ??= static::makeFindingNumber($finding);
        });
    }

    public static function makeFindingNumber(AmiFinding $finding): string
    {
        $audit = $finding->audit()->with(['amiPeriod', 'auditeeUnit'])->first();
        $year = $audit?->scheduled_date?->format('Y')
            ?? $audit?->amiPeriod?->start_date?->format('Y')
            ?? now()->format('Y');
        $unitCode = $audit?->auditeeUnit?->code ?: 'UNIT';
        $sequence = static::query()
            ->where('ami_audit_id', $finding->ami_audit_id)
            ->count() + 1;

        return sprintf('AMI-%s-%s-%03d', $year, $unitCode, $sequence);
    }

    public function scopeVisibleToUser(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(['super_admin', 'admin_lpm', 'pimpinan'])) {
            return $query;
        }

        if ($user->hasRole('auditor')) {
            return $query->whereHas('audit.auditorAssignments', fn (Builder $auditorQuery): Builder => $auditorQuery
                ->where('user_id', $user->id));
        }

        if ($user->hasRole('unit_pic') && $user->unit_id !== null) {
            return $query->whereHas('audit', fn (Builder $auditQuery): Builder => $auditQuery
                ->where('auditee_unit_id', $user->unit_id)
                ->where('status', AmiAuditStatus::Finalized->value));
        }

        return $query->whereRaw('1 = 0');
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->visibleToUser($user);
    }

    public function scopeForUnit(Builder $query, int|string|null $unitId): Builder
    {
        return $query->whereHas('audit', fn (Builder $auditQuery): Builder => $auditQuery
            ->where('auditee_unit_id', $unitId));
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(AmiAudit::class, 'ami_audit_id');
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(AmiChecklist::class, 'ami_checklist_id');
    }

    public function standardIndicator(): BelongsTo
    {
        return $this->belongsTo(StandardIndicator::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function correctiveActions(): HasMany
    {
        return $this->hasMany(CorrectiveAction::class);
    }

    public function latestCorrectiveAction(): HasOne
    {
        return $this->hasOne(CorrectiveAction::class)->latestOfMany();
    }
}
