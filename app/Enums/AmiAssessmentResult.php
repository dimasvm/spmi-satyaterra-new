<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum AmiAssessmentResult: string implements HasColor, HasLabel
{
    case Conform = 'conform';
    case Observation = 'observation';
    case Minor = 'minor';
    case Major = 'major';
    case Ofi = 'ofi';
    case NotApplicable = 'not_applicable';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Conform => 'Sesuai',
            self::Observation => 'Observasi',
            self::Minor => 'Minor',
            self::Major => 'Mayor',
            self::Ofi => 'OFI',
            self::NotApplicable => 'Tidak Berlaku',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Conform => 'success',
            self::Observation, self::Ofi => 'info',
            self::Minor => 'warning',
            self::Major => 'danger',
            self::NotApplicable => 'gray',
        };
    }
}
