<?php

namespace App\Models;

use App\Enums\UnitType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'type',
        'is_active',
    ];

    protected $casts = [
        'type' => UnitType::class,
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function indicators()
    {
        return $this->belongsToMany(StandardIndicator::class, IndicatorUnitAssignment::class);
    }

    public function indicatorAssignments(): HasMany
    {
        return $this->hasMany(IndicatorUnitAssignment::class);
    }

    public function amiAudits(): HasMany
    {
        return $this->hasMany(AmiAudit::class, 'auditee_unit_id');
    }

    public function scopeActive(Builder $query)
    {
        $query->where('is_active', true);
    }

    public function scopenonActive(Builder $query)
    {
        $query->where('is_active', false);
    }
}
