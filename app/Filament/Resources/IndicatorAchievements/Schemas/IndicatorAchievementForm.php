<?php

namespace App\Filament\Resources\IndicatorAchievements\Schemas;

use App\Enums\AchievementStatus;
use App\Enums\EvidenceFileType;
use App\Models\IndicatorAchievement;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class IndicatorAchievementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('assignment_id')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->aboveContent(fn ($record) => $record->standard_indicator->code)
                    ->formatStateUsing(fn ($record): string => $record->standard_indicator->statement)
                    ->size(TextSize::Large)
                    ->weight(FontWeight::Bold)
                    ->belowContent(fn ($record) => $record->standard_indicator->qualityStandard?->name),
                Grid::make(4)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('target')
                            ->label('Target')
                            ->badge()
                            ->state(fn ($record) => self::targetSummary($record)),
                        TextEntry::make('assignment.priority')
                            ->label('Prioritas')
                            ->badge(),
                        TextEntry::make('assignment.notes')
                            ->label('Catatan')
                            ->placeholder('Tidak ada'),
                    ]),
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Realisasi Capaian')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('realization_value')
                                            ->label('Nilai Realisasi')
                                            ->numeric()
                                            ->placeholder(fn ($record) => 'Misal: '.(float) $record->standard_indicator?->target_value)
                                            ->live()
                                            ->afterStateUpdated(function ($state, callable $set, $record): void {
                                                $targetValue = (float) ($record->standard_indicator?->target_value ?? 0);
                                                $realizationValue = (float) ($state ?? 0);

                                                $set('achievement_status', $realizationValue >= $targetValue ? AchievementStatus::Achieved : AchievementStatus::NotAchieved);
                                            })
                                            ->suffix(fn ($record) => $record->standard_indicator?->target_unit)
                                            ->minValue(0),
                                        Select::make('achievement_status')
                                            ->label('Status Capaian')
                                            ->options(AchievementStatus::class)
                                            ->native(false),
                                    ]),
                                Textarea::make('realization_text')
                                    ->label('Realisasi Naratif')
                                    ->rows(5)
                                    ->columnSpanFull(),
                                Textarea::make('notes')
                                    ->label('Catatan')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                        Tab::make('Bukti Capaian')
                            ->schema([
                                Repeater::make('evidences')
                                    ->label('Bukti')
                                    ->relationship()
                                    ->schema([
                                        Select::make('file_type')
                                            ->label('Jenis Bukti')
                                            ->options(EvidenceFileType::class)
                                            ->default(EvidenceFileType::Link)
                                            ->required()
                                            ->native(false)
                                            ->live(),
                                        TextInput::make('external_url')
                                            ->label('Tautan Bukti')
                                            ->url()
                                            ->maxLength(255)
                                            ->visible(fn (Get $get): bool => self::isLinkEvidenceType($get('file_type')))
                                            ->required(fn (Get $get): bool => self::isLinkEvidenceType($get('file_type'))),
                                        FileUpload::make('file_path')
                                            ->label('File Bukti')
                                            ->directory('achievement-evidences')
                                            ->visibility('private')
                                            ->acceptedFileTypes([
                                                'application/pdf',
                                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                                'image/jpeg',
                                                'image/png',
                                                'image/webp',
                                            ])
                                            ->maxSize(5120)
                                            ->storeFileNamesIn('file_name')
                                            ->visible(fn (Get $get): bool => ! self::isLinkEvidenceType($get('file_type'))),
                                        Textarea::make('description')
                                            ->label('Deskripsi')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2)
                                    ->addActionLabel('Tambah Bukti')
                                    ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => self::prepareEvidenceData($data))
                                    ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => self::prepareEvidenceData($data))
                                    ->columnSpanFull(),
                            ]),
                    ]),
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

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function prepareEvidenceData(array $data): array
    {
        $data['uploaded_by'] = auth()->id();

        if (self::isLinkEvidenceType($data['file_type'] ?? null)) {
            $data['file_path'] = null;
            $data['file_name'] = null;

            return $data;
        }

        $data['external_url'] = null;

        return $data;
    }

    private static function isLinkEvidenceType(mixed $fileType): bool
    {
        if ($fileType instanceof EvidenceFileType) {
            return $fileType === EvidenceFileType::Link;
        }

        if ($fileType === null || $fileType === '') {
            return true;
        }

        return EvidenceFileType::tryFrom((string) $fileType) === EvidenceFileType::Link;
    }
}
