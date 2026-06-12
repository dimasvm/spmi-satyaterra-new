<?php

namespace App\Filament\Widgets;

use App\Enums\AchievementReviewStatus;
use App\Enums\SubmissionStatus;
use App\Filament\Resources\AchievementReviews\AchievementReviewResource;
use App\Models\AchievementReview;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LpmAchievementReviewQueue extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('indicator-achievements.review');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Tugas Review Capaian Indikator')
            ->description('Capaian yang sudah dikirim unit dan menunggu validasi LPM.')
            ->query(fn (): Builder => AchievementReview::query()
                ->with([
                    'achievement' => fn ($achievementQuery) => $achievementQuery->withCount('evidences'),
                    'achievement.assignment.spmiPeriod',
                    'achievement.assignment.standardIndicator.qualityStandard',
                    'achievement.assignment.unit',
                    'achievement.submittedBy',
                ])
                ->where('status', AchievementReviewStatus::Pending)
                ->whereHas('achievement', fn (Builder $achievementQuery): Builder => $achievementQuery
                    ->where('submission_status', SubmissionStatus::Submitted->value)))
            ->columns([
                TextColumn::make('achievement.assignment.unit.name')
                    ->label('Unit')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('achievement.assignment.standardIndicator.code')
                    ->label('Kode')
                    ->searchable(),
                TextColumn::make('achievement.assignment.standardIndicator.statement')
                    ->label('Indikator')
                    ->description(fn (AchievementReview $record): ?string => $record->achievement?->assignment?->standardIndicator?->qualityStandard?->name)
                    ->searchable()
                    ->wrap()
                    ->limit(80),
                TextColumn::make('achievement.submitted_at')
                    ->label('Dikirim')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('achievement.assignment.due_date')
                    ->label('Deadline')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('achievement.submittedBy.name')
                    ->label('Dikirim Oleh')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('achievement.evidences_count')
                    ->label('Bukti')
                    ->badge()
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                Action::make('review')
                    ->label('Review')
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->button()
                    ->url(fn (AchievementReview $record): string => AchievementReviewResource::getUrl('view', [
                        'record' => $record,
                    ])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
