<?php

namespace App\Models;

use App\Enums\AchievementReviewStatus;
use Illuminate\Database\Eloquent\Builder;
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

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin() || $user->isAdminLpm() || $user->isPimpinan() || $user->can('indicator-achievements.review')) {
            return $query;
        }

        if ($user->isUnitPic() && $user->unit_id !== null) {
            return $query->whereHas('achievement.assignment', fn (Builder $assignmentQuery): Builder => $assignmentQuery
                ->where('unit_id', $user->unit_id));
        }

        return $query->whereRaw('1 = 0');
    }

    public function scopeForUnit(Builder $query, int|string|null $unitId): Builder
    {
        return $query->whereHas('achievement.assignment', fn (Builder $assignmentQuery): Builder => $assignmentQuery
            ->where('unit_id', $unitId));
    }
}
