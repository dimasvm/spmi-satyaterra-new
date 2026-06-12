<?php

namespace App\Filament\Resources\IndicatorAchievements\Tables;

use App\Enums\AchievementStatus;
use App\Enums\SubmissionStatus;
use App\Models\SpmiPeriod;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IndicatorAchievementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('assignment.standardIndicator.statement')
                    ->label('Indikator')
                    ->description(fn ($record): string => $record->standard_indicator?->qualityStandard?->name ?? '-')
                    ->description(fn ($record) => $record->standard_indicator?->code, 'above')
                    ->searchable()
                    ->width('20%')
                    ->wrap(),
                TextColumn::make('assignment.unit.code')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assignment.spmiPeriod.name')
                    ->label('Periode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('target')
                    ->label('Target')
                    ->state(fn ($record): string => trim(implode(' ', array_filter([
                        $record->assignment?->standardIndicator?->target_operator?->value,
                        $record->assignment?->standardIndicator?->target_value,
                        $record->assignment?->standardIndicator?->target_unit,
                    ]))) ?: '-'),
                TextColumn::make('realization_value')
                    ->label('Realisasi')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('achievement_status')
                    ->label('Status Capaian')
                    ->badge()
                    ->searchable(),
                TextColumn::make('submission_status')
                    ->label('Status Submit')
                    ->badge()
                    ->searchable(),
                TextColumn::make('latestReview.status')
                    ->label('Review Terakhir')
                    ->badge()
                    ->placeholder('Belum direview'),
                TextColumn::make('latestReview.notes')
                    ->label('Catatan Review')
                    ->limit(48)
                    ->tooltip(fn ($record): ?string => $record->latestReview?->notes)
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('submitted_at')
                    ->label('Dikirim Pada')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('submittedBy.name')
                    ->label('Dikirim Oleh')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('spmi_period')
                    ->label('Periode SPMI')
                    ->options(fn (): array => SpmiPeriod::query()
                        ->orderByDesc('start_date')
                        ->pluck('name', 'id')
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereHas('assignment', fn (Builder $assignmentQuery): Builder => $assignmentQuery->where('spmi_period_id', $data['value']))
                        : $query)
                    ->searchable()
                    ->default(fn (): ?int => SpmiPeriod::active()->value('id'))
                    ->preload(),
                SelectFilter::make('achievement_status')
                    ->label('Status Capaian')
                    ->options(AchievementStatus::class),
                SelectFilter::make('submission_status')
                    ->label('Status Submit')
                    ->options(SubmissionStatus::class),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->emptyStateHeading('Belum ada capaian indikator')
            ->emptyStateDescription('Capaian akan tersedia setelah indikator ditugaskan ke unit pada periode SPMI.')
            ->emptyStateIcon(Heroicon::OutlinedArrowTrendingUp)
            ->defaultSort('updated_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->icon(Heroicon::Eye)
                    ->button()
                    ->hiddenLabel()
                    ->color('gray'),
                EditAction::make()
                    ->label('Isi Capaian')
                    ->icon(Heroicon::OutlinedPencil),
            ], position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
