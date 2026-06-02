<?php

namespace App\Models;

use App\Enums\QualityDocumentStatus;
use App\Enums\QualityDocumentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'quality_standard_id',
        'spmi_period_id',
        'title',
        'document_type',
        'document_number',
        'version',
        'file_path',
        'external_url',
        'status',
        'uploaded_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'document_type' => QualityDocumentType::class,
        'version' => 'integer',
        'status' => QualityDocumentStatus::class,
        'approved_at' => 'datetime',
    ];

    public function qualityStandard(): BelongsTo
    {
        return $this->belongsTo(QualityStandard::class);
    }

    public function spmiPeriod(): BelongsTo
    {
        return $this->belongsTo(SpmiPeriod::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
