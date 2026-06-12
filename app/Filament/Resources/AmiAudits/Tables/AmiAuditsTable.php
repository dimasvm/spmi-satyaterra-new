<?php

namespace App\Filament\Resources\AmiAudits\Tables;

use App\Enums\AmiAuditStatus;
use App\Filament\Resources\AmiAudits\AmiAuditResource;
use App\Models\AmiAudit;
use App\Models\AmiChecklist;
use App\Models\IndicatorUnitAssignment;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class AmiAuditsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('amiPeriod.name')
                    ->label('Periode AMI')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('auditeeUnit.name')
                    ->label('Unit Auditee')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('scheduled_date')
                    ->label('Jadwal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('auditor_assignments_count')
                    ->label('Jumlah Auditor')
                    ->badge()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('checklists_count')
                    ->label('Checklist')
                    ->badge()
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('finalized_at')
                    ->label('Finalisasi')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('ami_period_id')
                    ->label('Periode AMI')
                    ->relationship('amiPeriod', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('auditee_unit_id')
                    ->label('Unit Auditee')
                    ->relationship('auditeeUnit', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(AmiAuditStatus::class),
            ])
            ->emptyStateHeading('Belum ada jadwal audit')
            ->emptyStateDescription('Buat jadwal audit AMI untuk unit yang akan diaudit pada periode aktif.')
            ->emptyStateIcon(Heroicon::OutlinedShieldCheck)
            ->defaultSort('scheduled_date')
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (): bool => AmiAuditResource::canManageAmi()),
                Action::make('finalize')
                    ->label('Finalisasi Audit')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Finalisasi audit ini?')
                    ->modalDescription('Status audit akan menjadi Final. Audit harus memiliki minimal satu lead auditor.')
                    ->visible(fn (AmiAudit $record): bool => AmiAuditResource::canManageAmi()
                        && $record->status !== AmiAuditStatus::Finalized)
                    ->action(function (AmiAudit $record): void {
                        if (! $record->leadAuditorAssignment()->exists()) {
                            Notification::make()
                                ->danger()
                                ->title('Audit belum memiliki lead auditor.')
                                ->body('Tambahkan minimal satu auditor dengan peran Ketua sebelum finalize.')
                                ->send();

                            return;
                        }

                        $record->update([
                            'status' => AmiAuditStatus::Finalized,
                            'finalized_at' => now(),
                            'finalized_by' => auth()->id(),
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Audit berhasil difinalisasi.')
                            ->send();
                    }),
                Action::make('generateChecklist')
                    ->label('Generate Checklist')
                    ->icon(Heroicon::OutlinedClipboardDocumentList)
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Generate checklist audit?')
                    ->modalDescription('Checklist akan dibuat dari penugasan indikator unit pada periode SPMI audit. Data lama tidak akan diduplikasi.')
                    ->visible(fn (AmiAudit $record): bool => AmiAuditResource::canManageAmi()
                        && $record->status !== AmiAuditStatus::Finalized)
                    ->action(function (AmiAudit $record): void {
                        $spmiPeriodId = $record->amiPeriod?->spmi_period_id;

                        if ($spmiPeriodId === null) {
                            Notification::make()
                                ->danger()
                                ->title('Periode SPMI tidak ditemukan.')
                                ->send();

                            return;
                        }

                        $assignmentIndicatorIds = IndicatorUnitAssignment::query()
                            ->where('unit_id', $record->auditee_unit_id)
                            ->where('spmi_period_id', $spmiPeriodId)
                            ->pluck('standard_indicator_id');

                        if ($assignmentIndicatorIds->isEmpty()) {
                            Notification::make()
                                ->warning()
                                ->title('Tidak ada penugasan indikator untuk unit dan periode ini.')
                                ->send();

                            return;
                        }

                        $created = 0;

                        DB::transaction(function () use ($assignmentIndicatorIds, $record, &$created): void {
                            foreach ($assignmentIndicatorIds as $standardIndicatorId) {
                                $checklist = AmiChecklist::query()->firstOrCreate([
                                    'ami_audit_id' => $record->id,
                                    'standard_indicator_id' => $standardIndicatorId,
                                ]);

                                if ($checklist->wasRecentlyCreated) {
                                    $created++;
                                }
                            }
                        });

                        Notification::make()
                            ->success()
                            ->title('Checklist audit berhasil digenerate.')
                            ->body($created.' checklist baru dibuat dari '.$assignmentIndicatorIds->count().' indikator penugasan.')
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => AmiAuditResource::canManageAmi()),
                ])
                    ->visible(fn (): bool => AmiAuditResource::canManageAmi()),
            ]);
    }
}
