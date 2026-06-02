<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ActivityLogEvent: string implements HasColor, HasLabel
{
    case Created = 'created';
    case Updated = 'updated';
    case Submitted = 'submitted';
    case Reviewed = 'reviewed';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Deleted = 'deleted';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Created => 'Dibuat',
            self::Updated => 'Diperbarui',
            self::Submitted => 'Dikirim',
            self::Reviewed => 'Ditinjau',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
            self::Deleted => 'Dihapus',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Created, self::Updated => 'gray',
            self::Submitted, self::Reviewed => 'info',
            self::Approved => 'success',
            self::Rejected, self::Deleted => 'danger',
        };
    }
}
