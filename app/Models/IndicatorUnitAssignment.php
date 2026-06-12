<?php

namespace App\Models;

use App\Enums\IndicatorAssignmentPriority;
use App\Enums\IndicatorAssignmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class IndicatorUnitAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'standard_indicator_id',
        'unit_id',
        'spmi_period_id',
        'due_date',
        'status',
        'is_primary_pic',
        'priority',
        'notes',
        'assigned_by',
        'assigned_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'status' => IndicatorAssignmentStatus::class,
        'is_primary_pic' => 'boolean',
        'priority' => IndicatorAssignmentPriority::class,
        'assigned_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (IndicatorUnitAssignment $assignment): void {
            $assignment->events()->create([
                'actor_id' => $assignment->assigned_by,
                'event' => 'assigned',
                'description' => $assignment->is_primary_pic
                    ? 'Penugasan dibuat sebagai PIC utama.'
                    : 'Penugasan dibuat sebagai unit terlibat.',
                'occurred_at' => $assignment->assigned_at ?? now(),
            ]);
        });

        static::updated(function (IndicatorUnitAssignment $assignment): void {
            if (! $assignment->wasChanged('status')) {
                return;
            }

            $assignment->events()->create([
                'actor_id' => Auth::user()->id,
                'event' => 'status_changed',
                'description' => 'Status penugasan diubah menjadi '.$assignment->status->getLabel().'.',
                'occurred_at' => now(),
            ]);
        });
    }

    public function standardIndicator(): BelongsTo
    {
        return $this->belongsTo(StandardIndicator::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function spmiPeriod(): BelongsTo
    {
        return $this->belongsTo(SpmiPeriod::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(IndicatorAchievement::class, 'assignment_id');
    }

    public function latestAchievement(): HasOne
    {
        return $this->hasOne(IndicatorAchievement::class, 'assignment_id')->latestOfMany();
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(IndicatorAssignmentEvent::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin() || $user->isAdminLpm() || $user->isPimpinan()) {
            return $query;
        }

        if ($user->isUnitPic() && $user->unit_id !== null) {
            return $query->where('unit_id', $user->unit_id);
        }

        if ($user->isAuditor()) {
            return $query->whereIn(
                'unit_id',
                AmiAudit::query()
                    ->select('auditee_unit_id')
                    ->forUser($user),
            );
        }

        return $query->whereRaw('1 = 0');
    }

    public function scopeForUnit(Builder $query, int|string|null $unitId): Builder
    {
        return $query->where('unit_id', $unitId);
    }

    public function scopeForActivePeriod(Builder $query): Builder
    {
        return $query->whereHas('spmiPeriod', fn (Builder $periodQuery): Builder => $periodQuery->active());
    }
}
