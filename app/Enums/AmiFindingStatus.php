<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum AmiFindingStatus: string implements HasColor, HasLabel
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case WaitingVerification = 'waiting_verification';
    case NeedRevision = 'need_revision';
    case Closed = 'closed';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Open => 'Terbuka',
            self::InProgress => 'Dalam Proses',
            self::WaitingVerification => 'Menunggu Verifikasi',
            self::NeedRevision => 'Perlu Revisi',
            self::Closed => 'Ditutup',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Open => 'gray',
            self::InProgress, self::WaitingVerification => 'warning',
            self::NeedRevision => 'danger',
            self::Closed => 'success',
        };
    }
}
