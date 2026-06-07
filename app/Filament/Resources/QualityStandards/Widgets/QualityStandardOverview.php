<?php

namespace App\Filament\Resources\QualityStandards\Widgets;

use App\Enums\QualityStandardStatus;
use App\Models\QualityStandard;
use App\Models\StandardIndicator;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QualityStandardOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Standar Mutu', $this->totalQualityStandar()),
            Stat::make('Standar Aktif', $this->totalActive()),
            Stat::make('Standar Nonaktif', $this->totalNonactive()),
            Stat::make('Total Indikator', $this->totalIndicators()),
        ];
    }

    private function totalQualityStandar()
    {
        return QualityStandard::count();
    }

    private function totalActive()
    {
        return QualityStandard::active()->count();
    }

    private function totalNonactive()
    {
        return QualityStandard::nonactive()->count();
    }

    private function totalIndicators()
    {
        return StandardIndicator::whereRelation('qualityStandard', 'status', QualityStandardStatus::Active)->count();
    }
}
