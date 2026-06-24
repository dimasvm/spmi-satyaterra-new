<?php

namespace App\Filament\Resources\QualityStandards\Tables;

use App\Enums\QualityStandardStatus;
use App\Enums\UnitType;
use App\Models\StandardCategory;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QualityStandardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Standar')
                    ->searchable()
                    ->description(fn ($record) => "$record->code ・ ".$record->category?->qualified_name)
                    ->sortable(),
                TextColumn::make('scope_type')
                    ->label('Level')
                    ->badge()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('statements_count')
                    ->label('Jumlah Pernyataan')
                    ->counts('statements'),
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
                    ->label('Kategori/Subkategori')
                    ->options(fn (): array => StandardCategory::hierarchicalOptions())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->forStandardCategory($data['value'])
                        : $query)
                    ->searchable()
                    ->preload(),
                SelectFilter::make('scope_type')
                    ->label('Level/Cakupan')
                    ->options([
                        UnitType::University->value => UnitType::University->getLabel(),
                        UnitType::Faculty->value => UnitType::Faculty->getLabel(),
                        UnitType::StudyProgram->value => UnitType::StudyProgram->getLabel(),
                        UnitType::Institution->value => UnitType::Institution->getLabel(),
                        UnitType::Bureau->value => UnitType::Bureau->getLabel(),
                    ]),
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
