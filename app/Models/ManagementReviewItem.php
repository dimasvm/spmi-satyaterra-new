<?php

namespace App\Models;

use App\Enums\ManagementReviewItemPriority;
use App\Enums\ManagementReviewItemStatus;
use App\Enums\ManagementReviewItemType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManagementReviewItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'management_review_id',
        'item_type',
        'reference_type',
        'reference_id',
        'title',
        'description',
        'analysis',
        'decision',
        'recommendation',
        'priority',
        'status',
    ];

    protected $casts = [
        'item_type' => ManagementReviewItemType::class,
        'priority' => ManagementReviewItemPriority::class,
        'status' => ManagementReviewItemStatus::class,
    ];

    public function managementReview(): BelongsTo
    {
        return $this->belongsTo(ManagementReview::class);
    }
}
