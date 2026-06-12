<?php

namespace App\Filament\Widgets;

use App\Enums\SubmissionStatus;
use App\Filament\Resources\IndicatorAchievements\IndicatorAchievementResource;
use App\Filament\Widgets\Concerns\InteractsWithSpmiDashboard;
use App\Models\IndicatorAchievement;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ReturnedAchievementsTable extends TableWidget
{
    use InteractsWithSpmiDashboard;

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return static::canViewManagementDashboard() || static::canViewUnitDashboard();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Capaian Dikembalikan')
            ->description('Capaian yang perlu diperbaiki oleh unit.')
            ->query(fn (): Builder => $this->applyAchievementDashboardScope(
                IndicatorAchievement::query()
                    ->with([
                        'assignment.standardIndicator',
                        'assignment.unit',
                        'latestReview.reviewer',
                    ])
                    ->where('submission_status', SubmissionStatus::Returned->value),
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
                    ->limit(60)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('latestReview.notes')
                    ->label('Catatan')
                    ->limit(70)
                    ->wrap()
                    ->placeholder('-'),
                TextColumn::make('latestReview.reviewed_at')
                    ->label('Dikembalikan')
                    ->dateTime('d/m/Y H:i')
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
            ->emptyStateHeading('Tidak ada capaian dikembalikan');
    }
}
