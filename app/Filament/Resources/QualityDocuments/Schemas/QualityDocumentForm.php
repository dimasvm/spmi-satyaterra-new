<?php

namespace App\Filament\Resources\QualityDocuments\Schemas;

use App\Enums\QualityDocumentStatus;
use App\Enums\QualityDocumentType;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class QualityDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::components());
    }

    public static function components(bool $includeQualityStandard = true, bool $includeSpmiPeriod = true): array
    {
        return [
            Section::make('Informasi Dokumen')
                ->description('Kelompokkan dokumen mutu berdasarkan standar, periode, dan jenis dokumen.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('quality_standard_id')
                                ->label('Standar Mutu')
                                ->relationship('qualityStandard', 'name')
                                ->searchable()
                                ->preload()
                                ->visible($includeQualityStandard),
                            Select::make('spmi_period_id')
                                ->label('Periode SPMI')
                                ->relationship('spmiPeriod', 'name')
                                ->searchable()
                                ->preload()
                                ->visible($includeSpmiPeriod),
                            TextInput::make('title')
                                ->label('Judul')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull(),
                            Select::make('document_type')
                                ->label('Jenis Dokumen')
                                ->options(QualityDocumentType::class)
                                ->default(QualityDocumentType::Other->value)
                                ->required()
                                ->native(false),
                            TextInput::make('document_number')
                                ->label('Nomor Dokumen')
                                ->maxLength(255),
                            TextInput::make('version')
                                ->label('Versi')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required(),
                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    QualityDocumentStatus::Draft->value => QualityDocumentStatus::Draft->getLabel(),
                                    QualityDocumentStatus::Active->value => QualityDocumentStatus::Active->getLabel(),
                                    QualityDocumentStatus::Archived->value => QualityDocumentStatus::Archived->getLabel(),
                                ])
                                ->default(QualityDocumentStatus::Draft->value)
                                ->required()
                                ->native(false)
                                ->live(),
                        ]),
                ])
                ->columnSpanFull(),
            Section::make('File atau Tautan')
                ->description('Isi minimal salah satu saat dokumen akan diaktifkan.')
                ->schema([
                    FileUpload::make('file_path')
                        ->label('File Dokumen')
                        ->directory('quality-documents')
                        ->visibility('private')
                        ->acceptedFileTypes([
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-powerpoint',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ])
                        ->maxSize(10240)
                        ->required(fn (Get $get): bool => $get('status') === QualityDocumentStatus::Active->value && blank($get('external_url')))
                        ->columnSpanFull(),
                    TextInput::make('external_url')
                        ->label('Tautan Eksternal')
                        ->url()
                        ->maxLength(255)
                        ->required(fn (Get $get): bool => $get('status') === QualityDocumentStatus::Active->value && blank($get('file_path')))
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
            Section::make('Persetujuan')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            Hidden::make('uploaded_by')
                                ->default(auth()->id())
                                ->dehydrated(),
                            Select::make('approved_by')
                                ->label('Disetujui Oleh')
                                ->options(fn (): array => User::query()
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all())
                                ->searchable()
                                ->preload(),
                            DateTimePicker::make('approved_at')
                                ->label('Disetujui Pada')
                                ->seconds(false),
                        ]),
                ])
                ->visible(fn (): bool => (bool) auth()->user()?->can('quality-documents.approve'))
                ->columnSpanFull(),
        ];
    }
}
