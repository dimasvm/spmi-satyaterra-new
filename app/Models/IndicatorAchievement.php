<?php

namespace App\Models;

use App\Enums\AchievementStatus;
use App\Enums\SubmissionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IndicatorAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'realization_value',
        'realization_text',
        'achievement_status',
        'notes',
        'submission_status',
        'submitted_at',
        'submitted_by',
    ];

    protected $casts = [
        'realization_value' => 'decimal:2',
        'achievement_status' => AchievementStatus::class,
        'submission_status' => SubmissionStatus::class,
        'submitted_at' => 'datetime',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(IndicatorUnitAssignment::class, 'assignment_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(AchievementEvidence::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(AchievementReview::class);
    }

    public function latestReview(): HasOne
    {
        return $this->hasOne(AchievementReview::class)->latestOfMany();
    }
}
