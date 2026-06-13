<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum StandardRevisionType: string implements HasColor, HasLabel
{
    case StandardRevision = 'standard_revision';
    case IndicatorRevision = 'indicator_revision';
    case TargetRevision = 'target_revision';
    case NewStandard = 'new_standard';
    case NewIndicator = 'new_indicator';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::StandardRevision => 'Revisi Standar',
            self::IndicatorRevision => 'Revisi Indikator',
            self::TargetRevision => 'Revisi Target',
            self::NewStandard => 'Standar Baru',
            self::NewIndicator => 'Indikator Baru',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::NewStandard, self::NewIndicator => 'success',
            self::TargetRevision => 'warning',
            default => 'info',
        };
    }
}
