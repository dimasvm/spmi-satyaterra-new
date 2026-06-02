<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum AchievementReviewStatus: string implements HasColor, HasLabel
{
    case Validated = 'validated';
    case Returned = 'returned';
    case Rejected = 'rejected';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Validated => 'Tervalidasi',
            self::Returned => 'Perlu Perbaikan',
            self::Rejected => 'Ditolak',
        };
    }

    public function getColor(): array|string|null
    {
        return match ($this) {
            self::Validated => 'success',
            self::Returned => 'warning',
            self::Rejected => 'danger',
        };
    }
}
