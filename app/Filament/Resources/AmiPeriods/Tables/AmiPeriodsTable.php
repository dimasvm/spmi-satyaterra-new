<?php

namespace App\Filament\Resources\AmiPeriods\Tables;

use App\Enums\AmiPeriodStatus;
use App\Filament\Resources\AmiPeriods\AmiPeriodResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AmiPeriodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('spmiPeriod.name')
                    ->label('Periode SPMI')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('audits_count')
                    ->label('Jumlah Unit Audit')
                    ->badge()
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('spmi_period_id')
                    ->label('Periode SPMI')
                    ->relationship('spmiPeriod', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(AmiPeriodStatus::class),
            ])
            ->defaultSort('start_date', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (): bool => AmiPeriodResource::canManageAmi()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => AmiPeriodResource::canManageAmi()),
                ])
                    ->visible(fn (): bool => AmiPeriodResource::canManageAmi()),
            ]);
    }
}
