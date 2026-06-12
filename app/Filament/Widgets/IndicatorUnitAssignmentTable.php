<?php

namespace App\Filament\Widgets;

use App\Enums\IndicatorAssignmentStatus;
use App\Enums\SubmissionStatus;
use App\Filament\Resources\IndicatorAchievements\IndicatorAchievementResource;
use App\Models\IndicatorAchievement;
use App\Models\IndicatorUnitAssignment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class IndicatorUnitAssignmentTable extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Pengisian Capaian Indikator Mutu')
            ->columns([
                TextColumn::make('standardIndicator.statement')
                    ->label('Indikator')
                    ->wrap(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('unit.name')
                    ->wrap()
                    ->label('Ke Unit'),
                TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge(),
                TextColumn::make('assigned_at')
                    ->label('Sejak')
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
                Action::make('isi_capaian')
                    ->label(fn (IndicatorUnitAssignment $record): string => $this->hasEditableAchievement($record) ? 'Isi Capaian' : 'Lihat Capaian')
                    ->icon(Heroicon::OutlinedPencil)
                    ->button()
                    ->action(fn (IndicatorUnitAssignment $record): mixed => $this->redirect($this->getAchievementUrl($record))),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    protected function getTableQuery(): Builder|Relation|null
    {
        $query = IndicatorUnitAssignment::query()->with(['standardIndicator', 'unit'])
            ->whereIn('status', [
                IndicatorAssignmentStatus::Assigned,
                IndicatorAssignmentStatus::InProgress,
                IndicatorAssignmentStatus::Returned,
            ]);

        $user = auth()->user();

        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->can('indicator-achievements.review')) {
            return $query;
        }

        if ($user->unit_id === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('unit_id', $user->unit_id);
    }

    private function getAchievementUrl(IndicatorUnitAssignment $assignment): string
    {
        $achievement = $this->resolveAchievement($assignment);

        $page = in_array($achievement->submission_status, [
            SubmissionStatus::Draft,
            SubmissionStatus::Returned,
        ], true) ? 'edit' : 'view';

        return IndicatorAchievementResource::getUrl($page, [
            'record' => $achievement,
        ]);
    }

    private function resolveAchievement(IndicatorUnitAssignment $assignment): IndicatorAchievement
    {
        $achievement = $assignment->achievements()
            ->whereIn('submission_status', [
                SubmissionStatus::Draft,
                SubmissionStatus::Returned,
            ])
            ->latest()
            ->first();

        if ($achievement !== null) {
            return $achievement;
        }

        $achievement = $assignment->achievements()
            ->latest()
            ->first();

        if ($achievement !== null) {
            return $achievement;
        }

        $achievement = $assignment->achievements()->create([
            'submission_status' => SubmissionStatus::Draft,
        ]);

        if ($assignment->status === IndicatorAssignmentStatus::Assigned) {
            $assignment->update([
                'status' => IndicatorAssignmentStatus::InProgress,
            ]);
        }

        return $achievement;
    }

    private function hasEditableAchievement(IndicatorUnitAssignment $assignment): bool
    {
        if (! $assignment->achievements()->exists()) {
            return true;
        }

        return $assignment->achievements()
            ->whereIn('submission_status', [
                SubmissionStatus::Draft,
                SubmissionStatus::Returned,
            ])
            ->exists();
    }
}
