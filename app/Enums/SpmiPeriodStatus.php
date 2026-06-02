<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum SpmiPeriodStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Active = 'active';
    case Closed = 'closed';
    case Archived = 'archived';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Active => 'Aktif',
            self::Closed => 'Ditutup',
            self::Archived => 'Diarsipkan',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft, self::Archived => 'gray',
            self::Active, self::Closed => 'success',
        };
    }
}
