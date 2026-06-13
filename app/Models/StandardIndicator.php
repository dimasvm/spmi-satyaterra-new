<?php

namespace App\Models;

use App\Enums\StandardIndicatorType;
use App\Enums\TargetOperator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StandardIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'quality_standard_id',
        'code',
        'statement',
        'indicator_type',
        'target_value',
        'target_operator',
        'target_unit',
        'weight',
        'evidence_required',
        'evidence_description',
    ];

    protected $casts = [
        'indicator_type' => StandardIndicatorType::class,
        'target_value' => 'decimal:2',
        'target_operator' => TargetOperator::class,
        'weight' => 'integer',
        'evidence_required' => 'boolean',
    ];

    public function qualityStandard(): BelongsTo
    {
        return $this->belongsTo(QualityStandard::class);
    }

    public function units()
    {
        return $this->belongsToMany(Unit::class, IndicatorUnitAssignment::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(IndicatorUnitAssignment::class);
    }

    public function amiChecklists(): HasMany
    {
        return $this->hasMany(AmiChecklist::class);
    }

    public function amiFindings(): HasMany
    {
        return $this->hasMany(AmiFinding::class);
    }

    public function improvementProposals(): HasMany
    {
        return $this->hasMany(StandardImprovementProposal::class);
    }

    public function createdFromImprovementProposals(): HasMany
    {
        return $this->hasMany(StandardImprovementProposal::class, 'created_indicator_id');
    }

    public function revisionHistories(): HasMany
    {
        return $this->hasMany(StandardRevisionHistory::class);
    }
}
