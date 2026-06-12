<?php

namespace App\Filament\Resources\AchievementReviews\Schemas;

use App\Enums\SubmissionStatus;
use App\Models\AchievementEvidence;
use App\Models\AchievementReview;
use App\Models\IndicatorAchievement;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class AchievementReviewInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Assignment')
                    ->description('Konteks unit, periode, dan status penugasan.')
                    ->icon(Heroicon::OutlinedClipboardDocumentList)
                    ->schema([
                        TextEntry::make('achievement.assignment.unit.name')
                            ->label('Unit')
                            ->icon(Heroicon::OutlinedBuildingOffice)
                            ->placeholder('-'),
                        TextEntry::make('achievement.assignment.spmiPeriod.name')
                            ->label('Periode SPMI')
                            ->icon(Heroicon::OutlinedCalendarDays)
                            ->placeholder('-'),
                        TextEntry::make('achievement.assignment.status')
                            ->label('Status Assignment')
                            ->badge()
                            ->placeholder('-'),
                        TextEntry::make('achievement.assignment.priority')
                            ->label('Prioritas')
                            ->badge()
                            ->placeholder('-'),
                        TextEntry::make('achievement.assignment.due_date')
                            ->label('Batas Waktu')
                            ->date('d/m/Y')
                            ->placeholder('-'),
                        TextEntry::make('achievement.assignment.notes')
                            ->label('Catatan Assignment')
                            ->placeholder('Tidak ada catatan assignment.')
                            ->columnSpanFull(),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
                Section::make('Data Indikator')
                    ->description('Standar, indikator, dan target yang harus dicapai.')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->schema([
                        TextEntry::make('achievement.assignment.standardIndicator.statement')
                            ->hiddenLabel()
                            ->aboveContent(fn (AchievementReview $record): ?string => $record->achievement?->standard_indicator?->code)
                            ->belowContent(fn (AchievementReview $record): ?string => $record->achievement?->standard_indicator?->qualityStandard?->name)
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold)
                            ->columnSpanFull(),
                        TextEntry::make('target')
                            ->label('Target Indikator')
                            ->badge()
                            ->state(fn (AchievementReview $record): string => self::targetSummary($record->achievement)),
                        TextEntry::make('achievement.assignment.standardIndicator.indicator_type')
                            ->label('Tipe Indikator')
                            ->badge()
                            ->placeholder('-'),
                        TextEntry::make('achievement.assignment.standardIndicator.weight')
                            ->label('Bobot')
                            ->numeric()
                            ->placeholder('-'),
                        TextEntry::make('achievement.assignment.standardIndicator.evidence_description')
                            ->label('Kebutuhan Bukti')
                            ->placeholder('Tidak ada deskripsi bukti.')
                            ->columnSpanFull(),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
                Section::make('Realisasi Unit')
                    ->description('Data capaian yang dikirim unit untuk direview.')
                    ->icon(Heroicon::OutlinedArrowTrendingUp)
                    ->schema([
                        TextEntry::make('achievement.realization_value')
                            ->label('Realisasi')
                            ->numeric()
                            ->suffix(fn (AchievementReview $record): ?string => $record->achievement?->standard_indicator?->target_unit)
                            ->placeholder('-'),
                        TextEntry::make('achievement.achievement_status')
                            ->label('Status Capaian')
                            ->badge()
                            ->placeholder('-'),
                        TextEntry::make('achievement.submission_status')
                            ->label('Status Submit')
                            ->badge()
                            ->color(fn ($state): string|array|null => self::submissionStatusColor($state)),
                        TextEntry::make('achievement.submitted_at')
                            ->label('Dikirim Pada')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('-'),
                        TextEntry::make('achievement.submittedBy.name')
                            ->label('Dikirim Oleh')
                            ->icon(Heroicon::OutlinedUserCircle)
                            ->placeholder('-'),
                        TextEntry::make('achievement.realization_text')
                            ->label('Realisasi Naratif')
                            ->placeholder('Tidak ada realisasi naratif.')
                            ->columnSpanFull(),
                        TextEntry::make('achievement.notes')
                            ->label('Catatan Unit')
                            ->color(Color::Gray)
                            ->placeholder('Tidak ada catatan unit.')
                            ->columnSpanFull(),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
                Section::make('Daftar File Bukti')
                    ->description('File atau tautan pendukung capaian.')
                    ->icon(Heroicon::OutlinedPaperClip)
                    ->schema([
                        RepeatableEntry::make('achievement.evidences')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('file_type')
                                    ->label('Jenis')
                                    ->badge(),
                                TextEntry::make('file_name')
                                    ->label('Nama File')
                                    ->placeholder(fn (AchievementEvidence $record): string => $record->external_url ?: '-')
                                    ->copyable(),
                                TextEntry::make('external_url')
                                    ->label('Tautan')
                                    ->placeholder('-')
                                    ->url(fn (?string $state): ?string => $state)
                                    ->openUrlInNewTab()
                                    ->copyable(),
                                TextEntry::make('file_path')
                                    ->label('Path File')
                                    ->placeholder('-')
                                    ->copyable(),
                                TextEntry::make('uploadedBy.name')
                                    ->label('Uploaded by')
                                    ->placeholder('-'),
                                TextEntry::make('created_at')
                                    ->label('Uploaded at')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('description')
                                    ->label('Deskripsi')
                                    ->placeholder('Tidak ada deskripsi.')
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Riwayat Review')
                    ->description('Catatan validasi sebelumnya.')
                    ->icon(Heroicon::OutlinedClock)
                    ->schema([
                        RepeatableEntry::make('achievement.reviews')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('status')
                                    ->label('Status Review')
                                    ->badge(),
                                TextEntry::make('reviewer.name')
                                    ->label('Reviewer')
                                    ->placeholder('-'),
                                TextEntry::make('reviewed_at')
                                    ->label('Reviewed at')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('-'),
                                TextEntry::make('notes')
                                    ->label('Catatan')
                                    ->placeholder('Tidak ada catatan.')
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    private static function targetSummary(?IndicatorAchievement $record): string
    {
        $indicator = $record?->standard_indicator;

        if ($indicator === null) {
            return '-';
        }

        return trim(implode(' ', array_filter([
            $indicator->target_operator?->value,
            (float) $indicator->target_value,
            $indicator->target_unit,
        ]))) ?: '-';
    }

    private static function submissionStatusColor(mixed $state): string|array|null
    {
        $value = $state instanceof SubmissionStatus ? $state->value : (string) $state;

        return match ($value) {
            'draft' => 'gray',
            'submitted' => 'warning',
            'returned' => 'danger',
            'validated' => 'success',
            default => null,
        };
    }
}
