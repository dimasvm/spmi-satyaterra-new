<?php

namespace App\Filament\Resources\AmiFindings\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AmiFindingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ringkasan Temuan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('finding_number')
                                    ->label('Nomor Temuan')
                                    ->copyable(),
                                TextEntry::make('category')
                                    ->label('Kategori')
                                    ->badge(),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge(),
                                TextEntry::make('audit.amiPeriod.name')
                                    ->label('Periode AMI'),
                                TextEntry::make('audit.auditeeUnit.name')
                                    ->label('Unit Auditee'),
                                TextEntry::make('due_date')
                                    ->label('Batas Waktu')
                                    ->date('d M Y')
                                    ->placeholder('-'),
                                TextEntry::make('standardIndicator.code')
                                    ->label('Kode Indikator')
                                    ->placeholder('-'),
                                TextEntry::make('createdBy.name')
                                    ->label('Dibuat Oleh')
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->columnSpanFull(),
                Section::make('Detail')
                    ->schema([
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->prose()
                            ->columnSpanFull(),
                        TextEntry::make('root_cause')
                            ->label('Akar Masalah')
                            ->placeholder('-')
                            ->prose()
                            ->columnSpanFull(),
                        TextEntry::make('recommendation')
                            ->label('Rekomendasi')
                            ->placeholder('-')
                            ->prose()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
