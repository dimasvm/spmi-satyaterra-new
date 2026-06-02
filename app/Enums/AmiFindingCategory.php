<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum AmiFindingCategory: string implements HasColor, HasLabel
{
    case Observation = 'observation';
    case Minor = 'minor';
    case Major = 'major';
    case Ofi = 'ofi';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Observation => 'Observasi',
            self::Minor => 'Minor',
            self::Major => 'Mayor',
            self::Ofi => 'OFI',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Observation, self::Ofi => 'info',
            self::Minor => 'warning',
            self::Major => 'danger',
        };
    }
}
