<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum QualityStandardStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Active = 'active';
    case Revised = 'revised';
    case Archived = 'archived';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Submitted => 'Dikirim',
            self::Approved => 'Disetujui',
            self::Active => 'Aktif',
            self::Revised => 'Direvisi',
            self::Archived => 'Diarsipkan',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft, self::Archived => 'gray',
            self::Submitted, self::Revised => 'info',
            self::Approved, self::Active => 'success',
        };
    }
}
