<?php

namespace App\Filament\Widgets;

use App\Enums\AchievementStatus;
use App\Filament\Resources\IndicatorAchievements\IndicatorAchievementResource;
use App\Filament\Widgets\Concerns\InteractsWithSpmiDashboard;
use App\Models\IndicatorAchievement;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class NotAchievedIndicatorsTable extends TableWidget
{
    use InteractsWithSpmiDashboard;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return static::canViewManagementDashboard() || static::canViewUnitDashboard();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Indikator Belum Tercapai')
            ->description('Capaian indikator dengan status tidak tercapai.')
            ->query(fn (): Builder => $this->applyAchievementDashboardScope(
                IndicatorAchievement::query()
                    ->with([
                        'assignment.standardIndicator',
                        'assignment.unit',
                    ])
                    ->where('achievement_status', AchievementStatus::NotAchieved->value),
            ))
            ->columns([
                TextColumn::make('assignment.unit.name')
                    ->label('Unit')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('assignment.standardIndicator.code')
                    ->label('Kode')
                    ->searchable(),
                TextColumn::make('assignment.standardIndicator.statement')
                    ->label('Indikator')
                    ->limit(65)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('realization_value')
                    ->label('Realisasi')
                    ->numeric(decimalPlaces: 2)
                    ->placeholder('-'),
                TextColumn::make('updated_at')
                    ->label('Update')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Detail')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (IndicatorAchievement $record): string => IndicatorAchievementResource::getUrl('view', [
                        'record' => $record,
                    ])),
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Tidak ada indikator belum tercapai');
    }
}
