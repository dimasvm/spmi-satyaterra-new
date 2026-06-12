<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\InteractsWithSpmiDashboard;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AuditorDashboardPlaceholder extends StatsOverviewWidget
{
    use InteractsWithSpmiDashboard;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return static::canViewAuditorDashboard()
            && ! static::canViewManagementDashboard()
            && ! static::canViewUnitDashboard();
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Penugasan Audit', 'Belum tersedia')
                ->description('Modul AMI belum tersedia untuk dashboard auditor.')
                ->color('gray'),
        ];
    }
}
