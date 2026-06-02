<?php

namespace App\Filament\Resources\StandardIndicators\Tables;

use App\Enums\StandardIndicatorType;
use App\Enums\TargetOperator;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class StandardIndicatorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('qualityStandard.name')
                    ->label('Standar')
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
            ])
            ->filters([
                SelectFilter::make('quality_standard_id')
                    ->label('Standar Mutu')
                    ->relationship('qualityStandard', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('indicator_type')
                    ->label('Jenis')
                    ->options(StandardIndicatorType::class),
                SelectFilter::make('target_operator')
                    ->label('Operator Target')
                    ->options(TargetOperator::class),
                TernaryFilter::make('evidence_required')
                    ->label('Bukti Wajib'),
            ])
            ->defaultSort('code')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
