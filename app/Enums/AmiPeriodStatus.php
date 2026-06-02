<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum AmiPeriodStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Ongoing = 'ongoing';
    case Completed = 'completed';
    case Closed = 'closed';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Scheduled => 'Terjadwal',
            self::Ongoing => 'Berjalan',
            self::Completed => 'Selesai',
            self::Closed => 'Ditutup',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Scheduled => 'info',
            self::Ongoing => 'warning',
            self::Completed, self::Closed => 'success',
        };
    }
}
