<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum ManagementReviewItemType: string implements HasColor, HasLabel
{
    case AuditFinding = 'audit_finding';
    case IndicatorAchievement = 'indicator_achievement';
    case CorrectiveAction = 'corrective_action';
    case General = 'general';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::AuditFinding => 'Temuan Audit',
            self::IndicatorAchievement => 'Capaian Indikator',
            self::CorrectiveAction => 'Tindak Lanjut',
            self::General => 'Umum',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::AuditFinding => 'danger',
            self::IndicatorAchievement => 'warning',
            self::CorrectiveAction => 'info',
            self::General => 'gray',
        };
    }
}
