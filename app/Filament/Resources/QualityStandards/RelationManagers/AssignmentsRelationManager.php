<?php

namespace App\Filament\Resources\QualityStandards\RelationManagers;

use App\Actions\AssignIndicatorsToUnits;
use App\Enums\IndicatorAssignmentStatus;
use App\Filament\Resources\IndicatorUnitAssignments\Schemas\IndicatorUnitAssignmentForm;
use App\Filament\Resources\StandardIndicators\Tables\StandardIndicatorsTable;
use App\Models\IndicatorUnitAssignment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    protected static ?string $title = 'Unit yang Ditugaskan';

    protected static ?string $modelLabel = 'Penugasan Unit';

    protected static ?string $pluralModelLabel = 'Penugasan Unit';

    public function form(Schema $schema): Schema
    {
        return IndicatorUnitAssignmentForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->modifyQueryUsing(fn (Builder $query): Builder => $this->scopeForCurrentUser($query)
                ->with(['unit', 'standardIndicator', 'spmiPeriod'])
                ->withCount('achievements'))
            ->columns([
                TextColumn::make('unit.name')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unit.type')
                    ->label('Tipe Unit')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('standardIndicator.code')
                    ->label('Kode Indikator')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('standardIndicator.statement')
                    ->label('Pernyataan Indikator')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('due_date')
                    ->label('Batas Waktu')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(IndicatorAssignmentStatus::class),
                Filter::make('due_date')
                    ->schema([
                        DatePicker::make('due_from')
                            ->label('Dari Tanggal'),
                        DatePicker::make('due_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['due_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('due_date', '>=', $date))
                        ->when($data['due_until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('due_date', '<=', $date))),
            ])
            ->headerActions([
                Action::make('assignToUnits')
                    ->label('Tugaskan Unit')
                    ->icon(Heroicon::Users)
                    ->schema([
                        Select::make('standard_indicator_id')
                            ->label('Indikator')
                            ->options(fn (): array => $this->getOwnerRecord()
                                ->indicators()
                                ->orderBy('code')
                                ->pluck('statement', 'id')
                                ->all())
                            ->searchable()
                            ->required(),
                        ...StandardIndicatorsTable::assignmentSchema(),
                    ])
                    ->visible(fn (): bool => auth()->user()?->can('create', IndicatorUnitAssignment::class) ?? false)
                    ->action(function (array $data): void {
                        $indicator = $this->getOwnerRecord()
                            ->indicators()
                            ->findOrFail($data['standard_indicator_id']);

                        app(AssignIndicatorsToUnits::class)->handle(
                            indicators: collect([$indicator]),
                            unitIds: $data['unit_ids'],
                            spmiPeriodId: $data['spmi_period_id'],
                            primaryPicUnitId: $data['primary_pic_unit_id'] ?? null,
                            dueDate: $data['due_date'] ?? null,
                            status: $data['status'],
                            priority: $data['priority'],
                            notes: $data['notes'] ?? null,
                            assignedBy: auth()->id(),
                        );
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (IndicatorUnitAssignment $record): bool => auth()->user()?->can('update', $record) ?? false),
                DeleteAction::make()
                    ->visible(fn (IndicatorUnitAssignment $record): bool => ($record->achievements_count ?? $record->achievements()->count()) === 0
                        && (auth()->user()?->can('delete', $record) ?? false)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->authorizeIndividualRecords()
                        ->visible(fn (): bool => auth()->user()?->can('deleteAny', IndicatorUnitAssignment::class) ?? false),
                ]),
            ]);
    }

    private function scopeForCurrentUser(Builder $query): Builder
    {
        $user = auth()->user();

        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->forUser($user);
    }
}
