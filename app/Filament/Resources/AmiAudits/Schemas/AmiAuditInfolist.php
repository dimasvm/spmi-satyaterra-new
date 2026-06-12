<?php

namespace App\Filament\Resources\AmiAudits\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AmiAuditInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Audit')
                    ->schema([
                        TextEntry::make('amiPeriod.name')
                            ->label('Periode AMI'),
                        TextEntry::make('amiPeriod.spmiPeriod.name')
                            ->label('Periode SPMI'),
                        TextEntry::make('auditeeUnit.name')
                            ->label('Unit Auditee'),
                        TextEntry::make('scheduled_date')
                            ->label('Jadwal Audit')
                            ->date('d M Y')
                            ->placeholder('-'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),
                        TextEntry::make('auditor_assignments_count')
                            ->label('Jumlah Auditor')
                            ->state(fn ($record): int => $record->auditorAssignments()->count())
                            ->badge(),
                        TextEntry::make('checklists_count')
                            ->label('Jumlah Checklist')
                            ->state(fn ($record): int => $record->checklists()->count())
                            ->badge(),
                        TextEntry::make('findings_count')
                            ->label('Jumlah Temuan')
                            ->state(fn ($record): int => $record->findings()->count())
                            ->badge(),
                        TextEntry::make('finalized_at')
                            ->label('Finalisasi')
                            ->dateTime('d M Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('finalizedBy.name')
                            ->label('Difinalisasi Oleh')
                            ->placeholder('-'),
                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
