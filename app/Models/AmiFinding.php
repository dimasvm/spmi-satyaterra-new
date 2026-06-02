<?php

namespace App\Models;

use App\Enums\AmiFindingCategory;
use App\Enums\AmiFindingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AmiFinding extends Model
{
    use HasFactory;

    protected $fillable = [
        'ami_audit_id',
        'ami_checklist_id',
        'standard_indicator_id',
        'finding_number',
        'category',
        'description',
        'root_cause',
        'recommendation',
        'due_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'category' => AmiFindingCategory::class,
        'due_date' => 'date',
        'status' => AmiFindingStatus::class,
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(AmiAudit::class, 'ami_audit_id');
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(AmiChecklist::class, 'ami_checklist_id');
    }

    public function standardIndicator(): BelongsTo
    {
        return $this->belongsTo(StandardIndicator::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function correctiveActions(): HasMany
    {
        return $this->hasMany(CorrectiveAction::class);
    }

    public function latestCorrectiveAction(): HasOne
    {
        return $this->hasOne(CorrectiveAction::class)->latestOfMany();
    }
}
