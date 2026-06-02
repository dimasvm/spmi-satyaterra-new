<?php

namespace App\Filament\Resources\StandardIndicators\Schemas;

use App\Enums\StandardIndicatorType;
use App\Enums\TargetOperator;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;

class StandardIndicatorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::components());
    }

    public static function components(bool $includeQualityStandard = true): array
    {
        $informationSchema = [
            TextInput::make('code')
                ->label('Kode')
                ->required()
                ->maxLength(255)
                ->columnSpan(1),
            Select::make('indicator_type')
                ->label('Jenis Indikator')
                ->options(StandardIndicatorType::class)
                ->default(StandardIndicatorType::Percentage->value)
                ->required()
                ->columnSpan(1),
            Textarea::make('statement')
                ->label('Pernyataan')
                ->required()
                ->rows(4)
                ->columnSpanFull(),
        ];

        if ($includeQualityStandard) {
            array_unshift(
                $informationSchema,
                Select::make('quality_standard_id')
                    ->label('Standar Mutu')
                    ->relationship('qualityStandard', 'name')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
            );
        }

        return [
            Fieldset::make('Informasi Indikator')
                ->schema($informationSchema)
                ->columns(2)
                ->columnSpanFull(),
            Fieldset::make('Target')
                ->schema([
                    Select::make('target_operator')
                        ->label('Operator Target')
                        ->options(TargetOperator::class)
                        ->columnSpan(1),
                    TextInput::make('target_value')
                        ->label('Nilai Target')
                        ->numeric()
                        ->columnSpan(1),
                    TextInput::make('target_unit')
                        ->label('Satuan Target')
                        ->maxLength(255)
                        ->columnSpan(1),
                    TextInput::make('weight')
                        ->label('Bobot')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->columnSpan(1),
                ])
                ->columns(3)
                ->columnSpanFull(),
            Fieldset::make('Bukti')
                ->schema([
                    Toggle::make('evidence_required')
                        ->label('Bukti Wajib')
                        ->default(true)
                        ->required()
                        ->columnSpan(1),
                    Textarea::make('evidence_description')
                        ->label('Deskripsi Bukti')
                        ->rows(4)
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }
}
