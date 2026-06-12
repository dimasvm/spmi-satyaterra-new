<?php

namespace App\Filament\Resources\AchievementReviews\Tables;

use App\Enums\AchievementReviewStatus;
use App\Enums\AchievementStatus;
use App\Enums\IndicatorAssignmentStatus;
use App\Enums\SubmissionStatus;
use App\Models\AchievementReview;
use App\Models\IndicatorAchievement;
use App\Models\QualityStandard;
use App\Models\SpmiPeriod;
use App\Models\Unit;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AchievementReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('achievement.assignment.unit.name')
                    ->label('Unit')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('achievement.assignment.standardIndicator.qualityStandard.name')
                    ->label('Standar')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('achievement.assignment.standardIndicator.statement')
                    ->label('Indikator')
                    ->description(fn (AchievementReview $record): ?string => $record->achievement?->standard_indicator?->code, 'above')
                    ->searchable()
                    ->wrap()
                    ->width('24%'),
                TextColumn::make('target')
                    ->label('Target')
                    ->state(fn (AchievementReview $record): string => self::targetSummary($record->achievement)),
                TextColumn::make('achievement.realization_value')
                    ->label('Realisasi')
                    ->numeric()
                    ->suffix(fn (AchievementReview $record): ?string => $record->achievement?->standard_indicator?->target_unit)
                    ->sortable(),
                TextColumn::make('achievement.achievement_status')
                    ->label('Status Capaian')
                    ->badge()
                    ->searchable(),
                TextColumn::make('achievement.submission_status')
                    ->label('Status Submit')
                    ->badge()
                    ->color(fn (SubmissionStatus $state): string|array|null => self::submissionStatusColor($state))
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status Review')
                    ->badge()
                    ->searchable(),
                TextColumn::make('achievement.submitted_at')
                    ->label('Dikirim Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('achievement.submittedBy.name')
                    ->label('Dikirim Oleh')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('achievement.evidences_count')
                    ->label('Jumlah Bukti')
                    ->badge()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('spmi_period_id')
                    ->label('Periode SPMI')
                    ->options(fn (): array => SpmiPeriod::query()
                        ->orderByDesc('start_date')
                        ->pluck('name', 'id')
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('achievement.assignment', fn (Builder $assignmentQuery): Builder => $assignmentQuery->where('spmi_period_id', $data['value']))
                        : $query)
                    ->searchable()
                    ->preload(),
                SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->options(fn (): array => Unit::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('achievement.assignment', fn (Builder $assignmentQuery): Builder => $assignmentQuery->where('unit_id', $data['value']))
                        : $query)
                    ->searchable()
                    ->preload(),
                SelectFilter::make('quality_standard_id')
                    ->label('Standar')
                    ->options(fn (): array => QualityStandard::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('achievement.assignment.standardIndicator', fn (Builder $indicatorQuery): Builder => $indicatorQuery->where('quality_standard_id', $data['value']))
                        : $query)
                    ->searchable()
                    ->preload(),
                SelectFilter::make('submission_status')
                    ->label('Status Submit')
                    ->options(SubmissionStatus::class)
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('achievement', fn (Builder $achievementQuery): Builder => $achievementQuery->where('submission_status', $data['value']))
                        : $query),
                SelectFilter::make('achievement_status')
                    ->label('Status Capaian')
                    ->options(AchievementStatus::class)
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('achievement', fn (Builder $achievementQuery): Builder => $achievementQuery->where('achievement_status', $data['value']))
                        : $query),
            ], layout: FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->emptyStateHeading('Belum ada capaian untuk divalidasi')
            ->emptyStateDescription('Capaian unit yang sudah disubmit akan masuk ke daftar validasi LPM.')
            ->emptyStateIcon(Heroicon::OutlinedShieldCheck)
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label('Detail')
                    ->icon(Heroicon::Eye)
                    ->color('gray'),
                self::reviewAction(
                    name: 'validateAchievement',
                    label: 'Validasi',
                    reviewStatus: AchievementReviewStatus::Validated,
                    submissionStatus: SubmissionStatus::Validated,
                    assignmentStatus: IndicatorAssignmentStatus::Validated,
                    color: 'success',
                    icon: Heroicon::OutlinedCheckCircle,
                    notesRequired: false,
                ),
                self::reviewAction(
                    name: 'returnAchievement',
                    label: 'Kembalikan',
                    reviewStatus: AchievementReviewStatus::Returned,
                    submissionStatus: SubmissionStatus::Returned,
                    assignmentStatus: IndicatorAssignmentStatus::Returned,
                    color: 'warning',
                    icon: Heroicon::OutlinedArrowUturnLeft,
                    notesRequired: true,
                ),
                self::reviewAction(
                    name: 'rejectAchievement',
                    label: 'Tolak',
                    reviewStatus: AchievementReviewStatus::Rejected,
                    submissionStatus: SubmissionStatus::Returned,
                    assignmentStatus: IndicatorAssignmentStatus::Returned,
                    color: 'danger',
                    icon: Heroicon::OutlinedXCircle,
                    notesRequired: true,
                ),
            ], position: RecordActionsPosition::BeforeColumns);
    }

    public static function reviewAction(
        string $name,
        string $label,
        AchievementReviewStatus $reviewStatus,
        SubmissionStatus $submissionStatus,
        IndicatorAssignmentStatus $assignmentStatus,
        string $color,
        Heroicon $icon,
        bool $notesRequired,
    ): Action {
        return Action::make($name)
            ->label($label)
            ->icon($icon)
            ->color($color)
            ->schema([
                Textarea::make('notes')
                    ->label('Catatan Review')
                    ->required($notesRequired)
                    ->rows(4)
                    ->maxLength(1000),
            ])
            ->requiresConfirmation()
            ->modalHeading($label.' Capaian')
            ->modalSubmitActionLabel($label)
            ->visible(fn (AchievementReview $record): bool => self::canReview($record))
            ->action(function (AchievementReview $record, array $data) use ($reviewStatus, $submissionStatus, $assignmentStatus, $label): void {
                DB::transaction(function () use ($record, $data, $reviewStatus, $submissionStatus, $assignmentStatus): void {
                    self::recordReview($record, $reviewStatus, $data['notes'] ?? null);

                    $record->achievement?->update([
                        'submission_status' => $submissionStatus,
                    ]);

                    $record->achievement?->assignment()->update([
                        'status' => $assignmentStatus,
                    ]);
                });

                Notification::make()
                    ->success()
                    ->title("Capaian berhasil {$label}.")
                    ->send();
            });
    }

    private static function recordReview(
        AchievementReview $record,
        AchievementReviewStatus $reviewStatus,
        ?string $notes,
    ): void {
        $reviewData = [
            'reviewer_id' => auth()->id(),
            'status' => $reviewStatus,
            'notes' => $notes,
            'reviewed_at' => now(),
        ];

        if ($record->status === AchievementReviewStatus::Pending) {
            $record->update($reviewData);

            return;
        }

        $achievement = $record->achievement;

        if ($achievement === null) {
            return;
        }

        $pendingReview = $achievement->reviews()
            ->where('status', AchievementReviewStatus::Pending)
            ->oldest()
            ->lockForUpdate()
            ->first();

        if ($pendingReview !== null) {
            $pendingReview->update($reviewData);

            return;
        }

        $achievement->reviews()->create($reviewData);
    }

    private static function canReview(AchievementReview $record): bool
    {
        return (bool) auth()->user()?->can('indicator-achievements.review')
            && $record->status === AchievementReviewStatus::Pending
            && $record->achievement?->submission_status === SubmissionStatus::Submitted;
    }

    private static function targetSummary(?IndicatorAchievement $record): string
    {
        $indicator = $record?->standard_indicator;

        if ($indicator === null) {
            return '-';
        }

        return trim(implode(' ', array_filter([
            $indicator->target_operator?->value,
            (float) $indicator->target_value,
            $indicator->target_unit,
        ]))) ?: '-';
    }

    private static function submissionStatusColor(SubmissionStatus $state): string|array|null
    {
        return match ($state) {
            SubmissionStatus::Draft => 'gray',
            SubmissionStatus::Submitted => 'warning',
            SubmissionStatus::Returned => 'danger',
            SubmissionStatus::Validated => 'success',
        };
    }
}
