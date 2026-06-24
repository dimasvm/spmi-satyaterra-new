<?php

namespace App\Models;

use App\Enums\QualityStandardStatus;
use App\Enums\UnitType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class QualityStandard extends Model
{
    use HasFactory;

    protected $fillable = [
        'standard_category_id',
        'scope_type',
        'spmi_period_id',
        'code',
        'name',
        'statement',
        'description',
        'status',
        'version',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'scope_type' => UnitType::class,
        'status' => QualityStandardStatus::class,
        'version' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(StandardCategory::class, 'standard_category_id');
    }

    public function spmiPeriod(): BelongsTo
    {
        return $this->belongsTo(SpmiPeriod::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(StandardIndicator::class);
    }

    public function statements(): HasMany
    {
        return $this->hasMany(StandardStatement::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(QualityDocument::class);
    }

    public function improvementProposals(): HasMany
    {
        return $this->hasMany(StandardImprovementProposal::class);
    }

    public function createdFromImprovementProposals(): HasMany
    {
        return $this->hasMany(StandardImprovementProposal::class, 'created_standard_id');
    }

    public function revisionHistories(): HasMany
    {
        return $this->hasMany(StandardRevisionHistory::class);
    }

    public function assignments(): HasManyThrough
    {
        return $this->hasManyThrough(
            IndicatorUnitAssignment::class,
            StandardIndicator::class,
            'quality_standard_id',
            'standard_indicator_id',
        );
    }

    public function achievements(): Builder
    {
        return IndicatorAchievement::query()
            ->whereHas('assignment.standardIndicator', fn (Builder $query): Builder => $query
                ->where('quality_standard_id', $this->getKey()));
    }

    // Scope -----------------------------------------------
    public function scopeActive(Builder $query)
    {
        $query->where('status', QualityStandardStatus::Active);
    }

    public function scopeNonactive(Builder $query)
    {
        $query->whereNot('status', QualityStandardStatus::Active);
    }

    public function scopeForStandardCategory(Builder $query, int|string $categoryId): Builder
    {
        return $query->where(function (Builder $query) use ($categoryId): void {
            $query
                ->where('standard_category_id', $categoryId)
                ->orWhereHas(
                    'category',
                    fn (Builder $categoryQuery): Builder => $categoryQuery->where('parent_id', $categoryId),
                );
        });
    }
}
