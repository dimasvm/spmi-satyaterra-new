<?php

namespace App\Filament\Widgets;

use App\Enums\IndicatorAssignmentStatus;
use App\Filament\Resources\IndicatorAchievements\IndicatorAchievementResource;
use App\Models\IndicatorUnitAssignment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class IndicatorUnitAssignmentTable extends TableWidget
{

    public function table(Table $table): Table
    {
        return $table
            ->heading('Pengisian Capaian Indikator Mutu')
            ->columns([
                TextColumn::make('standardIndicator.statement')
                    ->label('Indikator')
                    ->wrap(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('unit.name')
                    ->wrap()
                    ->label('Ke Unit'),
                TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge(),
                TextColumn::make('assigned_at')
                    ->label('Sejak')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                Action::make('isi_capaian')
                    ->label('Isi Capaian')
                    ->icon(Heroicon::OutlinedPencil)
                    ->button()
                    ->url(IndicatorAchievementResource::getUrl('create'))
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    protected function getTableQuery(): Builder|\Illuminate\Database\Eloquent\Relations\Relation|null
    {
        return IndicatorUnitAssignment::query()->with(['standardIndicator', 'unit'])
            ->where('status', IndicatorAssignmentStatus::Assigned);
    }
}
