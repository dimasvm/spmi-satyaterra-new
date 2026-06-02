<?php

namespace App\Filament\Resources\IndicatorUnitAssignments\Schemas;

use App\Enums\IndicatorAssignmentStatus;
use App\Models\IndicatorUnitAssignment;
use App\Models\StandardIndicator;
use App\Models\Unit;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class IndicatorUnitAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Penugasan')
                    ->schema([
                        Select::make('standard_indicator_id')
                            ->label('Indikator')
                            ->relationship('standardIndicator', 'code')
                            ->getOptionLabelFromRecordUsing(fn (StandardIndicator $record): string => "{$record->code} - {$record->statement}")
                            ->required()
                            ->searchable(['code', 'statement'])
                            ->preload()
                            ->columnSpanFull(),
                        Select::make('unit_id')
                            ->label('Unit')
                            ->relationship('unit', 'name')
                            ->getOptionLabelFromRecordUsing(fn (Unit $record): string => "{$record->code} - {$record->name}")
                            ->required()
                            ->searchable(['code', 'name'])
                            ->preload()
                            ->columnSpan(1),
                        Select::make('spmi_period_id')
                            ->label('Periode SPMI')
                            ->relationship('spmiPeriod', 'name')
                            ->required()
                            ->rules(fn (Get $get, ?IndicatorUnitAssignment $record): array => [
                                Rule::unique((new IndicatorUnitAssignment)->getTable(), 'spmi_period_id')
                                    ->where('standard_indicator_id', $get('standard_indicator_id'))
                                    ->where('unit_id', $get('unit_id'))
                                    ->ignore($record),
                            ])
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                        DatePicker::make('due_date')
                            ->label('Batas Waktu')
                            ->columnSpan(1),
                        Select::make('status')
                            ->label('Status')
                            ->options(IndicatorAssignmentStatus::class)
                            ->default(IndicatorAssignmentStatus::Assigned->value)
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
