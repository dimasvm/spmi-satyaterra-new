<?php

namespace App\Models;

use App\Enums\AmiAuditorRole;
use App\Enums\AmiAuditStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AmiAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'ami_period_id',
        'auditee_unit_id',
        'scheduled_date',
        'status',
        'notes',
        'finalized_at',
        'finalized_by',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'status' => AmiAuditStatus::class,
        'finalized_at' => 'datetime',
    ];

    public function amiPeriod(): BelongsTo
    {
        return $this->belongsTo(AmiPeriod::class);
    }

    public function auditeeUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'auditee_unit_id');
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function auditorAssignments(): HasMany
    {
        return $this->hasMany(AmiAuditor::class);
    }

    public function auditors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ami_auditors')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function leadAuditorAssignment(): HasOne
    {
        return $this->hasOne(AmiAuditor::class)->where('role', AmiAuditorRole::Lead->value);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(AmiChecklist::class);
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
            return $query->whereHas('auditorAssignments', fn (Builder $auditorQuery): Builder => $auditorQuery
                ->where('user_id', $user->id));
        }

        if ($user->isUnitPic() && $user->unit_id !== null) {
            return $query->where('auditee_unit_id', $user->unit_id);
        }

        return $query->whereRaw('1 = 0');
    }

    public function scopeForUnit(Builder $query, int|string|null $unitId): Builder
    {
        return $query->where('auditee_unit_id', $unitId);
    }
}
