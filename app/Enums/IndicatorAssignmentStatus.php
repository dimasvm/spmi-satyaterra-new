<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum IndicatorAssignmentStatus: string implements HasColor, HasLabel
{
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Submitted = 'submitted';
    case Validated = 'validated';
    case Returned = 'returned';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Assigned => 'Ditugaskan',
            self::InProgress => 'Dalam Proses',
            self::Submitted => 'Dikirim',
            self::Validated => 'Tervalidasi',
            self::Returned => 'Dikembalikan',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Assigned => 'gray',
            self::InProgress => 'info',
            self::Submitted => 'warning',
            self::Validated => 'success',
            self::Returned => 'danger',
        };
    }
}
