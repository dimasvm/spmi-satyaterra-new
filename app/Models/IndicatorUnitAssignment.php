<?php

namespace App\Models;

use App\Enums\IndicatorAssignmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IndicatorUnitAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'standard_indicator_id',
        'unit_id',
        'spmi_period_id',
        'due_date',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'status' => IndicatorAssignmentStatus::class,
    ];

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
}
