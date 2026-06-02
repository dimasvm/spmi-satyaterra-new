<?php

namespace App\Models;

use App\Enums\AmiAuditorRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmiAuditor extends Model
{
    use HasFactory;

    protected $fillable = [
        'ami_audit_id',
        'user_id',
        'role',
    ];

    protected $casts = [
        'role' => AmiAuditorRole::class,
    ];

    public function audit(): BelongsTo
    {
        return $this->belongsTo(AmiAudit::class, 'ami_audit_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
