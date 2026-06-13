<?php

namespace App\Models;

use App\Enums\StandardRevisionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StandardRevisionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'quality_standard_id',
        'standard_indicator_id',
        'standard_improvement_proposal_id',
        'revision_type',
        'old_data',
        'new_data',
        'notes',
        'revised_by',
        'revised_at',
    ];

    protected $casts = [
        'revision_type' => StandardRevisionType::class,
        'old_data' => 'array',
        'new_data' => 'array',
        'revised_at' => 'datetime',
    ];

    public function qualityStandard(): BelongsTo
    {
        return $this->belongsTo(QualityStandard::class);
    }

    public function standardIndicator(): BelongsTo
    {
        return $this->belongsTo(StandardIndicator::class);
    }

    public function standardImprovementProposal(): BelongsTo
    {
        return $this->belongsTo(StandardImprovementProposal::class);
    }

    public function revisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revised_by');
    }
}
