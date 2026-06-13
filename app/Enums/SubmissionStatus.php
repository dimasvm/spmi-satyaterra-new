<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum SubmissionStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Returned = 'returned';
    case Validated = 'validated';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Submitted => 'Dikirim',
            self::Returned => 'Dikembalikan',
            self::Validated => 'Tervalidasi',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'warning',
            self::Returned => 'danger',
            self::Validated => 'success',
        };
    }
}
