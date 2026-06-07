<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndicatorAssignmentEvent extends Model
{
    protected $fillable = [
        'indicator_unit_assignment_id',
        'actor_id',
        'event',
        'description',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        //
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(IndicatorUnitAssignment::class, 'indicator_unit_assignment_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
