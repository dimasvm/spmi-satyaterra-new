<?php

namespace App\Filament\Resources\QualityStandards\Schemas;

use App\Enums\QualityStandardStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class QualityStandardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Informasi Standar')
                            ->schema([
                                Select::make('standard_category_id')
                                    ->label('Kategori')
                                    ->relationship('category', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(1),
                                Select::make('spmi_period_id')
                                    ->label('Periode SPMI')
                                    ->relationship('spmiPeriod', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(1),
                                TextInput::make('code')
                                    ->label('Kode')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),
                                TextInput::make('name')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(1),
                                Select::make('status')
                                    ->label('Status')
                                    ->options(QualityStandardStatus::class)
                                    ->default(QualityStandardStatus::Draft->value)
                                    ->required()
                                    ->columnSpan(1),
                                TextInput::make('version')
                                    ->label('Versi')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->columnSpan(1),
                                Textarea::make('description')
                                    ->label('Deskripsi')
                                    ->rows(5)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                        Tab::make('Persetujuan')
                            ->schema([
                                Select::make('approved_by')
                                    ->label('Disetujui Oleh')
                                    ->relationship('approver', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->columnSpan(1),
                                DateTimePicker::make('approved_at')
                                    ->label('Disetujui Pada')
                                    ->seconds(false)
                                    ->columnSpan(1),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }
}
