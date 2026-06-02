<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum CorrectiveActionStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case InReview = 'in_review';
    case NeedRevision = 'need_revision';
    case Accepted = 'accepted';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Submitted => 'Dikirim',
            self::InReview => 'Ditinjau',
            self::NeedRevision => 'Perlu Revisi',
            self::Accepted => 'Diterima',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted, self::InReview => 'info',
            self::NeedRevision => 'warning',
            self::Accepted => 'success',
        };
    }
}
