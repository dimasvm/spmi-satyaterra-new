<?php

namespace App\Filament\Resources\IndicatorAchievements\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IndicatorAchievementInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konteks Indikator')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('assignment.spmiPeriod.name')
                                    ->label('Periode SPMI'),
                                TextEntry::make('assignment.unit.name')
                                    ->label('Unit'),
                                TextEntry::make('assignment.standardIndicator.code')
                                    ->label('Kode Indikator')
                                    ->copyable(),
                                TextEntry::make('assignment.standardIndicator.qualityStandard.name')
                                    ->label('Standar Mutu')
                                    ->columnSpan(2),
                                TextEntry::make('assignment.priority')
                                    ->label('Prioritas')
                                    ->badge()
                                    ->placeholder('-'),
                            ]),
                        TextEntry::make('assignment.standardIndicator.statement')
                            ->label('Pernyataan Indikator')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Realisasi Capaian')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('target')
                                    ->label('Target')
                                    ->state(fn ($record): string => trim(implode(' ', array_filter([
                                        $record->standard_indicator?->target_operator?->value,
                                        $record->standard_indicator?->target_value,
                                        $record->standard_indicator?->target_unit,
                                    ]))) ?: '-'),
                                TextEntry::make('realization_value')
                                    ->label('Realisasi')
                                    ->numeric()
                                    ->suffix(fn ($record): ?string => $record->standard_indicator?->target_unit)
                                    ->placeholder('-'),
                                TextEntry::make('achievement_status')
                                    ->label('Status Capaian')
                                    ->badge()
                                    ->placeholder('-'),
                            ]),
                        TextEntry::make('realization_text')
                            ->label('Realisasi Naratif')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('notes')
                            ->label('Catatan Unit')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Validasi')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('submission_status')
                                    ->label('Status Submit')
                                    ->badge(),
                                TextEntry::make('submitted_at')
                                    ->label('Dikirim Pada')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('submittedBy.name')
                                    ->label('Dikirim Oleh')
                                    ->placeholder('-'),
                                TextEntry::make('latestReview.status')
                                    ->label('Review Terakhir')
                                    ->badge()
                                    ->placeholder('-'),
                                TextEntry::make('latestReview.reviewer.name')
                                    ->label('Reviewer')
                                    ->placeholder('-'),
                                TextEntry::make('latestReview.reviewed_at')
                                    ->label('Direview Pada')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('-'),
                            ]),
                        TextEntry::make('latestReview.notes')
                            ->label('Catatan Review')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Audit Data')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('updated_at')
                                    ->label('Diperbarui Pada')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
