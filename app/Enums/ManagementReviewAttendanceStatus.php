<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ManagementReviewAttendanceStatus: string implements HasColor, HasLabel
{
    case Present = 'present';
    case Absent = 'absent';
    case Represented = 'represented';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Present => 'Hadir',
            self::Absent => 'Tidak Hadir',
            self::Represented => 'Diwakilkan',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Present => 'success',
            self::Absent => 'danger',
            self::Represented => 'warning',
        };
    }
}
