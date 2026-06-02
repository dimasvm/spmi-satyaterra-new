<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum AchievementStatus: string implements HasColor, HasLabel
{
    case Achieved = 'achieved';
    case NotAchieved = 'not_achieved';
    case PartiallyAchieved = 'partially_achieved';
    case NotApplicable = 'not_applicable';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Achieved => 'Tercapai',
            self::NotAchieved => 'Tidak Tercapai',
            self::PartiallyAchieved => 'Tercapai Sebagian',
            self::NotApplicable => 'Tidak Berlaku',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Achieved => 'success',
            self::NotAchieved => 'danger',
            self::PartiallyAchieved => 'warning',
            self::NotApplicable => 'gray',
        };
    }
}
