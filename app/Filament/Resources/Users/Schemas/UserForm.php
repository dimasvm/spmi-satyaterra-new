<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengguna')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->saved(fn (?string $state): bool => filled($state))
                            ->maxLength(255)
                            ->columnSpan(1),
                        Select::make('unit_id')
                            ->label('Unit')
                            ->relationship('unit', 'name')
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                        Select::make('roles')
                            ->label('Role')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->columnSpan(1),
                        DateTimePicker::make('email_verified_at')
                            ->label('Email Terverifikasi Pada')
                            ->seconds(false)
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
