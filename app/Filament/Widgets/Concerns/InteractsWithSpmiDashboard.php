<?php

namespace App\Filament\Widgets\Concerns;

use App\Models\SpmiPeriod;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Database\Eloquent\Builder;

trait InteractsWithSpmiDashboard
{
    use InteractsWithPageFilters;

    protected function selectedSpmiPeriodId(): ?int
    {
        $periodId = $this->pageFilters['spmi_period_id'] ?? null;

        if (filled($periodId)) {
            return (int) $periodId;
        }

        return SpmiPeriod::active()->value('id');
    }

    protected function applyAssignmentDashboardScope(Builder $query): Builder
    {
        $periodId = $this->selectedSpmiPeriodId();
        $user = auth()->user();

        return $query
            ->when($periodId, fn (Builder $query): Builder => $query->where('spmi_period_id', $periodId))
            ->when(
                $user?->hasRole('unit_pic') && $user->unit_id !== null,
                fn (Builder $query): Builder => $query->where('unit_id', $user->unit_id),
            );
    }

    protected function applyAchievementDashboardScope(Builder $query): Builder
    {
        return $query->whereHas(
            'assignment',
            fn (Builder $assignmentQuery): Builder => $this->applyAssignmentDashboardScope($assignmentQuery),
        );
    }

    protected static function canViewManagementDashboard(): bool
    {
        return (bool) auth()->user()?->hasAnyRole([
            'super_admin',
            'admin_lpm',
            'pimpinan',
            'viewer',
        ]);
    }

    protected static function canViewUnitDashboard(): bool
    {
        return (bool) auth()->user()?->hasRole('unit_pic');
    }

    protected static function canViewAuditorDashboard(): bool
    {
        return (bool) auth()->user()?->hasRole('auditor');
    }
}
