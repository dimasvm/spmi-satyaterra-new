<?php

namespace App\Models;

use App\Enums\SpmiPeriodStatus;
use App\Enums\SpmiSemester;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpmiPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'academic_year',
        'semester',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'semester' => SpmiSemester::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => SpmiPeriodStatus::class,
    ];

    public function qualityStandards(): HasMany
    {
        return $this->hasMany(QualityStandard::class);
    }

    public function indicatorAssignments(): HasMany
    {
        return $this->hasMany(IndicatorUnitAssignment::class);
    }

    public function amiPeriods(): HasMany
    {
        return $this->hasMany(AmiPeriod::class);
    }

    public function qualityDocuments(): HasMany
    {
        return $this->hasMany(QualityDocument::class);
    }
}
