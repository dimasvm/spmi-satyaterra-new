<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum CorrectiveActionReviewStatus: string implements HasColor, HasLabel
{
    case Accepted = 'accepted';
    case NeedRevision = 'need_revision';
    case Rejected = 'rejected';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Accepted => 'Diterima',
            self::NeedRevision => 'Perlu Revisi',
            self::Rejected => 'Ditolak',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Accepted => 'success',
            self::NeedRevision => 'warning',
            self::Rejected => 'danger',
        };
    }
}
