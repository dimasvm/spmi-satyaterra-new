<?php

namespace App\Models;

use App\Enums\CorrectiveActionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CorrectiveAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'ami_finding_id',
        'action_plan',
        'root_cause_analysis',
        'pic_user_id',
        'target_date',
        'status',
        'submitted_at',
        'submitted_by',
    ];

    protected $casts = [
        'target_date' => 'date',
        'status' => CorrectiveActionStatus::class,
        'submitted_at' => 'datetime',
    ];

    public function finding(): BelongsTo
    {
        return $this->belongsTo(AmiFinding::class, 'ami_finding_id');
    }

    public function picUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(CorrectiveActionEvidence::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CorrectiveActionReview::class);
    }

    public function latestReview(): HasOne
    {
        return $this->hasOne(CorrectiveActionReview::class)->latestOfMany();
    }
}
