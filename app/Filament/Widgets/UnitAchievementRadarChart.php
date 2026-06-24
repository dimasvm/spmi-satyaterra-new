<?php

namespace App\Filament\Widgets;

use App\Enums\AchievementStatus;
use App\Filament\Widgets\Concerns\InteractsWithSpmiDashboard;
use App\Models\Unit;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class UnitAchievementRadarChart extends ChartWidget
{
    use InteractsWithSpmiDashboard;

    protected ?string $heading = 'Peringkat Capaian Unit';

    public static function canView(): bool
    {
        return static::canViewManagementDashboard();
    }

    protected function getData(): array
    {
        $periodId = $this->selectedSpmiPeriodId();

        $units = Unit::query()
            ->whereHas('indicatorAssignments', fn (Builder $query) => $query->where('spmi_period_id', $periodId))
            ->withCount([
                'indicatorAssignments as total_assignments' => fn (Builder $query) => $query->where('spmi_period_id', $periodId),
                'indicatorAssignments as achieved_assignments' => fn (Builder $query) => $query
                    ->where('spmi_period_id', $periodId)
                    ->whereHas('latestAchievement', fn (Builder $q) => $q->where('achievement_status', AchievementStatus::Achieved->value)),
            ])
            ->get();

        $unitData = $units->map(function ($unit) {
            $percentage = $unit->total_assignments > 0
                ? round(($unit->achieved_assignments / $unit->total_assignments) * 100, 2)
                : 0;

            return [
                'name' => $unit->code,
                'percentage' => $percentage,
            ];
        })
            ->sortByDesc('percentage')
            ->take(10); // Show top 10 units to keep chart readable

        return [
            'datasets' => [
                [
                    'label' => 'Persentase Capaian (%)',
                    'data' => $unitData->pluck('percentage')->toArray(),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgb(54, 162, 235)',
                    'pointBackgroundColor' => 'rgb(54, 162, 235)',
                    'pointBorderColor' => '#fff',
                    'pointHoverBackgroundColor' => '#fff',
                    'pointHoverBorderColor' => 'rgb(54, 162, 235)',
                ],
            ],
            'labels' => $unitData->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'radar';
    }
}
