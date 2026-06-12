<?php

namespace App\Filament\Resources\QualityStandards\Tables;

use App\Enums\QualityStandardStatus;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QualityStandardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Standar')
                    ->searchable()
                    ->description(fn ($record) => "$record->code ・ ".$record->category->name)
                    ->sortable(),
                TextColumn::make('indicators_count')
                    ->label('Jumlah Indikator')
                    ->counts('indicators'),
                TextColumn::make('spmiPeriod.name')
                    ->label('Periode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('version')
                    ->label('Versi')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('approved_at')
                    ->label('Disetujui Pada')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('approver.name')
                    ->label('Disetujui Oleh')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
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
                SelectFilter::make('standard_category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('spmi_period_id')
                    ->label('Periode SPMI')
                    ->relationship('spmiPeriod', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->default(QualityStandardStatus::Active)
                    ->options(QualityStandardStatus::class),
            ])
            ->emptyStateHeading('Belum ada standar mutu')
            ->emptyStateDescription('Tambahkan standar mutu dan indikatornya untuk periode SPMI.')
            ->emptyStateIcon(Heroicon::OutlinedDocumentText)
            ->defaultSort('code')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->color('gray')
                        ->icon(Heroicon::Eye)
                        ->hiddenLabel(),
                    EditAction::make()
                        ->color('gray')
                        ->hiddenLabel(),
                ])->buttonGroup(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
