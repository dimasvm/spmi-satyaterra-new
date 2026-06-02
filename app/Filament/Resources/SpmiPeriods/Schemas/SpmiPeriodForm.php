<?php

namespace App\Filament\Resources\SpmiPeriods\Schemas;

use App\Enums\SpmiPeriodStatus;
use App\Enums\SpmiSemester;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SpmiPeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Periode')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        TextInput::make('academic_year')
                            ->label('Tahun Akademik')
                            ->placeholder('2025/2026')
                            ->maxLength(255)
                            ->columnSpan(1),
                        Select::make('semester')
                            ->label('Semester')
                            ->options(SpmiSemester::class)
                            ->columnSpan(1),
                        Select::make('status')
                            ->label('Status')
                            ->options(SpmiPeriodStatus::class)
                            ->default(SpmiPeriodStatus::Draft->value)
                            ->required()
                            ->columnSpan(1),
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->columnSpan(1),
                        DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->afterOrEqual('start_date')
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
