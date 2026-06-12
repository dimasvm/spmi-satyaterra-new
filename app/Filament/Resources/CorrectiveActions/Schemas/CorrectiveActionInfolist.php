<?php

namespace App\Filament\Resources\CorrectiveActions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CorrectiveActionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Temuan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('finding.finding_number')
                                    ->label('Nomor Temuan')
                                    ->badge(),
                                TextEntry::make('finding.audit.auditeeUnit.name')
                                    ->label('Unit'),
                                TextEntry::make('finding.category')
                                    ->label('Kategori')
                                    ->badge(),
                            ]),
                        TextEntry::make('finding.description')
                            ->label('Deskripsi Temuan')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Tindak Lanjut')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('picUser.name')
                                    ->label('PIC')
                                    ->placeholder('-'),
                                TextEntry::make('target_date')
                                    ->label('Target')
                                    ->date('d M Y')
                                    ->placeholder('-'),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge(),
                                TextEntry::make('overdue')
                                    ->label('Overdue')
                                    ->state(fn ($record): ?string => $record->isOverdue() ? 'Overdue' : null)
                                    ->badge()
                                    ->color('danger')
                                    ->placeholder('-'),
                                TextEntry::make('submitted_at')
                                    ->label('Dikirim')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('-'),
                            ]),
                        TextEntry::make('root_cause_analysis')
                            ->label('Analisis Akar Masalah')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('action_plan')
                            ->label('Rencana Perbaikan')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Review Terakhir')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('latestReview.status')
                                    ->label('Status Review')
                                    ->badge()
                                    ->placeholder('-'),
                                TextEntry::make('latestReview.reviewer.name')
                                    ->label('Reviewer')
                                    ->placeholder('-'),
                                TextEntry::make('latestReview.reviewed_at')
                                    ->label('Direview')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('-'),
                            ]),
                        TextEntry::make('latestReview.notes')
                            ->label('Catatan')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
