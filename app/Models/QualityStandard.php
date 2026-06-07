<?php

namespace App\Models;

use App\Enums\QualityStandardStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QualityStandard extends Model
{
    use HasFactory;

    protected $fillable = [
        'standard_category_id',
        'spmi_period_id',
        'code',
        'name',
        'description',
        'status',
        'version',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
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

    public function documents(): HasMany
    {
        return $this->hasMany(QualityDocument::class);
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
}
