<?php

namespace App\Filament\Resources\IndicatorUnitAssignments\Tables;

use App\Models\IndicatorUnitAssignment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class IndicatorUnitAssignmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(IndicatorUnitAssignment::query()->with('standardIndicator'))
            ->columns([
                TextColumn::make('standardIndicator.statement')
                    ->wrap()
                    ->label('Indikator')
                    ->description(fn ($record) => $record->standardIndicator->qualityStandard->name),
                TextColumn::make('unit.name')
                    ->label('Ke Unit'),
                TextColumn::make('spmiPeriod.name')
                    ->label('Periode'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('assigned_at')
                    ->label('Mulai Penugasan')
                    ->date('d M Y H:i'),
                TextColumn::make('notes')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->groups([
                Group::make('standardIndicator.statement')
                    ->label('Indikator')
                    ->titlePrefixedWithLabel(false),
                Group::make('unit.name')
                    ->titlePrefixedWithLabel(false)
                    ->label('Unit'),
            ])
            ->defaultGroup('unit.name')
            ->filters([
                SelectFilter::make('standard_indicator_id')
                    ->relationship('standardIndicator', 'statement')
                    ->label('Indikator')
                    ->searchable()
                    ->columnSpan(2)
                    ->preload(),
                SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->relationship('unit', 'name')
                    ->preload()
                    ->searchable(),
                SelectFilter::make('spmi_period_id')
                    ->label('Periode')
                    ->relationship('spmiPeriod', 'name')
                    ->preload()
                    ->native(false),
            ], layout: FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->emptyStateHeading('Belum ada penugasan indikator')
            ->emptyStateDescription('Tugaskan indikator standar ke unit agar unit dapat mengisi capaian.')
            ->emptyStateIcon(Heroicon::UserPlus)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
