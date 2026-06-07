<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum IndicatorAssignmentPriority: string implements HasColor, HasLabel
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Low => 'Rendah',
            self::Normal => 'Normal',
            self::High => 'Tinggi',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Low => 'gray',
            self::Normal => 'info',
            self::High => 'warning',
        };
    }
}
