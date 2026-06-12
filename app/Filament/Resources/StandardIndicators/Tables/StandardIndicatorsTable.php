<?php

namespace App\Filament\Resources\StandardIndicators\Tables;

use App\Actions\AssignIndicatorsToUnits;
use App\Enums\IndicatorAssignmentPriority;
use App\Enums\IndicatorAssignmentStatus;
use App\Enums\StandardIndicatorType;
use App\Enums\TargetOperator;
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
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class StandardIndicatorsTable
{
    public static function configure(Table $table, bool $isRelationManager = false): Table
    {
        $columns = [
            TextColumn::make('statement')
                ->label('Pernyataan')
                ->description(fn ($record) => $record->code, 'above')
                ->description(fn ($record) => $record->qualityStandard->name)
                ->wrap()
                ->searchable(),
            TextColumn::make('target_value')
                ->label('Target')
                ->badge()
                ->formatStateUsing(fn ($record, $state) => $record->target_operator->value.' '.(float) $state.' '.$record->target_unit),
            TextColumn::make('weight')
                ->label('Bobot')
                ->numeric()
                ->sortable(),
            IconColumn::make('evidence_required')
                ->label('Bukti Wajib')
                ->boolean(),
            TextColumn::make('assigned_units')
                ->state(fn (StandardIndicator $record): array => $record->assignments
                    ->map(fn ($assignment): ?string => $assignment->unit?->name)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all())
                ->bulleted()
                ->limitList(2)
                ->expandableLimitedList()
                ->label('Unit Terkait'),
            TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];

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
            ->groups([
                Group::make('qualityStandard.name')
                    ->label('Standar Mutu')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false),
            ])
            ->emptyStateHeading('Belum ada indikator standar')
            ->emptyStateDescription('Tambahkan indikator agar dapat ditugaskan ke unit dan diukur capaiannya.')
            ->emptyStateIcon(Heroicon::OutlinedClipboardDocumentList)
            ->defaultGroup('qualityStandard.name')
            ->paginationMode(PaginationMode::Default)
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

    public static function assignToUnitsAction(): Action
    {
        return Action::make('assignToUnits')
            ->label('Tugaskan')
            ->button()
            ->icon(Heroicon::Users)
            ->modalHeading('Tugaskan Indikator ke Unit')
            ->schema(static::assignmentSchema())
            ->action(function (array $data, StandardIndicator $record): void {
                $result = app(AssignIndicatorsToUnits::class)->handle(
                    indicators: [$record],
                    unitIds: $data['unit_ids'],
                    spmiPeriodId: $data['spmi_period_id'],
                    primaryPicUnitId: $data['primary_pic_unit_id'] ?? null,
                    dueDate: $data['due_date'] ?? null,
                    status: $data['status'],
                    priority: $data['priority'],
                    notes: $data['notes'] ?? null,
                    assignedBy: auth()->id(),
                );

                static::sendAssignmentNotification($result['created'], $result['skipped']);
            });
    }

    private static function bulkAssignToUnitsAction(): BulkAction
    {
        return BulkAction::make('assignToUnits')
            ->label('Tugaskan ke Unit')
            ->modalHeading('Tugaskan Indikator Terpilih ke Unit')
            ->schema(static::assignmentSchema())
            ->icon(Heroicon::Users)
            ->action(function (array $data, EloquentCollection $records): void {
                $result = app(AssignIndicatorsToUnits::class)->handle(
                    indicators: $records,
                    unitIds: $data['unit_ids'],
                    spmiPeriodId: $data['spmi_period_id'],
                    primaryPicUnitId: $data['primary_pic_unit_id'] ?? null,
                    dueDate: $data['due_date'] ?? null,
                    status: $data['status'],
                    priority: $data['priority'],
                    notes: $data['notes'] ?? null,
                    assignedBy: auth()->id(),
                );

                static::sendAssignmentNotification($result['created'], $result['skipped']);
            })
            ->deselectRecordsAfterCompletion();
    }

    public static function assignmentSchema(): array
    {
        return [
            Grid::make()
                ->schema([
                    Select::make('spmi_period_id')
                        ->label('Periode SPMI')
                        ->options(fn () => SpmiPeriod::query()->pluck('name', 'id'))
                        ->searchable()
                        ->default(fn (): ?int => SpmiPeriod::active()->value('id'))
                        ->preload()
                        ->required(),
                    DatePicker::make('due_date')
                        ->label('Batas Waktu Pengisian'),
                ])
                ->columnSpanFull(),
            Grid::make(3)
                ->schema([
                    Select::make('status')
                        ->label('Status')
                        ->options(IndicatorAssignmentStatus::class)
                        ->default(IndicatorAssignmentStatus::Assigned->value)
                        ->required(),
                    Select::make('priority')
                        ->label('Prioritas')
                        ->options(IndicatorAssignmentPriority::class)
                        ->default(IndicatorAssignmentPriority::Normal->value)
                        ->required(),
                ])
                ->columnSpanFull(),
            CheckboxList::make('unit_ids')
                ->label('Tugaskan Ke Unit')
                ->options(Unit::query()->pluck('name', 'id'))
                ->searchable()
                ->required()
                ->bulkToggleable()
                ->columns(3),
            Textarea::make('notes')
                ->label('Catatan')
                ->rows(3),
        ];
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
