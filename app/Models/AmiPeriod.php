<?php

namespace App\Models;

use App\Enums\AmiPeriodStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AmiPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'spmi_period_id',
        'name',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => AmiPeriodStatus::class,
    ];

    public function spmiPeriod(): BelongsTo
    {
        return $this->belongsTo(SpmiPeriod::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(AmiAudit::class);
    }
}
