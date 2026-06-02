<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum StandardIndicatorType: string implements HasColor, HasLabel
{
    case Percentage = 'percentage';
    case Number = 'number';
    case Boolean = 'boolean';
    case Checklist = 'checklist';
    case Text = 'text';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Percentage => 'Persentase',
            self::Number => 'Angka',
            self::Boolean => 'Ya/Tidak',
            self::Checklist => 'Checklist',
            self::Text => 'Teks',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Percentage => 'info',
            self::Number => 'success',
            self::Boolean => 'warning',
            self::Checklist => 'gray',
            self::Text => 'gray',
        };
    }
}
