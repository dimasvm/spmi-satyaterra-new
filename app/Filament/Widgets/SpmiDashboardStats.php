<?php

namespace App\Filament\Widgets;

use App\Enums\AchievementStatus;
use App\Enums\SubmissionStatus;
use App\Filament\Widgets\Concerns\InteractsWithSpmiDashboard;
use App\Models\IndicatorAchievement;
use App\Models\IndicatorUnitAssignment;
use App\Models\QualityStandard;
use App\Models\StandardIndicator;
use App\Models\Unit;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class SpmiDashboardStats extends StatsOverviewWidget
{
    use InteractsWithSpmiDashboard;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return static::canViewManagementDashboard() || static::canViewUnitDashboard();
    }

    protected function getStats(): array
    {
        if (static::canViewUnitDashboard()) {
            return $this->getUnitStats();
        }

        return $this->getManagementStats();
    }

    /**
     * @return array<int, Stat>
     */
    private function getManagementStats(): array
    {
        $assignmentTotal = (clone $this->assignmentBaseQuery())->count();
        $assignmentFilled = (clone $this->assignmentBaseQuery())->has('achievements')->count();
        $achievementTotals = $this->achievementStatusTotals();
        $achievementStatusTotals = $this->achievementResultTotals();

        return [
            Stat::make('Total Standar', $this->formatNumber($this->qualityStandardBaseQuery()->count()))
                ->color('gray'),
            Stat::make('Total Indikator', $this->formatNumber($this->standardIndicatorBaseQuery()->count()))
                ->color('gray'),
            Stat::make('Total Unit', $this->formatNumber(Unit::query()->active()->count()))
                ->color('gray'),
            Stat::make('Total Penugasan Indikator', $this->formatNumber($assignmentTotal))
                ->color('info'),
            Stat::make('Capaian Belum Diisi', $this->formatNumber((clone $this->assignmentBaseQuery())->doesntHave('achievements')->count()))
                ->color('warning'),
            Stat::make('Capaian Menunggu Validasi', $this->formatNumber((int) $achievementTotals->submitted_count))
                ->color('info'),
            Stat::make('Capaian Tervalidasi', $this->formatNumber((int) $achievementTotals->validated_count))
                ->color('success'),
            Stat::make('Capaian Belum Tercapai', $this->formatNumber((int) $achievementStatusTotals->not_achieved_count))
                ->color('danger'),
            Stat::make('Progress Pengisian', $this->percentage($assignmentFilled, $assignmentTotal))
                ->description($this->formatNumber($assignmentFilled).' dari '.$this->formatNumber($assignmentTotal).' penugasan sudah memiliki capaian')
                ->color('info'),
            Stat::make('Capaian Tercapai', $this->percentage((int) $achievementStatusTotals->achieved_count, (int) $achievementStatusTotals->measured_count))
                ->description($this->formatNumber((int) $achievementStatusTotals->achieved_count).' dari '.$this->formatNumber((int) $achievementStatusTotals->measured_count).' capaian terukur')
                ->color('success'),
        ];
    }

    /**
     * @return array<int, Stat>
     */
    private function getUnitStats(): array
    {
        $assignmentTotal = (clone $this->assignmentBaseQuery())->count();
        $achievementTotals = $this->achievementStatusTotals();
        $achievementStatusTotals = $this->achievementResultTotals();

        return [
            Stat::make('Indikator Ditugaskan', $this->formatNumber($assignmentTotal))
                ->color('info'),
            Stat::make('Belum Diisi', $this->formatNumber((clone $this->assignmentBaseQuery())->doesntHave('achievements')->count()))
                ->color('warning'),
            Stat::make('Draft', $this->formatNumber((int) $achievementTotals->draft_count))
                ->color('gray'),
            Stat::make('Menunggu Validasi', $this->formatNumber((int) $achievementTotals->submitted_count))
                ->color('info'),
            Stat::make('Dikembalikan', $this->formatNumber((int) $achievementTotals->returned_count))
                ->color('warning'),
            Stat::make('Tervalidasi', $this->formatNumber((int) $achievementTotals->validated_count))
                ->color('success'),
            Stat::make('Tercapai', $this->formatNumber((int) $achievementStatusTotals->achieved_count))
                ->color('success'),
            Stat::make('Belum Tercapai', $this->formatNumber((int) $achievementStatusTotals->not_achieved_count))
                ->color('danger'),
        ];
    }

    private function assignmentBaseQuery(): Builder
    {
        return $this->applyAssignmentDashboardScope(IndicatorUnitAssignment::query());
    }

    private function achievementBaseQuery(): Builder
    {
        return $this->applyAchievementDashboardScope(IndicatorAchievement::query());
    }

    private function qualityStandardBaseQuery(): Builder
    {
        $periodId = $this->selectedSpmiPeriodId();

        return QualityStandard::query()
            ->when($periodId, fn (Builder $query): Builder => $query->where('spmi_period_id', $periodId));
    }

    private function standardIndicatorBaseQuery(): Builder
    {
        $periodId = $this->selectedSpmiPeriodId();

        return StandardIndicator::query()
            ->when($periodId, fn (Builder $query): Builder => $query->whereHas(
                'qualityStandard',
                fn (Builder $qualityStandardQuery): Builder => $qualityStandardQuery->where('spmi_period_id', $periodId),
            ));
    }

    private function achievementStatusTotals(): object
    {
        return $this->achievementBaseQuery()
            ->selectRaw('count(*) as total_count')
            ->selectRaw('sum(case when submission_status = ? then 1 else 0 end) as draft_count', [SubmissionStatus::Draft->value])
            ->selectRaw('sum(case when submission_status = ? then 1 else 0 end) as submitted_count', [SubmissionStatus::Submitted->value])
            ->selectRaw('sum(case when submission_status = ? then 1 else 0 end) as returned_count', [SubmissionStatus::Returned->value])
            ->selectRaw('sum(case when submission_status = ? then 1 else 0 end) as validated_count', [SubmissionStatus::Validated->value])
            ->first();
    }

    private function achievementResultTotals(): object
    {
        return $this->achievementBaseQuery()
            ->selectRaw('count(achievement_status) as measured_count')
            ->selectRaw('sum(case when achievement_status = ? then 1 else 0 end) as achieved_count', [AchievementStatus::Achieved->value])
            ->selectRaw('sum(case when achievement_status = ? then 1 else 0 end) as not_achieved_count', [AchievementStatus::NotAchieved->value])
            ->first();
    }

    private function percentage(int $value, int $total): string
    {
        if ($total === 0) {
            return '0%';
        }

        return number_format(($value / $total) * 100, 1).'%';
    }

    private function formatNumber(int $value): string
    {
        return number_format($value, 0, ',', '.');
    }
}
