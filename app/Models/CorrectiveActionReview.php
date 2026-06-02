<?php

namespace App\Models;

use App\Enums\CorrectiveActionReviewStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorrectiveActionReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'corrective_action_id',
        'reviewer_id',
        'status',
        'notes',
        'reviewed_at',
    ];

    protected $casts = [
        'status' => CorrectiveActionReviewStatus::class,
        'reviewed_at' => 'datetime',
    ];

    public function correctiveAction(): BelongsTo
    {
        return $this->belongsTo(CorrectiveAction::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
