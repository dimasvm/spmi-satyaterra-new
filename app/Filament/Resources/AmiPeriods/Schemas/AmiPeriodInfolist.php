<?php

namespace App\Filament\Resources\AmiPeriods\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AmiPeriodInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Periode AMI')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama Periode'),
                        TextEntry::make('spmiPeriod.name')
                            ->label('Periode SPMI'),
                        TextEntry::make('start_date')
                            ->label('Tanggal Mulai')
                            ->date('d M Y')
                            ->placeholder('-'),
                        TextEntry::make('end_date')
                            ->label('Tanggal Selesai')
                            ->date('d M Y')
                            ->placeholder('-'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),
                        TextEntry::make('audits_count')
                            ->label('Jumlah Unit Audit')
                            ->state(fn ($record): int => $record->audits()->count())
                            ->badge(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
