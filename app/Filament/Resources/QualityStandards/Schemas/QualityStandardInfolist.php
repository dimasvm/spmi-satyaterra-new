<?php

namespace App\Filament\Resources\QualityStandards\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QualityStandardInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Standar')
                    ->schema([
                        TextEntry::make('code')
                            ->label('Kode')
                            ->badge(),
                        TextEntry::make('name')
                            ->label('Nama Standar')
                            ->columnSpan(2),
                        TextEntry::make('category.name')
                            ->label('Kategori')
                            ->badge(),
                        TextEntry::make('spmiPeriod.name')
                            ->label('Periode SPMI')
                            ->placeholder('-'),
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),
                        TextEntry::make('version')
                            ->label('Versi')
                            ->badge(),
                        TextEntry::make('approver.name')
                            ->label('Disetujui Oleh')
                            ->placeholder('-'),
                        TextEntry::make('approved_at')
                            ->label('Disetujui Pada')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime('d M Y H:i'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
            ]);
    }
}
