<?php

namespace App\Filament\Widgets;

use App\Enums\SubmissionStatus;
use App\Filament\Widgets\Concerns\InteractsWithSpmiDashboard;
use App\Models\Unit;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class UnitsWithoutSubmissionTable extends TableWidget
{
    use InteractsWithSpmiDashboard;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return static::canViewManagementDashboard() || static::canViewUnitDashboard();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Unit Belum Submit')
            ->description('Unit yang masih memiliki penugasan tanpa capaian terkirim.')
            ->query(fn (): Builder => $this->getUnitsWithoutSubmissionQuery())
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Unit')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge(),
                TextColumn::make('assignments_without_submission_count')
                    ->label('Belum Submit')
                    ->badge()
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ])
            ->defaultSort('assignments_without_submission_count', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Semua unit sudah submit');
    }

    private function getUnitsWithoutSubmissionQuery(): Builder
    {
        $periodId = $this->selectedSpmiPeriodId();
        $user = auth()->user();

        return Unit::query()
            ->active()
            ->when(
                static::canViewUnitDashboard() && $user?->unit_id !== null,
                fn (Builder $query): Builder => $query->whereKey($user->unit_id),
            )
            ->whereHas(
                'indicatorAssignments',
                fn (Builder $query): Builder => $this->applyWithoutSubmissionAssignmentScope($query, $periodId),
            )
            ->withCount([
                'indicatorAssignments as assignments_without_submission_count' => fn (Builder $query): Builder => $this->applyWithoutSubmissionAssignmentScope($query, $periodId),
            ]);
    }

    private function applyWithoutSubmissionAssignmentScope(Builder $query, ?int $periodId): Builder
    {
        return $query
            ->when($periodId, fn (Builder $query): Builder => $query->where('spmi_period_id', $periodId))
            ->whereDoesntHave('achievements', fn (Builder $achievementQuery): Builder => $achievementQuery
                ->whereIn('submission_status', [
                    SubmissionStatus::Submitted->value,
                    SubmissionStatus::Validated->value,
                ]));
    }
}
