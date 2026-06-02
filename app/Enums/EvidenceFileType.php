<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum EvidenceFileType: string implements HasColor, HasLabel
{
    case Pdf = 'pdf';
    case Docx = 'docx';
    case Xlsx = 'xlsx';
    case Image = 'image';
    case Link = 'link';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Pdf => 'PDF',
            self::Docx => 'DOCX',
            self::Xlsx => 'XLSX',
            self::Image => 'Gambar',
            self::Link => 'Tautan',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pdf => 'danger',
            self::Docx => 'info',
            self::Xlsx => 'success',
            self::Image => 'warning',
            self::Link => 'gray',
        };
    }
}
