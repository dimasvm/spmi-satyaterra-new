<?php

namespace App\Filament\Resources\StandardIndicators\Tables;

use App\Enums\IndicatorAssignmentStatus;
use App\Enums\StandardIndicatorType;
use App\Enums\TargetOperator;
use App\Models\IndicatorUnitAssignment;
use App\Models\SpmiPeriod;
use App\Models\StandardIndicator;
use App\Models\Unit;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class StandardIndicatorsTable
{
    public static function configure(Table $table, bool $isRelationManager = false): Table
    {
        $columns = [
            TextColumn::make('code')
                ->label('Kode')
                ->searchable()
                ->sortable(),
            TextColumn::make('statement')
                ->label('Pernyataan')
                ->limit(70)
                ->searchable()
                ->toggleable(),
            TextColumn::make('indicator_type')
                ->label('Jenis')
                ->badge()
                ->searchable(),
            TextColumn::make('target_value')
                ->label('Target')
                ->numeric()
                ->sortable(),
            TextColumn::make('target_operator')
                ->label('Operator')
                ->badge()
                ->searchable(),
            TextColumn::make('target_unit')
                ->label('Satuan')
                ->searchable(),
            TextColumn::make('weight')
                ->label('Bobot')
                ->numeric()
                ->sortable(),
            IconColumn::make('evidence_required')
                ->label('Bukti Wajib')
                ->boolean(),
            TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];

        if (! $isRelationManager) {
            array_splice($columns, 1, 0, [
                TextColumn::make('qualityStandard.name')
                    ->label('Standar')
                    ->searchable()
                    ->sortable(),
            ]);
        }

        $filters = [
            SelectFilter::make('indicator_type')
                ->label('Jenis')
                ->options(StandardIndicatorType::class),
            SelectFilter::make('target_operator')
                ->label('Operator Target')
                ->options(TargetOperator::class),
            TernaryFilter::make('evidence_required')
                ->label('Bukti Wajib'),
        ];

        if (! $isRelationManager) {
            array_unshift(
                $filters,
                SelectFilter::make('quality_standard_id')
                    ->label('Standar Mutu')
                    ->relationship('qualityStandard', 'name')
                    ->searchable()
                    ->preload(),
            );
        }

        $table = $table
            ->columns($columns)
            ->filters($filters)
            ->defaultSort('code');

        if ($isRelationManager) {
            $table
                ->headerActions([
                    CreateAction::make()
                        ->label('Tambah Indikator'),
                ])
                ->recordActions([
                    static::assignToUnitsAction(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]);
        } else {
            $table->recordActions([
                static::assignToUnitsAction(),
                EditAction::make(),
            ]);
        }

        return $table
            ->toolbarActions([
                BulkActionGroup::make([
                    static::bulkAssignToUnitsAction(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function assignToUnitsAction(): Action
    {
        return Action::make('assignToUnits')
            ->label('Tugaskan')
            ->modalHeading('Tugaskan Indikator ke Unit')
            ->schema(static::assignmentSchema())
            ->action(function (array $data, StandardIndicator $record): void {
                [$createdCount, $skippedCount] = static::createAssignments([$record], $data);

                static::sendAssignmentNotification($createdCount, $skippedCount);
            });
    }

    private static function bulkAssignToUnitsAction(): BulkAction
    {
        return BulkAction::make('assignToUnits')
            ->label('Tugaskan ke Unit')
            ->modalHeading('Tugaskan Indikator Terpilih ke Unit')
            ->schema(static::assignmentSchema())
            ->action(function (array $data, EloquentCollection $records): void {
                [$createdCount, $skippedCount] = static::createAssignments($records, $data);

                static::sendAssignmentNotification($createdCount, $skippedCount);
            })
            ->deselectRecordsAfterCompletion();
    }

    private static function assignmentSchema(): array
    {
        return [
            Select::make('unit_ids')
                ->label('Unit')
                ->options(fn (): array => Unit::query()
                    ->where('is_active', true)
                    ->orderBy('code')
                    ->get()
                    ->mapWithKeys(fn (Unit $unit): array => [
                        $unit->id => "{$unit->code} - {$unit->name}",
                    ])
                    ->all())
                ->multiple()
                ->searchable()
                ->preload()
                ->required(),
            Select::make('spmi_period_id')
                ->label('Periode SPMI')
                ->options(fn (): array => SpmiPeriod::query()
                    ->orderByDesc('start_date')
                    ->orderByDesc('id')
                    ->pluck('name', 'id')
                    ->all())
                ->searchable()
                ->preload()
                ->required(),
            DatePicker::make('due_date')
                ->label('Batas Waktu'),
            Select::make('status')
                ->label('Status')
                ->options(IndicatorAssignmentStatus::class)
                ->default(IndicatorAssignmentStatus::Assigned->value)
                ->required(),
        ];
    }

    /**
     * @param  iterable<int, StandardIndicator>  $indicators
     * @param  array{unit_ids: array<int|string>, spmi_period_id: int|string, due_date?: string|null, status: string}  $data
     * @return array{0: int, 1: int}
     */
    private static function createAssignments(iterable $indicators, array $data): array
    {
        $createdCount = 0;
        $skippedCount = 0;

        foreach ($indicators as $indicator) {
            foreach ($data['unit_ids'] as $unitId) {
                $assignment = IndicatorUnitAssignment::query()->firstOrCreate(
                    [
                        'standard_indicator_id' => $indicator->id,
                        'unit_id' => $unitId,
                        'spmi_period_id' => $data['spmi_period_id'],
                    ],
                    [
                        'due_date' => $data['due_date'] ?? null,
                        'status' => $data['status'],
                    ],
                );

                if ($assignment->wasRecentlyCreated) {
                    $createdCount++;
                } else {
                    $skippedCount++;
                }
            }
        }

        return [$createdCount, $skippedCount];
    }

    private static function sendAssignmentNotification(int $createdCount, int $skippedCount): void
    {
        Notification::make()
            ->title("{$createdCount} penugasan dibuat")
            ->body($skippedCount > 0 ? "{$skippedCount} penugasan sudah ada dan dilewati." : null)
            ->success()
            ->send();
    }
}
