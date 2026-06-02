<?php

namespace App\Models;

use App\Enums\AmiAssessmentResult;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AmiChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'ami_audit_id',
        'standard_indicator_id',
        'assessment_result',
        'auditor_notes',
    ];

    protected $casts = [
        'assessment_result' => AmiAssessmentResult::class,
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(AmiAudit::class, 'ami_audit_id');
    }

    public function standardIndicator(): BelongsTo
    {
        return $this->belongsTo(StandardIndicator::class);
    }

    public function findings(): HasMany
    {
        return $this->hasMany(AmiFinding::class);
    }
}
