<?php

namespace App\Filament\Resources\QualityDocuments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class QualityDocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dokumen Mutu')
                    ->schema([
                        TextEntry::make('title')
                            ->label('Judul')
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold)
                            ->columnSpanFull(),
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('document_type')
                                    ->label('Jenis')
                                    ->badge(),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge(),
                                TextEntry::make('document_number')
                                    ->label('Nomor')
                                    ->placeholder('-'),
                                TextEntry::make('version')
                                    ->label('Versi')
                                    ->badge(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('qualityStandard.name')
                                    ->label('Standar Mutu')
                                    ->placeholder('-'),
                                TextEntry::make('spmiPeriod.name')
                                    ->label('Periode SPMI')
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Persetujuan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('uploadedBy.name')
                                    ->label('Diunggah Oleh')
                                    ->placeholder('-'),
                                TextEntry::make('approvedBy.name')
                                    ->label('Disetujui Oleh')
                                    ->placeholder('-'),
                                TextEntry::make('approved_at')
                                    ->label('Disetujui Pada')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
