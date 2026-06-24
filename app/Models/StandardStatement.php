<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StandardStatement extends Model
{
    use HasFactory;

    protected $fillable = [
        'quality_standard_id',
        'code',
        'statement',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function qualityStandard(): BelongsTo
    {
        return $this->belongsTo(QualityStandard::class);
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(StandardIndicator::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return trim("{$this->code} - ".str($this->statement)->limit(90));
    }
}
