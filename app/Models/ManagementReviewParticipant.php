<?php

namespace App\Models;

use App\Enums\ManagementReviewAttendanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagementReviewParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'management_review_id',
        'user_id',
        'name',
        'position',
        'unit_id',
        'attendance_status',
        'notes',
    ];

    protected $casts = [
        'attendance_status' => ManagementReviewAttendanceStatus::class,
    ];

    public function managementReview(): BelongsTo
    {
        return $this->belongsTo(ManagementReview::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
