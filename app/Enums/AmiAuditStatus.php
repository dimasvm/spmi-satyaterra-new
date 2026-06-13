<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum AmiAuditStatus: string implements HasColor, HasLabel
{
    case Planned = 'planned';
    case Scheduled = 'scheduled';
    case Ongoing = 'ongoing';
    case Completed = 'completed';
    case Finalized = 'finalized';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Planned => 'Direncanakan',
            self::Scheduled => 'Terjadwal',
            self::Ongoing => 'Berjalan',
            self::Completed => 'Selesai',
            self::Finalized => 'Final',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Planned => 'gray',
            self::Scheduled => 'warning',
            self::Ongoing => 'info',
            self::Completed, self::Finalized => 'success',
        };
    }
}
