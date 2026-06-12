<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\IndicatorAchievements\IndicatorAchievementResource;
use App\Filament\Widgets\Concerns\InteractsWithSpmiDashboard;
use App\Models\IndicatorAchievement;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestAchievementsTable extends TableWidget
{
    use InteractsWithSpmiDashboard;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return static::canViewManagementDashboard() || static::canViewUnitDashboard();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Capaian Terbaru')
            ->description('Capaian indikator yang terakhir diperbarui.')
            ->query(fn (): Builder => $this->applyAchievementDashboardScope(
                IndicatorAchievement::query()
                    ->with([
                        'assignment.standardIndicator',
                        'assignment.unit',
                        'submittedBy',
                    ])
                    ->withCount('evidences'),
            ))
            ->columns([
                TextColumn::make('assignment.standardIndicator.code')
                    ->label('Kode')
                    ->searchable(),
                TextColumn::make('assignment.standardIndicator.statement')
                    ->label('Indikator')
                    ->limit(55)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('assignment.unit.name')
                    ->label('Unit')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('submission_status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('achievement_status')
                    ->label('Capaian')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('evidences_count')
                    ->label('Bukti')
                    ->badge()
                    ->alignCenter(),
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
            ->emptyStateHeading('Belum ada capaian');
    }
}
