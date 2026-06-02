<?php

namespace App\Models;

use App\Enums\AchievementReviewStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AchievementReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'indicator_achievement_id',
        'reviewer_id',
        'status',
        'notes',
        'reviewed_at',
    ];

    protected $casts = [
        'status' => AchievementReviewStatus::class,
        'reviewed_at' => 'datetime',
    ];

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(IndicatorAchievement::class, 'indicator_achievement_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
