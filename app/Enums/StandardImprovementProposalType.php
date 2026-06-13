<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum StandardImprovementProposalType: string implements HasColor, HasLabel
{
    case ReviseStandard = 'revise_standard';
    case CreateNewStandard = 'create_new_standard';
    case ReviseIndicator = 'revise_indicator';
    case CreateNewIndicator = 'create_new_indicator';
    case RemoveIndicator = 'remove_indicator';
    case ReviseTarget = 'revise_target';
    case ReviseDocument = 'revise_document';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::ReviseStandard => 'Revisi Standar',
            self::CreateNewStandard => 'Standar Baru',
            self::ReviseIndicator => 'Revisi Indikator',
            self::CreateNewIndicator => 'Indikator Baru',
            self::RemoveIndicator => 'Hapus Indikator',
            self::ReviseTarget => 'Revisi Target',
            self::ReviseDocument => 'Revisi Dokumen',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CreateNewStandard, self::CreateNewIndicator => 'success',
            self::RemoveIndicator => 'danger',
            self::ReviseTarget => 'warning',
            self::ReviseDocument => 'gray',
            default => 'info',
        };
    }
}
