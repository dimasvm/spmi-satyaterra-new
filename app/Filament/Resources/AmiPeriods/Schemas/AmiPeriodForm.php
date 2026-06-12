<?php

namespace App\Filament\Resources\AmiPeriods\Schemas;

use App\Enums\AmiPeriodStatus;
use App\Models\SpmiPeriod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AmiPeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Periode Audit Mutu Internal')
                    ->schema([
                        Select::make('spmi_period_id')
                            ->label('Periode SPMI')
                            ->relationship('spmiPeriod', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn (): ?int => SpmiPeriod::active()->value('id'))
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Periode AMI')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai'),
                        DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->afterOrEqual('start_date'),
                        Select::make('status')
                            ->label('Status')
                            ->options(AmiPeriodStatus::class)
                            ->default(AmiPeriodStatus::Draft->value)
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
