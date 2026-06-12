<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AuditorDashboardPlaceholder;
use App\Filament\Widgets\LatestAchievementsTable;
use App\Filament\Widgets\NotAchievedIndicatorsTable;
use App\Filament\Widgets\ReturnedAchievementsTable;
use App\Filament\Widgets\SpmiDashboardStats;
use App\Filament\Widgets\UnitsWithoutSubmissionTable;
use App\Models\SpmiPeriod;
use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'Dashboard SPMI';

    public static function canAccess(): bool
    {
        return (bool) Auth::user()?->can('dashboard.view');
    }

    public function persistsFiltersInSession(): bool
    {
        return false;
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('spmi_period_id')
                    ->label('Periode SPMI')
                    ->columnStart(-1)
                    ->options(fn () => SpmiPeriod::query()->pluck('name', 'id'))
                    ->default(fn (): ?int => SpmiPeriod::active()->value('id'))
                    ->searchable()
                    ->preload(),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            SpmiDashboardStats::class,
            AuditorDashboardPlaceholder::class,
            LatestAchievementsTable::class,
            UnitsWithoutSubmissionTable::class,
            ReturnedAchievementsTable::class,
            NotAchievedIndicatorsTable::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
