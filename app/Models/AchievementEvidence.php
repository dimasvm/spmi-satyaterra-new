<?php

namespace App\Models;

use App\Enums\EvidenceFileType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AchievementEvidence extends Model
{
    use HasFactory;

    protected $fillable = [
        'indicator_achievement_id',
        'file_name',
        'file_path',
        'file_type',
        'external_url',
        'description',
        'uploaded_by',
    ];

    protected $casts = [
        'file_type' => EvidenceFileType::class,
    ];

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(IndicatorAchievement::class, 'indicator_achievement_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
