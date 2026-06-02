<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum SpmiSemester: string implements HasColor, HasLabel
{
    case Ganjil = 'ganjil';
    case Genap = 'genap';
    case Tahunan = 'tahunan';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Ganjil => 'Ganjil',
            self::Genap => 'Genap',
            self::Tahunan => 'Tahunan',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Ganjil => 'info',
            self::Genap => 'success',
            self::Tahunan => 'warning',
        };
    }
}
