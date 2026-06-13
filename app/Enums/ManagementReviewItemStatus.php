<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ManagementReviewItemStatus: string implements HasColor, HasLabel
{
    case Open = 'open';
    case Discussed = 'discussed';
    case Decided = 'decided';
    case FollowedUp = 'followed_up';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Open => 'Terbuka',
            self::Discussed => 'Dibahas',
            self::Decided => 'Diputuskan',
            self::FollowedUp => 'Ditindaklanjuti',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Open => 'gray',
            self::Discussed => 'info',
            self::Decided => 'success',
            self::FollowedUp => 'primary',
        };
    }
}
