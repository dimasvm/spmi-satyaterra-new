<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum QualityDocumentType: string implements HasColor, HasLabel
{
    case Policy = 'policy';
    case Manual = 'manual';
    case Standard = 'standard';
    case Sop = 'sop';
    case Form = 'form';
    case Sk = 'sk';
    case Guideline = 'guideline';
    case Report = 'report';
    case Other = 'other';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Policy => 'Kebijakan',
            self::Manual => 'Manual',
            self::Standard => 'Standar',
            self::Sop => 'SOP',
            self::Form => 'Formulir',
            self::Sk => 'SK',
            self::Guideline => 'Pedoman',
            self::Report => 'Laporan',
            self::Other => 'Lainnya',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Policy, self::Manual, self::Standard => 'info',
            self::Sop, self::Guideline => 'success',
            self::Form, self::Report => 'warning',
            self::Sk => 'danger',
            self::Other => 'gray',
        };
    }
}
