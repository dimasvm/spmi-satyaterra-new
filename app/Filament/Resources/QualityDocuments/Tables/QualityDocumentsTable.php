<?php

namespace App\Filament\Resources\QualityDocuments\Tables;

use App\Enums\QualityDocumentStatus;
use App\Enums\QualityDocumentType;
use App\Filament\Resources\QualityDocuments\QualityDocumentResource;
use App\Models\QualityDocument;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;

class QualityDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('document_type')
                    ->label('Jenis')
                    ->badge()
                    ->sortable(),
                TextColumn::make('document_number')
                    ->label('Nomor')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('version')
                    ->label('Versi')
                    ->badge()
                    ->sortable(),
                TextColumn::make('qualityStandard.name')
                    ->label('Standar')
                    ->placeholder('-')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('spmiPeriod.name')
                    ->label('Periode')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('uploadedBy.name')
                    ->label('Diunggah Oleh')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('approved_at')
                    ->label('Disetujui')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('document_type')
                    ->label('Jenis Dokumen')
                    ->options(QualityDocumentType::class),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        QualityDocumentStatus::Draft->value => QualityDocumentStatus::Draft->getLabel(),
                        QualityDocumentStatus::Active->value => QualityDocumentStatus::Active->getLabel(),
                        QualityDocumentStatus::Archived->value => QualityDocumentStatus::Archived->getLabel(),
                    ]),
                SelectFilter::make('spmi_period_id')
                    ->label('Periode SPMI')
                    ->relationship('spmiPeriod', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('quality_standard_id')
                    ->label('Standar Mutu')
                    ->relationship('qualityStandard', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->emptyStateHeading('Belum ada dokumen mutu')
            ->emptyStateDescription('Tambahkan dokumen mutu seperti kebijakan, SOP, formulir, pedoman, atau laporan.')
            ->emptyStateIcon(Heroicon::OutlinedFolderOpen)
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ActionGroup::make([
                    static::openDocumentAction(),
                    ViewAction::make()
                        ->color('gray')
                        ->icon(Heroicon::Eye)
                        ->hiddenLabel(),
                    EditAction::make()
                        ->color('gray')
                        ->hiddenLabel()
                        ->visible(fn (QualityDocument $record): bool => QualityDocumentResource::canEdit($record)),
                    static::approveAction(),
                    static::archiveAction(),
                ])->buttonGroup(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => QualityDocumentResource::canDeleteAny()),
                ])
                    ->visible(fn (): bool => QualityDocumentResource::canDeleteAny()),
            ]);
    }

    public static function openDocumentAction(): Action
    {
        return Action::make('openDocument')
            ->label('Buka')
            ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
            ->color('gray')
            ->hiddenLabel()
            ->url(fn (QualityDocument $record): ?string => static::documentUrl($record))
            ->openUrlInNewTab()
            ->visible(fn (QualityDocument $record): bool => filled($record->external_url) || filled($record->file_path));
    }

    public static function approveAction(): Action
    {
        return Action::make('approve')
            ->label('Setujui')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn (QualityDocument $record): bool => auth()->user()?->can('approve', $record)
                && $record->status !== QualityDocumentStatus::Active)
            ->action(function (QualityDocument $record): void {
                if (blank($record->file_path) && blank($record->external_url)) {
                    Notification::make()
                        ->danger()
                        ->title('Dokumen belum memiliki file atau tautan.')
                        ->send();

                    return;
                }

                $record->update([
                    'status' => QualityDocumentStatus::Active,
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                Notification::make()
                    ->success()
                    ->title('Dokumen mutu disetujui dan diaktifkan.')
                    ->send();
            });
    }

    public static function archiveAction(): Action
    {
        return Action::make('archive')
            ->label('Arsipkan')
            ->icon(Heroicon::OutlinedArchiveBox)
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn (QualityDocument $record): bool => auth()->user()?->can('archive', $record)
                && $record->status !== QualityDocumentStatus::Archived)
            ->action(function (QualityDocument $record): void {
                $record->update([
                    'status' => QualityDocumentStatus::Archived,
                ]);

                Notification::make()
                    ->warning()
                    ->title('Dokumen mutu diarsipkan.')
                    ->send();
            });
    }

    private static function documentUrl(QualityDocument $record): ?string
    {
        if (filled($record->external_url)) {
            return $record->external_url;
        }

        if (blank($record->file_path)) {
            return null;
        }

        return URL::signedRoute('quality-documents.file', ['document' => $record], absolute: false);
    }
}
