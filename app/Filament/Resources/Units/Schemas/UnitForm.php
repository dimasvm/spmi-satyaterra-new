<?php

namespace App\Filament\Resources\Units\Schemas;

use App\Enums\UnitType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Unit')
                    ->schema([
                        Select::make('parent_id')
                            ->label('Induk Unit')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->columnSpanFull(),
                        TextInput::make('code')
                            ->label('Kode')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        Select::make('type')
                            ->label('Jenis')
                            ->options(UnitType::class)
                            ->searchable()
                            ->columnSpan(1),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
